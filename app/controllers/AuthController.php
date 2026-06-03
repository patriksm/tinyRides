<?php

declare(strict_types=1);

class AuthController extends Controller
{
    public function login(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('');
        }

        $this->view('auth/login', [
      //            'title' => 'Login',
            'title' => t('nav.login'),
            '_token' => $this->csrfToken(),
        ]);
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        $this->requireCsrf();

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $errors = [];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email';
        }
        if ($password === '') {
            $errors[] = 'Please enter your password';
        }

        if (!empty($errors)) {
            $this->setMessage('error', implode('<br>', $errors));
            $this->redirect('auth/login?email=' . urlencode($email));
        }

        $userModel = $this->model('User');

        $user = $userModel->findByEmail($email);

        if (!$user || empty($user['password']) || !password_verify($password, (string)$user['password'])) {
            $this->setMessage('error', 'Email or password is wrong');
            $this->redirect('auth/login?email=' . urlencode($email));
        }

        if (isset($user['is_active']) && (int)$user['is_active'] !== 1) {
            $this->setMessage('error', 'Your account is inactive. Please contact support');
            $this->redirect('auth/login?email=' . urlencode($email));
        }

        $_SESSION['user_id'] = (int)($user['id'] ?? 0);
        $_SESSION['user_name'] = (string)($user['name'] ?? '');
        $_SESSION['user_email'] = (string)($user['email'] ?? '');
        $_SESSION['role'] = (string)($user['role'] ?? 'user');

        $userModel->updateLastLogin((int)($user['id'] ?? 0));

        $this->setMessage('success', 'Welcome');
        $this->redirect('');
    }

    public function register(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('');
        }

        $this->view('auth/register', [
//            'title' => 'Register',
            'title' => t('nav.register'),

            '_token' => $this->csrfToken(),
        ]);
    }

    public function buttonsignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/register');
        }

        $this->requireCsrf();

        $name     = trim((string)($_POST['name'] ?? ''));
        $email    = trim((string)($_POST['email'] ?? ''));
        $phone    = trim((string)($_POST['phone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['confirm_password'] ?? '');

        $errors = [];

        if ($name === '') {
            $errors[] = 'Please enter your name';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter your valid email';
        }

        if ($phone === '') {
            $errors[] = 'Please enter your phone number';
        } elseif (!preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
            $errors[] = 'Please enter valid phone number';
        }

        if ($password === '' || strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }

        if ($password !== $confirm) {
            $errors[] = 'Passwords must be the same';
        }


        $userModel = $this->model('User');

        if (empty($errors) && $userModel->emailExists($email)) {
            $errors[] = 'This email is already registered.';
        }

        if (empty($errors) && method_exists($userModel, 'phoneExists') && $userModel->phoneExists($phone)) {
            $errors[] = 'This phone number is already registered.';
        }

        if (!empty($errors)) {
            $this->setMessage('error', implode('<br>', $errors));
            $this->redirect(
                'auth/register?name=' . urlencode($name) .
                    '&email=' . urlencode($email) .
                    '&phone=' . urlencode($phone)
            );
        }

        $ok = $userModel->register([
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'password' => $password,
        ]);

        if ($ok) {
            $this->setMessage('success', 'You successfully signed up');
            $this->redirect('auth/login?email=' . urlencode($email));
        }

        $this->setMessage('error', 'Something went wrong. Please try again');
        $this->redirect(
            'auth/register?name=' . urlencode($name) .
                '&email=' . urlencode($email) .
                '&phone=' . urlencode($phone)
        );
    }


    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                (bool)($params['secure'] ?? false),
                (bool)($params['httponly'] ?? true)
            );
        }

        session_destroy();

        $this->setMessage('success', 'You have logged out');
        $this->redirect('');
    }
}
