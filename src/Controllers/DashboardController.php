<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class DashboardController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function index(): void
    {
        $userId = (int)$_SESSION['user_id'];
        
        // 1. Fetch pending groceries
        $groceries = $this->db->query("SELECT id, item_name, quantity FROM grocery_items WHERE is_purchased = 0 ORDER BY id DESC")->fetchAll();

        // 2. Fetch upcoming events (next 7 days)
        $events = $this->db->query("SELECT title, start_time FROM calendar_events WHERE start_time >= NOW() AND start_time <= DATE_ADD(NOW(), INTERVAL 7 DAY) ORDER BY start_time ASC")->fetchAll();

        // 3. Fetch user's chores
        $stmt = $this->db->prepare("SELECT id, title, points_value, status, due_date FROM chores WHERE assigned_user_id = :user_id AND status != 'verified' ORDER BY due_date ASC");
        $stmt->execute([':user_id' => $userId]);
        $chores = $stmt->fetchAll();

        // Pass data to View (Assumes view template exists)
        require __DIR__ . '/../Views/dashboard.php';
    }
}