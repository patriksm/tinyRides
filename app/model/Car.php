<?php

declare(strict_types=1);

class Car extends Model
{
    protected string $table = 'cars';

    protected function allowedColumns(): array
    {
        return [
            'id',
            'user_id',
            'brand',
            'model',
            'year',
            'color',
            'uuid',

            'category_id',

            'age_category',
            'condition_status',

            'price_per_day',
            'price_per',

            'deposit',
            'rental_terms',

            'contact_name',
            'contact_phone',
            'contact_email',

            'battery_life',
            'charging_time',
            'max_speed',
            'max_weight_capacity',

            'description',

            'image',

            'status',
            'views',
            'total_rentals',
            'created_at',
            'updated_at',

            // NEW structured address
            'city',
            'street',
            'latitude',
            'longitude',
        ];
    }

    public function getAllWithOwner(?int $limit = null): array
    {
        $sql = "
        SELECT
            cars.*,
            users.name AS owner_name,
            users.email AS owner_email,
            c.name AS category_name,
            (
                SELECT ci.image_path
                FROM car_images ci
                WHERE ci.car_id = cars.id
                ORDER BY ci.is_primary DESC, ci.display_order ASC, ci.id ASC
                LIMIT 1
            ) AS main_photo
        FROM cars
        JOIN users ON users.id = cars.user_id
        LEFT JOIN categories c ON c.id = cars.category_id
        WHERE cars.status IN ('active','rented')
        ORDER BY cars.created_at DESC
    ";

        if ($limit !== null) {
            $limit = max(1, (int)$limit);
            $sql .= " LIMIT {$limit}";
        }

        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    public function getWithOwner(int $carId): array|false
    {
        $sql = "
        SELECT
            cars.*,
            users.name AS owner_name,
            users.email AS owner_email,
            users.address AS owner_address,
            c.name AS category_name
        FROM cars
        JOIN users ON users.id = cars.user_id
        LEFT JOIN categories c ON c.id = cars.category_id
        WHERE cars.id = :id
        LIMIT 1
    ";

        $this->db->query($sql);
        $this->db->bind(':id', $carId);
        return $this->db->fetch();
    }

    public function getWithOwnerAndPhotos(int $carId): array|false
    {
        $car = $this->getWithOwner($carId);
        if (!$car) return false;

        $photos = $this->getPhotos($carId);
        $car['photos'] = $photos;

        // field name image_path
        $car['main_photo'] = $photos[0]['image_path'] ?? ($car['image'] ?? null);

        return $car;
    }

    public function searchAndFilter(array $params): array
    {
        $where = ["cars.status IN ('active','rented')"];
        $bind = [];

        // search
        if (!empty($params['search'])) {
            $where[] = "(cars.brand LIKE :search OR cars.model LIKE :search OR cars.description LIKE :search)";
            $bind[':search'] = '%' . (string)$params['search'] . '%';
        }

        // location -> city
        if (!empty($params['location'])) {
            $where[] = "cars.city = :city";
            $bind[':city'] = (string)$params['location'];
        }

        // category
        if (!empty($params['category_id'])) {
            $where[] = "cars.category_id = :category_id";
            $bind[':category_id'] = (int)$params['category_id'];
        } elseif (!empty($params['category'])) {
            $where[] = "cars.category_id = :category_id";
            $bind[':category_id'] = (int)$params['category'];
        }

        // age
        if (!empty($params['age_category'])) {
            $where[] = "cars.age_category = :age_category";
            $bind[':age_category'] = (string)$params['age_category'];
        }

        // condition
        if (!empty($params['condition_status'])) {
            $where[] = "cars.condition_status = :condition_status";
            $bind[':condition_status'] = (string)$params['condition_status'];
        }

        // min/max price
        if (!empty($params['min_price'])) {
            $where[] = "cars.price_per_day >= :min_price";
            $bind[':min_price'] = (float)$params['min_price'];
        }

        if (!empty($params['max_price'])) {
            $where[] = "cars.price_per_day <= :max_price";
            $bind[':max_price'] = (float)$params['max_price'];
        }

        // order by
        $orderBy = "cars.created_at DESC";
        if (!empty($params['sort'])) {
            $orderBy = match ((string)$params['sort']) {
                'price_low'  => "cars.price_per_day ASC",
                'price_high' => "cars.price_per_day DESC",
                default      => "cars.created_at DESC",
            };
        }


        $sql = "
        SELECT
            cars.*,
            users.name AS owner_name,
            c.name AS category_name,
            (
                SELECT ci.image_path
                FROM car_images ci
                WHERE ci.car_id = cars.id
                ORDER BY ci.is_primary DESC, ci.display_order ASC, ci.id ASC
                LIMIT 1
            ) AS main_photo
        FROM cars
        JOIN users ON users.id = cars.user_id
        LEFT JOIN categories c ON c.id = cars.category_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY {$orderBy}
    ";

        $this->db->query($sql);
        foreach ($bind as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->fetchAll();
    }

    public function setStatusByUuid(string $uuid, string $status): bool
    {
        $sql = "UPDATE cars SET status = :status WHERE uuid = :uuid LIMIT 1";
        return $this->db
            ->query($sql)
            ->bindAll([
                ':status' => $status,
                ':uuid'   => $uuid,
            ])
            ->execute();
    }


    public function getAllLocation(): array
    {
        $this->db->query("
            SELECT DISTINCT city
            FROM cars
            WHERE status = 'active' AND city IS NOT NULL AND city <> ''
            ORDER BY city
        ");
        $result = $this->db->fetchAll();
        return array_values(array_filter(array_column($result, 'city')));
    }

    public function getPriceRange(): array|false
    {
        $this->db->query("SELECT MIN(price_per_day) AS min_price, MAX(price_per_day) AS max_price, AVG(price_per_day) AS avg_price FROM cars WHERE status = 'active'");
        return $this->db->fetch();
    }

    public function getUserCars(int $userId): array
    {
        return $this->findBy('user_id', $userId, 'created_at DESC');
    }

    public function addCar(array $data): int|false
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'active';

        // price_per whitelist
        if (isset($data['price_per'])) {
            $per = (string)$data['price_per'];
            if (!in_array($per, ['day', 'hour', 'week'], true)) {
                $data['price_per'] = 'day';
            }
        }

        return $this->create($data);
    }

    public function updateStatus(int $carId, string $status): bool
    {
        return $this->update($carId, ['status' => $status]);
    }

    public function updateImage(int $carId, string $imagePath): bool
    {
        return $this->update($carId, ['image' => $imagePath]);
    }

    public function increamentView(int $carId): bool
    {
        $this->db->query("UPDATE cars SET views = views + 1 WHERE id = :id");
        $this->db->bind(':id', $carId);
        return (bool)$this->db->execute();
    }

    /**
     * Car photos (INSERT)
     * $photos = [ ['image_path' => '...', 'is_primary' => 1, 'display_order' => 0], ... ]
     */
    public function addPhotos(int $carId, array $photos): bool
    {
        if (empty($photos)) return true;

        $sql = "INSERT INTO car_images (car_id, image_path, is_primary, display_order)
            VALUES (:car_id, :image_path, :is_primary, :display_order)";

        foreach ($photos as $p) {
            $this->db->query($sql);
            $this->db->bind(':car_id', $carId);
            $this->db->bind(':image_path', (string)$p['image_path']);
            $this->db->bind(':is_primary', (int)$p['is_primary']);
            $this->db->bind(':display_order', (int)$p['display_order']);
            if (!$this->db->execute()) return false;
        }
        return true;
    }

    public function findByUuid(string $uuid): array|false
    {
        return $this->findOneBy('uuid', $uuid);
    }

    public function getPhotos(int $carId): array
    {
        $this->db->query("
        SELECT image_path, is_primary, display_order
        FROM car_images
        WHERE car_id = :id
        ORDER BY is_primary DESC, display_order ASC, id ASC
    ");
        $this->db->bind(':id', $carId);
        return $this->db->fetchAll();
    }

    public function nextDisplayOrder(int $carId): int
    {
        $this->db->query("SELECT COALESCE(MAX(display_order), -1) AS m FROM car_images WHERE car_id = :id");
        $this->db->bind(':id', $carId);
        $row = $this->db->fetch();
        return (int)($row['m'] ?? -1) + 1;
    }


    public function deletePhoto(int $photoId, int $carId): bool
    {
        $this->db->query("DELETE FROM car_photos WHERE id = :pid AND car_id = :cid");
        $this->db->bind(':pid', $photoId);
        $this->db->bind(':cid', $carId);
        return (bool)$this->db->execute();
    }

    public function deleteByUuid(string $uuid): bool
    {
        if ($uuid === '') {
            throw new InvalidArgumentException('Invalid uuid');
        }

        $this->db->query("DELETE FROM {$this->table} WHERE uuid = :uuid");
        $this->db->bind(':uuid', $uuid);

        return (bool)$this->db->execute();
    }

    public function getWithOwnerAndPhotosByUuid(string $uuid): array|false
    {
        $this->db->query("
        SELECT
            c.*,
            u.name AS owner_name,
            u.email AS owner_email,
            cat.name AS category_name
        FROM cars c
        JOIN users u ON u.id = c.user_id
        LEFT JOIN categories cat ON cat.id = c.category_id
        WHERE c.uuid = :uuid
        LIMIT 1
    ");
        $this->db->bind(':uuid', $uuid);
        $car = $this->db->fetch();

        if (!$car) return false;

        $this->db->query("
        SELECT *
        FROM car_images
        WHERE car_id = :car_id
        ORDER BY display_order ASC
    ");
        $this->db->bind(':car_id', (int)$car['id']);
        $car['photos'] = $this->db->fetchAll();

        return $car;
    }

    public function updateStatusByUuid(string $uuid, string $status): bool
    {
        if ($uuid === '') return false;

        $allowed = ['active', 'inactive', 'rented'];
        if (!in_array($status, $allowed, true)) return false;

        $this->db->query("UPDATE cars SET status = :status WHERE uuid = :uuid LIMIT 1");
        $this->db->bind(':status', $status);
        $this->db->bind(':uuid', $uuid);

        return (bool)$this->db->execute();
    }

    public function cycleStatusByUuid(string $uuid): string|false
    {
        $car = $this->findByUuid($uuid);
        if (!$car) return false;

        $current = (string)($car['status'] ?? 'active');

        $next = match ($current) {
            'active' => 'rented',
            'rented' => 'inactive',
            default => 'active',
        };

        $ok = $this->updateStatusByUuid($uuid, $next);
        return $ok ? $next : false;
    }

    public function begin(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}
