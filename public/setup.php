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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Household Manager Setup</title>
    <style>
        :root { --primary: #2563eb; --bg: #f3f4f6; --text: #1f2937; --border: #e5e7eb; --card: #ffffff; }
        body { background-color: var(--bg); font-family: system-ui, -apple-system, sans-serif; color: var(--text); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 1rem; }
        .card { background: var(--card); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); width: 100%; max-width: 450px; overflow: hidden; }
        .header { background: var(--primary); color: white; padding: 1.5rem; text-align: center; font-weight: 600; font-size: 1.25rem; margin: 0; }
        .content { padding: 2rem; }
        .step-indicator { font-size: 0.875rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; display: block; }
        .error { background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.875rem; border: 1px solid #f87171; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem; }
        input { width: 100%; padding: 0.75rem; margin-bottom: 1.25rem; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 1rem; transition: border-color 0.15s; }
        input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        button { width: 100%; padding: 0.75rem; background: var(--primary); color: white; border: none; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: background-color 0.15s; }
        button:hover { background: #1d4ed8; }
        code { display: block; background: #111827; color: #10b981; padding: 1rem; border-radius: 6px; font-family: ui-monospace, monospace; font-size: 0.875rem; margin-top: 1rem; text-align: center; }
        .success-msg { color: #059669; font-weight: 600; font-size: 1.125rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
<div class="card">
    <h1 class="header">Household Manager</h1>
    <div class="content">
        <span class="step-indicator">Step <?= $step ?> of 3</span>
        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

        <?php if ($step === 1): ?>
            <p style="margin-top: 0; color: #4b5563; font-size: 0.95rem; margin-bottom: 1.5rem;">Enter your MySQL database credentials.</p>
            <form method="POST">
                <label>Database Host</label><input type="text" name="db_host" value="127.0.0.1" required>
                <label>Database Name</label><input type="text" name="db_name" required>
                <label>Database User</label><input type="text" name="db_user" required>
                <label>Database Password</label><input type="password" name="db_pass">
                <button type="submit">Connect & Continue</button>
            </form>
        <?php elseif ($step === 2): ?>
            <p style="margin-top: 0; color: #4b5563; font-size: 0.95rem; margin-bottom: 1.5rem;">Create the primary administrator account.</p>
            <form method="POST">
                <label>Administrator Name</label><input type="text" name="admin_name" required>
                <label>Email Address</label><input type="email" name="admin_email" required>
                <label>Login PIN (Numbers only)</label><input type="password" name="admin_pin" pattern="[0-9]*" inputmode="numeric" required>
                <button type="submit">Install Database</button>
            </form>
        <?php elseif ($step === 3): ?>
            <div style="text-align: center;">
                <div class="success-msg">Installation Complete</div>
                <p style="color: #4b5563; font-size: 0.95rem;">For security, you must delete the setup file manually before logging in.</p>
                <code>rm public/setup.php</code>
                <a href="/login" style="display: inline-block; margin-top: 1.5rem; color: var(--primary); font-weight: 600; text-decoration: none;">Proceed to Login &rarr;</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>