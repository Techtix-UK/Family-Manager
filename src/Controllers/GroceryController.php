<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Models\GroceryItem;

class GroceryController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(): void
    {
        require __DIR__ . '/../Views/add_grocery.php';
    }

    public function store(): void
    {
        $input = [
            'item_name' => $_POST['item_name'] ?? '',
            'quantity' => $_POST['quantity'] ?? '1',
            'added_by_user_id' => $_SESSION['user_id'] ?? null
        ];

        try {
            $model = new GroceryItem($this->db);
            $model->create($input);
            
            header('Location: /dashboard');
            exit;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /grocery/new');
            exit;
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = 'Failed to add item to the database.';
            header('Location: /grocery/new');
            exit;
        }
    }
}