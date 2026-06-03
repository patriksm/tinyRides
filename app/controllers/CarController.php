<?php

declare(strict_types=1);

class CarController extends Controller
{
    private Car $carModel;

    public function __construct()
    {
        parent::__construct();
        $this->carModel = $this->model('Car');
    }

    public function index(): void
    {
        // Front/filter:
        // search, location, category_id, age_category, condition_status, min, max, sort

        $filters = [
            'search' => trim((string)($_GET['search'] ?? '')),
            'location' => trim((string)($_GET['location'] ?? '')),
            'category_id' => (($_GET['category_id'] ?? '') !== '') ? (int)$_GET['category_id'] : null,
            'age_category' => trim((string)($_GET['age_category'] ?? '')),
            'condition_status' => trim((string)($_GET['condition_status'] ?? '')),
            'min_price' => (($_GET['min'] ?? '') !== '') ? (float)$_GET['min'] : null,
            'max_price' => (($_GET['max'] ?? '') !== '') ? (float)$_GET['max'] : null,
            'sort' => trim((string)($_GET['sort'] ?? '')),
        ];

        if ($filters['location'] === 'all-locations') {
            $filters['location'] = '';
        }

        $params = [];
        foreach ($filters as $key => $value) {
            if ($value === null) continue;
            if (is_string($value) && $value === '') continue;
            $params[$key] = $value;
        }

        if (!empty($params)) {
            $cars = $this->carModel->searchAndFilter($params);
        } else {
            $cars = $this->carModel->getAllWithOwner();
        }

        $this->view('car/index', [
//            'title' => 'listing',
            'title' => t('nav.items'),
            'cars' => $cars,
            'q'=>$filters
        ]);
    }

    public function detail(string $uuid = ''): void
    {
        $uuid = trim($uuid);
        if ($uuid === '') throw new RuntimeException("Not found", 404);

        // photos + owner 
        $car = $this->carModel->getWithOwnerAndPhotosByUuid($uuid);

        if (!$car) throw new RuntimeException("Not found", 404);

        $this->view('car/detail', [
            'title' => 'Car details',
            'car' => $car,
        ]);
    }


    public function create(): void
    {
        $this->requiredLogin();
        $this->view('car/create', [
            'title' => 'Add new car',
            '_token' => $this->csrfToken(),
        ]);
    }

    public function store(): void
    {
        $this->requiredLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('car/create');
        }
        $this->requireCsrf();

        $userId = (int)($_SESSION['user_id'] ?? 0);

        // ----------------------------
        // Read inputs
        // ----------------------------
        $brand       = $this->post('brand');
        $model       = $this->post('model');
        $description = $this->post('description');

        // NEW: strict address fields (from hidden inputs)
        $city   = $this->post('city');
        $street = $this->post('street');
        $lat    = $this->post('latitude');
        $lon    = $this->post('longitude');

        // optional: keep old "location" string (for compatibility with old DB column)
        $locationText = trim($city . ($street !== '' ? ', ' . $street : ''));

        $categoryIdRaw = $this->post('category_id');
        $categoryId = ($categoryIdRaw !== '' && ctype_digit($categoryIdRaw)) ? (int)$categoryIdRaw : 0;

        $ageCategory = $this->post('age_category');
        $condition   = $this->post('condition_status');

        $pricePerDay = $this->post('price_per_day');
        $per         = $this->post('per');

        $deposit     = $this->post('deposit');
        $rentalTerms = $this->post('rental_terms');

        $contactName  = $this->post('contact_name');
        $contactPhone = $this->post('contact_phone');
        $contactEmail = $this->post('contact_email');

        $batteryLife       = $this->post('battery_life');
        $chargingTime      = $this->post('charging_time');
        $maxSpeed          = $this->post('max_speed');
        $maxWeightCapacity = $this->post('max_weight_capacity');

        // Files
        $photos = $_FILES['photos'] ?? null;
        $hasUploads = $photos && isset($photos['name']) && is_array($photos['name']);
        $photoCount = $hasUploads ? count(array_filter($photos['name'], static fn($n) => $n !== '')) : 0;

        // ----------------------------
        // Validate (field-level)
        // ----------------------------
        $errors = []; // field => message

        if ($brand === '') $errors['brand'] = t('validation.brand');
        if ($model === '') $errors['model'] = t('validation.model');

