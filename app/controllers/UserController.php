<?php

declare(strict_types=1);

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requiredLogin();
    }

    public function index(): void
    {
        $this->redirect('user/profile');
    }

    /**
     * /user/profile
     */
    public function profile(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $userModel = $this->model('User');
        $carModel  = $this->model('Car');

        $user = null;

        if (method_exists($userModel, 'findById')) {
            $user = $userModel->findById($userId);
        } elseif (method_exists($userModel, 'findbyId')) {
            $user = $userModel->findbyId($userId);
        }

        if (!$user) {
            $this->setMessage('error', 'User not found');
            $this->redirect('');
        }

        // listings
        $cars = $carModel->getUserCars($userId);

        $this->view('user/profile', [
            'title'  => 'Profile',
            'user'   => $user,
            'cars'   => $cars,
            '_token' => $this->csrfToken(),
        ]);
    }

    /**
     * /user/myCars
     */
    public function myCars(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $carModel = $this->model('Car');

        $cars = $carModel->getUserCars($userId);

        $this->view('user/mycars', [
            'title' => 'My cars',
            'cars'  => $cars,
        ]);
    }

    /**
     * POST /user/updateProfile
     * updates name + phone + address  
     */
    public function updateProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/profile');
        }

        $this->requireCsrf();

        $userId  = (int)($_SESSION['user_id'] ?? 0);

        $name    = trim((string)$this->post('name'));
        $email   = trim((string)$this->post('email'));
        $phone   = trim((string)$this->post('phone'));
        $address = trim((string)$this->post('address'));

        $errors = [];

        if ($name === '') {
            $errors[] = 'Please enter your name';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email';
        }

        $userModel = $this->model('User');

        // blocking if user tries to use another users email
        $existing = $userModel->findByEmail($email);
        if ($existing && (int)($existing['id'] ?? 0) !== $userId) {
            $errors[] = 'This email is already in use';
        }

        if (!empty($errors)) {
            $this->setMessage('error', implode('<br>', $errors));
            $this->redirect('user/profile');
        }

        $data = [
            'name'       => $name,
            'email'      => $email,
            'phone'      => ($phone !== '' ? $phone : null),
            'address'    => ($address !== '' ? $address : null),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $userModel->update($userId, $data);

        if ($ok) {
            $_SESSION['user_name']    = $name;
            $_SESSION['user_email']   = $email;
            $_SESSION['user_phone']   = $phone;
            $_SESSION['user_address'] = $address;

            $this->setMessage('success', 'Profile has been updated');
        } else {
            $this->setMessage('error', 'Something went wrong');
        }

        $this->redirect('user/profile');
    }

    /**
     * POST /user/changePassword
     */
    public function changePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/profile');
        }

        $this->requireCsrf();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $userModel = $this->model('User');

        // checking user
        $user = null;
        if (method_exists($userModel, 'findById')) {
            $user = $userModel->findById($userId);
        } elseif (method_exists($userModel, 'findbyId')) {
            $user = $userModel->findbyId($userId);
        }

        if (!$user || empty($user['password'])) {
            $this->setMessage('error', 'User not found');
            $this->redirect('user/profile');
        }

        $currentPassword = (string)$this->post('current_password');
        $newPassword     = (string)$this->post('new_password');
        $confirmPassword = (string)$this->post('confirm_password');

        $errors = [];

        if ($currentPassword === '' || !password_verify($currentPassword, (string)$user['password'])) {
            $errors[] = 'Current password is incorrect';
        }
        if ($newPassword === '' || strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters long';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Both passwords must be the same';
        }

        if (!empty($errors)) {
            $this->setMessage('error', implode('<br>', $errors));
            $this->redirect('user/profile');
        }

        $ok = $userModel->changePassword($userId, $newPassword);

        if ($ok) {
            $this->setMessage('success', 'Password has been changed');
        } else {
            $this->setMessage('error', 'Something went wrong');
        }

        $this->redirect('user/profile');
    }
}
