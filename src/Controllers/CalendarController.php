<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;
use RuntimeException;

class CalendarController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(): void
    {
        require __DIR__ . '/../Views/add_event.php';
    }

    public function store(): void
    {
        $title = trim($_POST['title'] ?? '');
        $start = $_POST['start_time'] ?? '';
        $end = $_POST['end_time'] ?? '';
        $userId = (int)$_SESSION['user_id'];

        if ($title === '' || $start === '' || $end === '') {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: /events/new');
            exit;
        }

        // Standardize datetime-local input (Y-m-d\TH:i) to MySQL DATETIME (Y-m-d H:i:s)
        $startTime = date('Y-m-d H:i:s', strtotime($start));
        $endTime = date('Y-m-d H:i:s', strtotime($end));

        try {
            $stmt = $this->db->prepare("INSERT INTO calendar_events (title, start_time, end_time, created_by_user_id) VALUES (:title, :start, :end, :user_id)");
            $stmt->execute([
                ':title' => $title,
                ':start' => $startTime,
                ':end' => $endTime,
                ':user_id' => $userId
            ]);
            header('Location: /dashboard');
            exit;
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Failed to save event.';
            header('Location: /events/new');
            exit;
        }
    }
}