        // NEW: strict location validation
        if ($city === '')   $errors['city'] = t('validation.city');
        if ($street === '') $errors['street'] = t('validation.street');

        // lat/lon -> selection proof
        if ($lat === '' || !is_numeric($lat) || $lon === '' || !is_numeric($lon)) {
            $errors['city'] = $errors['city'] ?? 'Please select a city from suggestions';
        }

        if ($categoryId <= 0) $errors['category_id'] = t('validation.category');

        $allowedAges = ['0-2', '2-5', '5-8', '8+'];
        if ($ageCategory === '' || !in_array($ageCategory, $allowedAges, true)) {
            $errors['age_category'] = t('validation.age_suitability');
        }

        $allowedConditions = ['new', 'excellent', 'good', 'used'];
        if ($condition === '' || !in_array($condition, $allowedConditions, true)) {
            $errors['condition_status'] = t('validation.condition');
        }

        if ($pricePerDay === '' || !is_numeric($pricePerDay) || (float)$pricePerDay <= 0) {
            $errors['price_per_day'] = t('validation.price_range');
        }

        $allowedPer = ['day', 'hour', 'week'];
        if ($per === '' || !in_array($per, $allowedPer, true)) {
            $errors['per'] = 'Per must be day/hour/week';
            $per = 'day';
        }

        if ($deposit !== '' && (!is_numeric($deposit) || (float)$deposit < 0)) {
            $errors['deposit'] = t('validation.deposit');
        }

