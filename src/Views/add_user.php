<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Add User</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f4f4f5; padding: 1rem; margin: 0; }
        .card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
        .error { background: #fee2e2; color: #dc2626; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 14px; }
        input, select { width: 100%; padding: 0.75rem; margin-bottom: 1.25rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-size: 16px; background: #fff; }
        .btn { width: 100%; padding: 0.75rem; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 600; font-size: 16px; cursor: pointer; display: block; text-align: center; text-decoration: none; box-sizing: border-box; }
        .btn-cancel { background: #f3f4f6; color: #374151; margin-top: 0.75rem; }
    </style>
</head>
<body>
<div class="card">
    <h2 style="margin-top: 0;">Add Family Member</h2>
    
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="/users/new" method="POST">
        <label>Name</label>
        <input type="text" name="name" required>
        
        <label>Email (Optional for Kids)</label>
        <input type="email" name="email">
        
        <label>Role</label>
        <select name="role" required>
            <option value="adult">Adult / Parent</option>
            <option value="child">Child</option>
        </select>
        
        <label>Login PIN</label>
        <input type="password" name="pin" pattern="[0-9]*" inputmode="numeric" required>
        
        <button type="submit" class="btn">Save User</button>
        <a href="/dashboard" class="btn btn-cancel">Cancel</a>
    </form>
</div>
</body>
</html>