<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use RuntimeException;

class ChoreManager
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Verifies a chore and awards points to the assigned user transactionally.
     */
    public function verifyAndAwardPoints(int $choreId): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Fetch chore details with FOR UPDATE to prevent race conditions
            $stmt = $this->db->prepare("SELECT assigned_user_id, points_value, status FROM chores WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $choreId]);
            $chore = $stmt->fetch();

            if (!$chore || $chore['status'] === 'verified') {
                $this->db->rollBack();
                return false;
            }

            // 2. Update Chore Status
            $updateChore = $this->db->prepare("UPDATE chores SET status = 'verified' WHERE id = :id");
            $updateChore->execute([':id' => $choreId]);

            // 3. Award Points if user is assigned
            if (!empty($chore['assigned_user_id']) && $chore['points_value'] > 0) {
                $updateUser = $this->db->prepare("UPDATE users SET points_balance = points_balance + :points WHERE id = :user_id");
                $updateUser->execute([
                    ':points' => $chore['points_value'],
                    ':user_id' => $chore['assigned_user_id']
                ]);
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Chore verification failed: ' . $e->getMessage());
            throw new RuntimeException('Database error during transaction.');
        }
    }
}