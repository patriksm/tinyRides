<?php

declare(strict_types=1);

class User extends Model
{
    protected string $table = 'users';

    protected function allowedColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'address',
            'password',
            'role',
            'is_verified',
            'is_active',
            'created_at',
            'updated_at',
            'last_login',
        ];
    }

    public function findByEmail(string $email): array|false
    {
        return $this->findOneBy('email', $email);
    }

    public function emailExists(string $email): bool
    {
        return (bool)$this->findOneBy('email', $email);
    }

    public function phoneExists(string $phone): bool
    {
        $user = $this->findOneBy('phone', $phone);
        return $user !== false;
    }


    public function register(array $data): bool
    {
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new InvalidArgumentException("Missing required fields for register");
        }

        $data['password'] = password_hash((string)$data['password'], PASSWORD_DEFAULT);
        $data['role'] = $data['role'] ?? 'user';
        $data['is_verified'] = $data['is_verified'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');

        return $this->create($data) !== false;
    }

    public function updateLastLogin(int $id): bool
    {
        return $this->update($id, [
            'last_login' => date('Y-m-d H:i:s'),
        ]);
    }


    public function changePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    public function getUserCars(int $userId): array
    {
        $this->db->query("SELECT * FROM cars WHERE user_id = :id ORDER BY created_at DESC");
        $this->db->bind(':id', $userId);
        return $this->db->fetchAll();
    }
}
