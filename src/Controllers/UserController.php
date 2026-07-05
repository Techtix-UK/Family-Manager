<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;
use RuntimeException;

class UserController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(): void
    {
        require __DIR__ . '/../Views/add_user.php';
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL) ?: null;
        $pin = (string)($_POST['pin'] ?? '');
        $role = $_POST['role'] === 'child' ? 'child' : 'adult';

        if ($name === '' || $pin === '') {
            $_SESSION['error'] = 'Name and PIN are required.';
            header('Location: /users/new');
            exit;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO users (name, email, pin_hash, role) VALUES (:name, :email, :pin, :role)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':pin' => password_hash($pin, PASSWORD_DEFAULT),
                ':role' => $role
            ]);
            header('Location: /dashboard');
            exit;
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Database error. Email might be in use.';
            header('Location: /users/new');
            exit;
        }
    }
}