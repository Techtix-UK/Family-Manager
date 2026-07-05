<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class AuthController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        require __DIR__ . '/../Views/login.php';
    }

    public function processLogin(): void
    {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $pin = (string)($_POST['pin'] ?? '');

        if (!$email || !$pin) {
            $this->redirectWithError('Email and PIN are required.');
        }

        $stmt = $this->db->prepare("SELECT id, name, pin_hash, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // Native PHP 8+ password verification
        if ($user && password_verify($pin, $user['pin_hash'])) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: /dashboard');
            exit;
        }

        $this->redirectWithError('Invalid credentials.');
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit;
    }

    private function redirectWithError(string $message): void
    {
        $_SESSION['login_error'] = $message;
        header('Location: /login');
        exit;
    }
}