        if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = t('validation.email');
        }

        // Photos must be at least 1
        if ($photoCount <= 0) {
            $errors['photos'] = t('validation.image');
        }

        if ($photoCount > 5) {
            $errors['photos'] = t('validation.max_image');
        }

        // validate upload error codes quickly
        if ($photoCount > 0 && $hasUploads) {
            $errs = $photos['error'] ?? [];
            foreach ($errs as $i => $code) {
                if (($photos['name'][$i] ?? '') === '') continue;
                if ($code !== UPLOAD_ERR_OK) {
                    $errors['photos'] = 'Rasm yuklashda xatolik yuz berdi';
                    break;
                }
            }
        }

        // ----------------------------
        // On validation error: keep OLD + ERRORS
        // ----------------------------
        if (!empty($errors)) {
            $_SESSION['old'] = [
                'brand' => $brand,
                'model' => $model,
                'description' => $description,

                // NEW: keep address old values
                'city' => $city,
                'street' => $street,
                'latitude' => $lat,
                'longitude' => $lon,

                // optional backward compatibility
                'location' => $locationText,

                'category_id' => $categoryIdRaw,
                'age_category' => $ageCategory,
                'condition_status' => $condition,
                'price_per_day' => $pricePerDay,
                'per' => $per,
                'deposit' => $deposit,
                'rental_terms' => $rentalTerms,
                'contact_name' => $contactName,
                'contact_phone' => $contactPhone,
                'contact_email' => $contactEmail,
                'battery_life' => $batteryLife,
                'charging_time' => $chargingTime,
                'max_speed' => $maxSpeed,
                'max_weight_capacity' => $maxWeightCapacity,
            ];

            $_SESSION['errors'] = $errors;
            $this->redirect('car/create');
        }

        // ----------------------------
        // Prepare insert data
        // ----------------------------
        $data = [
            'uuid' => $this->uuidV4(),
            'user_id' => $userId,
            'brand' => $brand,
            'model' => $model,
            'description' => ($description !== '') ? $description : null,

            // NEW: store structured address
            'city' => $city,
            'street' => $street,
            'latitude' => (float)$lat,
            'longitude' => (float)$lon,

            // optional: if DB still has old location column
            // 'location' => ($locationText !== '') ? $locationText : null,

            'category_id' => $categoryId,
            'age_category' => $ageCategory,
            'condition_status' => $condition,
            'price_per_day' => (float)$pricePerDay,
            'price_per' => $per,

            'deposit' => ($deposit !== '' && is_numeric($deposit)) ? (float)$deposit : null,
            'rental_terms' => ($rentalTerms !== '') ? $rentalTerms : null,
            'contact_name' => ($contactName !== '') ? $contactName : null,
            'contact_phone' => ($contactPhone !== '') ? $contactPhone : null,
            'contact_email' => ($contactEmail !== '') ? $contactEmail : null,

            'battery_life' => ($batteryLife !== '' && is_numeric($batteryLife)) ? (float)$batteryLife : null,
            'charging_time' => ($chargingTime !== '' && is_numeric($chargingTime)) ? (float)$chargingTime : null,
            'max_speed' => ($maxSpeed !== '' && is_numeric($maxSpeed)) ? (float)$maxSpeed : null,
            'max_weight_capacity' => ($maxWeightCapacity !== '' && is_numeric($maxWeightCapacity)) ? (int)$maxWeightCapacity : null,
        ];

        // ----------------------------
        // Save (with transaction)
        // ----------------------------
        $db = $this->db();
        $db->beginTransaction();

        try {
            $id = $this->carModel->addCar($data);
            if ($id === false) {
                throw new RuntimeException('Something went wrong. Please try again.');
            }
            $id = (int)$id;

            // Save photos (append order; do NOT overwrite)
            if ($photoCount > 0 && $hasUploads) {
                $startOrder = 0;
                if (method_exists($this->carModel, 'nextDisplayOrder')) {
                    $startOrder = $this->carModel->nextDisplayOrder($id);
                }

                $rows = $this->saveUploadedPhotos($photos, $id, $startOrder);

                $ok = $this->carModel->addPhotos($id, $rows);
                if (!$ok) {
                    throw new RuntimeException('Could not save photos');
                }
            }

            $db->commit();

            unset($_SESSION['old'], $_SESSION['errors']);
            $this->setMessage('success', 'Item has been created');
            $this->redirect('user/profile');
        } catch (Throwable $e) {
            $db->rollback();

            $_SESSION['old'] = [
                'brand' => $brand,
                'model' => $model,
                'description' => $description,

                // NEW: keep address old values
                'city' => $city,
                'street' => $street,
                'latitude' => $lat,
                'longitude' => $lon,
                'location' => $locationText,

                'category_id' => $categoryIdRaw,
                'age_category' => $ageCategory,
                'condition_status' => $condition,
                'price_per_day' => $pricePerDay,
                'per' => $per,
                'deposit' => $deposit,
                'rental_terms' => $rentalTerms,
                'contact_name' => $contactName,
                'contact_phone' => $contactPhone,
                'contact_email' => $contactEmail,
                'battery_life' => $batteryLife,
                'charging_time' => $chargingTime,
                'max_speed' => $maxSpeed,
                'max_weight_capacity' => $maxWeightCapacity,
            ];

            // friendly error
            $_SESSION['errors'] = ['form' => $e->getMessage()];
            $this->redirect('car/create');
        }
    }


    public function edit(string $uuid = ''): void
    {
        $this->requiredLogin();

        $uuid = trim($uuid);
        if ($uuid === '') {
            throw new RuntimeException("Not found", 404);
        }

        $car = $this->carModel->findByUuid($uuid);
        if (!$car) {
            throw new RuntimeException("Not found", 404);
        }

        $this->view('car/edit', [
            'car' => $car,
            '_token' => $this->csrfToken(),
        ]);
    }


    public function changeStatus(string $id = '0'): void
    {
        $this->requiredLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $this->requireCsrf();

        $uuid = trim($id);
        if ($uuid === '' || $uuid === '0') {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Not found']);
            return;
        }

        $car = $this->carModel->findByUuid($uuid);
        if (!$car) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Not found']);
            return;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ((int)($car['user_id'] ?? 0) !== $userId) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        // ✅ front-end take status
        $requested = strtolower(trim((string)($_POST['status'] ?? '')));

        if ($requested === 'rent') $requested = 'rented';

        $allowed = ['active', 'inactive', 'rented'];
        if (!in_array($requested, $allowed, true)) {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid status']);
            return;
        }

        $ok = $this->carModel->setStatusByUuid($uuid, $requested);
        if (!$ok) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Could not update']);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => $requested]);
    }

    public function delete(string $id = '0'): void
    {
        $this->requiredLogin();
        $uuid = trim($id);
        if ($uuid === '') {
            throw new RuntimeException("Not found", 404);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/profile');
        }
        $this->requireCsrf();

        $car = $this->carModel->findByUuid($uuid);
        if (!$car) {
            throw new RuntimeException('Not found', 404);
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ((int)($car['user_id'] ?? 0) !== $userId) {
            throw new RuntimeException('Not found', 404);
        }

        $ok = $this->carModel->deleteByUuid($uuid);
        $this->setMessage($ok ? 'success' : 'error', $ok ? 'Item deleted' : 'Something went wrong');
        $this->redirect('user/profile');
    }

    public function update(string $uuid = ''): void
    {
        $this->requiredLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/profile');
        }

        $this->requireCsrf();

        $uuid = trim($uuid);
        if ($uuid === '') {
            throw new RuntimeException('Not found', 404);
        }

        $car = $this->carModel->findByUuid($uuid);
        if (!$car) {
            throw new RuntimeException('Not found', 404);
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ((int)($car['user_id'] ?? 0) !== $userId) {
            throw new RuntimeException('Not found', 404);
        }

        $dbId = (int)($car['id'] ?? 0);
        if ($dbId <= 0) {
            throw new RuntimeException('Invalid car id', 500);
        }

        $categoryIdRaw = $this->post('category_id');
        $categoryId = ($categoryIdRaw !== '' && ctype_digit($categoryIdRaw)) ? (int)$categoryIdRaw : 0;

        $brand = trim($this->post('brand'));
        $model = trim($this->post('model'));
        $description = trim($this->post('description'));

        $city = trim($this->post('city'));
        $street = trim($this->post('street'));
        $latitude = $this->post('latitude');
        $longitude = $this->post('longitude');

        $ageCategory = trim($this->post('age_category'));
        $conditionStatus = trim($this->post('condition_status'));

        $pricePerDay = $this->post('price_per_day');
        $per = trim($this->post('per'));

        $deposit = $this->post('deposit');
        $rentalTerms = trim($this->post('rental_terms'));

        $contactName = trim($this->post('contact_name'));
        $contactPhone = trim($this->post('contact_phone'));
        $contactEmail = trim($this->post('contact_email'));

        $batteryLife = $this->post('battery_life');
        $chargingTime = $this->post('charging_time');
        $maxSpeed = $this->post('max_speed');
        $maxWeightCapacity = $this->post('max_weight_capacity');

        $photos = $_FILES['photos'] ?? null;
        $hasUploads = $photos && isset($photos['name']) && is_array($photos['name']);
        $photoCount = $hasUploads
            ? count(array_filter($photos['name'], static fn($n) => $n !== ''))
            : 0;

        $errors = [];

        if ($brand === '') {
            $errors[] = 'Brand is required';
        }

        if ($model === '') {
            $errors[] = 'Model is required';
        }

        if ($categoryId <= 0) {
            $errors[] = 'Category is required';
        }

        $allowedAges = ['0-2', '2-5', '5-8', '8+'];
        if ($ageCategory === '' || !in_array($ageCategory, $allowedAges, true)) {
            $errors[] = 'Age suitability is invalid';
        }

        $allowedConditions = ['new', 'excellent', 'good', 'used'];
        if ($conditionStatus === '' || !in_array($conditionStatus, $allowedConditions, true)) {
            $errors[] = 'Condition is invalid';
        }

        if ($pricePerDay === '' || !is_numeric($pricePerDay) || (float)$pricePerDay <= 0) {
            $errors[] = 'Price must be greater than 0';
        }

        $allowedPer = ['day', 'hour', 'week'];
        if ($per === '' || !in_array($per, $allowedPer, true)) {
            $errors[] = 'Per must be day/hour/week';
        }

        if ($city === '') {
            $errors[] = 'City is required';
        }

        if ($street === '') {
            $errors[] = 'Street is required';
        }

        if ($latitude === '' || !is_numeric($latitude)) {
            $errors[] = 'Latitude is invalid';
        }

        if ($longitude === '' || !is_numeric($longitude)) {
            $errors[] = 'Longitude is invalid';
        }

        if ($deposit !== '' && (!is_numeric($deposit) || (float)$deposit < 0)) {
            $errors[] = 'Deposit is invalid';
        }

        if ($contactPhone === '') {
            $errors[] = 'Phone number is required';
        }

        if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email address is invalid';
        }

        if ($photoCount > 5) {
            $errors[] = 'Maximum 5 ta rasm yuklash mumkin';
        }

        if ($photoCount > 0 && $hasUploads) {
            $errs = $photos['error'] ?? [];
            foreach ($errs as $i => $code) {
                if (($photos['name'][$i] ?? '') === '') {
                    continue;
                }
                if ($code !== UPLOAD_ERR_OK) {
                    $errors[] = 'Rasm yuklashda xatolik yuz berdi';
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $this->setMessage('error', implode('<br>', $errors));
            $this->redirect('car/edit/' . urlencode($uuid));
        }

        $data = [
            'brand' => $brand,
            'model' => $model,
            'description' => $description !== '' ? $description : null,

            'category_id' => $categoryId,
            'age_category' => $ageCategory,
            'condition_status' => $conditionStatus,

            'price_per_day' => (float)$pricePerDay,
            'price_per' => $per,

            'deposit' => ($deposit !== '' && is_numeric($deposit)) ? (float)$deposit : null,
            'rental_terms' => $rentalTerms !== '' ? $rentalTerms : null,

            'contact_name' => $contactName !== '' ? $contactName : null,
            'contact_phone' => $contactPhone,
            'contact_email' => $contactEmail !== '' ? $contactEmail : null,

            'battery_life' => ($batteryLife !== '' && is_numeric($batteryLife)) ? (float)$batteryLife : null,
            'charging_time' => ($chargingTime !== '' && is_numeric($chargingTime)) ? (float)$chargingTime : null,
            'max_speed' => ($maxSpeed !== '' && is_numeric($maxSpeed)) ? (float)$maxSpeed : null,
            'max_weight_capacity' => ($maxWeightCapacity !== '' && is_numeric($maxWeightCapacity)) ? (int)$maxWeightCapacity : null,

            'city' => $city,
            'street' => $street,
            'latitude' => (float)$latitude,
            'longitude' => (float)$longitude,
        ];

        $db = $this->db();
        $db->beginTransaction();

        try {
            $ok = $this->carModel->updateByUuid($uuid, $data);
            if (!$ok) {
                throw new RuntimeException('Could not update item');
            }

            if ($photoCount > 0 && $hasUploads) {
                $startOrder = 0;
                if (method_exists($this->carModel, 'nextDisplayOrder')) {
                    $startOrder = $this->carModel->nextDisplayOrder($dbId);
                }

                $rows = $this->saveUploadedPhotos($photos, $dbId, $startOrder);

                if (!empty($rows)) {
                    $okPhotos = $this->carModel->addPhotos($dbId, $rows);
                    if (!$okPhotos) {
                        throw new RuntimeException('Could not save photos');
                    }
                }
            }

            $db->commit();

            $this->setMessage('success', 'Item updated');
            $this->redirect('user/profile');
        } catch (Throwable $e) {
            $db->rollback();
            $this->setMessage('error', $e->getMessage());
            $this->redirect('car/edit/' . urlencode($uuid));
        }
    }

    private function saveUploadedPhotos(array $photos, int $carId, int $startOrder = 0): array
    {
        $baseDir = ROOT_PATH . '/public/uploads/cars/' . $carId;

        if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
            throw new RuntimeException('Upload folder yaratib bo‘lmadi');
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024;
        $maxFiles = 5;

        $rows = [];

        $names = $photos['name'] ?? [];
        $tmpNames = $photos['tmp_name'] ?? [];
        $errors = $photos['error'] ?? [];
        $sizes = $photos['size'] ?? [];

        if (!is_array($names)) {
            return [];
        }

        $count = count($names);
        $order = $startOrder;

        for ($i = 0; $i < $count; $i++) {
            if (($names[$i] ?? '') === '') {
                continue;
            }

            if (count($rows) >= $maxFiles) {
                break;
            }

            if (($errors[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Rasm yuklashda xatolik yuz berdi');
            }

            if (($sizes[$i] ?? 0) > $maxSize) {
                throw new RuntimeException('Rasm hajmi katta (max 5MB)');
            }

            $original = (string)$names[$i];
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                throw new RuntimeException('Faqat jpg, jpeg, png, webp ruxsat etiladi');
            }

            $tmp = (string)($tmpNames[$i] ?? '');
            if ($tmp === '' || !is_uploaded_file($tmp) || @getimagesize($tmp) === false) {
                throw new RuntimeException('Yuklangan fayl rasm emas');
            }

            $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
            $dest = $baseDir . '/' . $fileName;

            if (!move_uploaded_file($tmp, $dest)) {
                throw new RuntimeException('Rasmni saqlab bo‘lmadi');
            }

            $dbPath = '/public/uploads/cars/' . $carId . '/' . $fileName;

            $rows[] = [
                'image_path' => $dbPath,
                'is_primary' => ($order === 0) ? 1 : 0,
                'display_order' => $order,
            ];

            $order++;
        }

        return $rows;
    }


    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
