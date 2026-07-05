<?php
declare(strict_types=1);

session_start();

$configDir = __DIR__ . '/../config';
$configFile = $configDir . '/database.php';
$lockFile = $configDir . '/setup.lock';

// Pre-flight Security Check
if (file_exists($lockFile)) {
    http_response_code(403);
    die('<h1>Setup Locked</h1><p>Setup has already been completed. Delete this file for security.</p>');
}

if (!is_writable($configDir)) {
    die("<h1>Permission Denied</h1><p>The directory <code>$configDir</code> must be writable by the web server.</p>");
}

$step = (int)($_GET['step'] ?? 1);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        $host = trim($_POST['db_host'] ?? '');
        $name = trim($_POST['db_name'] ?? '');
        $user = trim($_POST['db_user'] ?? '');
        $pass = trim($_POST['db_pass'] ?? '');

        try {
            $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            $configContent = "<?php\ndeclare(strict_types=1);\n\nfunction getDatabaseConnection(): PDO {\n"
                . "    \$dsn = 'mysql:host=" . addslashes($host) . ";dbname=" . addslashes($name) . ";charset=utf8mb4';\n"
                . "    \$options = [\n"
                . "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n"
                . "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n"
                . "        PDO::ATTR_EMULATE_PREPARES => false,\n"
                . "    ];\n"
                . "    return new PDO(\$dsn, '" . addslashes($user) . "', '" . addslashes($pass) . "', \$options);\n"
                . "}\n";

            if (file_put_contents($configFile, $configContent) === false) {
                throw new Exception('Failed to write to config/database.php.');
            }

            header('Location: setup.php?step=2');
            exit;
        } catch (Throwable $e) {
            $error = 'Database Connection Failed: ' . htmlspecialchars($e->getMessage());
        }
    } elseif ($step === 2) {
        require_once $configFile;
        
        $adminName = trim($_POST['admin_name'] ?? '');
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminPin = trim($_POST['admin_pin'] ?? '');

        if (empty($adminName) || empty($adminEmail) || empty($adminPin)) {
            $error = 'All fields are required.';
        } else {
            try {
                $db = getDatabaseConnection();
                
                // Complete DDL Schema Execution
                $schema = "
                    SET FOREIGN_KEY_CHECKS=0;
                    
                    CREATE TABLE IF NOT EXISTS users (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(255) UNIQUE NULL,
                        pin_hash VARCHAR(255) NOT NULL,
                        role ENUM('adult', 'child') NOT NULL DEFAULT 'adult',
                        points_balance INT UNSIGNED NOT NULL DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    
                    CREATE TABLE IF NOT EXISTS chores (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        points_value INT UNSIGNED NOT NULL DEFAULT 0,
                        assigned_user_id INT UNSIGNED NULL,
                        status ENUM('pending', 'completed', 'verified') NOT NULL DEFAULT 'pending',
                        due_date DATE NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        CONSTRAINT fk_chores_user FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                    CREATE TABLE IF NOT EXISTS grocery_items (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        item_name VARCHAR(255) NOT NULL,
                        quantity VARCHAR(50) NOT NULL DEFAULT '1',
                        is_purchased TINYINT(1) NOT NULL DEFAULT 0,
                        added_by_user_id INT UNSIGNED NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        CONSTRAINT fk_grocery_user FOREIGN KEY (added_by_user_id) REFERENCES users(id) ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                    CREATE TABLE IF NOT EXISTS calendar_events (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        start_time DATETIME NOT NULL,
                        end_time DATETIME NOT NULL,
                        created_by_user_id INT UNSIGNED NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        CONSTRAINT fk_calendar_user FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
                        INDEX idx_event_dates (start_time, end_time)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                    SET FOREIGN_KEY_CHECKS=1;
                ";
                
                $db->exec($schema);

                $stmt = $db->prepare("INSERT INTO users (name, email, pin_hash, role) VALUES (:name, :email, :pin_hash, 'adult')");
                $stmt->execute([
                    ':name' => $adminName,
                    ':email' => filter_var($adminEmail, FILTER_SANITIZE_EMAIL),
                    ':pin_hash' => password_hash($adminPin, PASSWORD_DEFAULT)
                ]);

                file_put_contents($lockFile, 'LOCKED: ' . date('c'));

                header('Location: setup.php?step=3');
                exit;
            } catch (Throwable $e) {
                $error = 'Setup Failed: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Household Manager Setup</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f4f4f5; display: flex; justify-content: center; padding: 2rem; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 500px; box-sizing: border-box; }
        .error { color: #dc2626; background: #fee2e2; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; font-weight: 500; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #374151; }
        input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 0.75rem; background: #2563eb; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        code { display: block; background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 4px; margin-top: 1rem; word-break: break-all; }
    </style>
</head>
<body>

<div class="card">
    <h2>Setup - Step <?= $step ?></h2>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <?php if ($step === 1): ?>
        <p>Enter your MySQL database credentials. The database must exist.</p>
        <form method="POST">
            <label for="db_host">Database Host</label>
            <input type="text" id="db_host" name="db_host" value="127.0.0.1" required>
            
            <label for="db_name">Database Name</label>
            <input type="text" id="db_name" name="db_name" required>
            
            <label for="db_user">Database User</label>
            <input type="text" id="db_user" name="db_user" required>
            
            <label for="db_pass">Database Password</label>
            <input type="password" id="db_pass" name="db_pass">
            
            <button type="submit">Test Connection & Save</button>
        </form>
    <?php elseif ($step === 2): ?>
        <p>Create your primary Administrator account. Tables will be deployed automatically.</p>
        <form method="POST">
            <label for="admin_name">Admin Name (e.g., Mom / Dad)</label>
            <input type="text" id="admin_name" name="admin_name" required>
            
            <label for="admin_email">Admin Email</label>
            <input type="email" id="admin_email" name="admin_email" required>
            
            <label for="admin_pin">Login PIN (Numeric for mobile ease)</label>
            <input type="password" id="admin_pin" name="admin_pin" pattern="[0-9]*" inputmode="numeric" required>
            
            <button type="submit">Build Database & Create Admin</button>
        </form>
    <?php elseif ($step === 3): ?>
        <h3 style="color: #16a34a; margin-top: 0;">Setup Complete!</h3>
        <p>The database schema has been deployed successfully.</p>
        <p><strong>CRITICAL SECURITY ACTION REQUIRED:</strong></p>
        <p>Delete this file from your server immediately.</p>
        <code>rm public/setup.php</code>
        <a href="/login" style="display: block; text-align: center; margin-top: 1.5rem; color: #2563eb; text-decoration: none; font-weight: bold;">Proceed to Login &rarr;</a>
    <?php endif; ?>
</div>

</body>
</html>