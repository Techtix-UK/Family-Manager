<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Household Manager</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f4f4f5; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); width: 90%; max-width: 400px; box-sizing: border-box; }
        .error { background: #fee2e2; color: #dc2626; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; text-align: center; font-weight: 500; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; }
        input { width: 100%; padding: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-size: 16px; /* Prevents iOS zoom */ }
        button { width: 100%; padding: 0.75rem; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>

<div class="login-card">
    <h2 style="text-align: center; margin-top: 0;">Family Login</h2>
    
    <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['login_error']) ?></div>
        <?php unset($_SESSION['login_error']); ?>
    <?php endif; ?>

    <form action="/login" method="POST">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" inputmode="email" required autocomplete="email">
        
        <label for="pin">PIN</label>
        <input type="password" id="pin" name="pin" pattern="[0-9]*" inputmode="numeric" required>
        
        <button type="submit">Sign In</button>
    </form>
</div>

</body>
</html>