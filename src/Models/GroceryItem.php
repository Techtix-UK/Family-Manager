<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use InvalidArgumentException;
use RuntimeException;

class GroceryItem
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Creates a new grocery item securely.
     *
     * @param array $input Raw associative array from POST/JSON payload
     * @return int The ID of the inserted item
     * @throws InvalidArgumentException|RuntimeException
     */
    public function create(array $input): int
    {
        // 1. Defensive Input Parsing & Sanitization
        $itemName = isset($input['item_name']) ? trim((string)$input['item_name']) : '';
        $quantity = isset($input['quantity']) ? trim((string)$input['quantity']) : '1';
        
        $addedBy = null;
        if (!empty($input['added_by_user_id']) && is_numeric($input['added_by_user_id'])) {
            $addedBy = (int)$input['added_by_user_id'];
        }

        if ($itemName === '') {
            throw new InvalidArgumentException('Item name cannot be empty.');
        }

        // 2. Execution
        $sql = "INSERT INTO grocery_items (item_name, quantity, added_by_user_id) 
                VALUES (:item_name, :quantity, :added_by)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':item_name', $itemName, PDO::PARAM_STR);
            $stmt->bindValue(':quantity', $quantity, PDO::PARAM_STR);
            $stmt->bindValue(':added_by', $addedBy, $addedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            
            $stmt->execute();
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Failed to insert grocery item: ' . $e->getMessage());
            throw new RuntimeException('Database error occurred while saving the item.');
        }
    }

    /**
     * Fetches all unpurchased items.
     *
     * @return array
     */
    public function getPendingItems(): array
    {
        $sql = "SELECT id, item_name, quantity, added_by_user_id, created_at 
                FROM grocery_items 
                WHERE is_purchased = 0 
                ORDER BY id DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}