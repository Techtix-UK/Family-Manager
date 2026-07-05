<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Add Grocery Item</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; padding: 1rem; margin: 0; }
        .card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; border: 1px solid #e2e8f0; }
        .error { background: #fee2e2; color: #dc2626; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 500; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155; font-size: 14px; }
        input { width: 100%; padding: 0.75rem; margin-bottom: 1.25rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; background: #fff; }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .btn { width: 100%; padding: 0.75rem; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 600; font-size: 16px; cursor: pointer; display: block; text-align: center; text-decoration: none; box-sizing: border-box; }
        .btn:hover { background: #1d4ed8; }
        .btn-cancel { background: #f1f5f9; color: #475569; margin-top: 0.75rem; }
        .btn-cancel:hover { background: #e2e8f0; }
    </style>
</head>
<body>
<div class="card">
    <h2 style="margin-top: 0; color: #1e293b;">Add to Grocery List</h2>
    
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="/grocery/new" method="POST">
        <label>Item Name</label>
        <input type="text" name="item_name" placeholder="e.g. Milk, Apples" required autofocus>
        
        <label>Quantity / Amount</label>
        <input type="text" name="quantity" value="1" required>
        
        <button type="submit" class="btn">Add Item</button>
        <a href="/dashboard" class="btn btn-cancel">Cancel</a>
    </form>
</div>
</body>
</html>