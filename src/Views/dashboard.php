<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Household Dashboard</title>
    <style>
        :root { --primary: #2563eb; --bg: #f4f4f5; --card: #ffffff; --text: #1f2937; --border: #e5e7eb; }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 1rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        h1, h2 { margin: 0; }
        .grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        @media (min-width: 768px) { .grid { grid-template-columns: repeat(3, 1fr); } }
        .card { background: var(--card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .list-item { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border); }
        .list-item:last-child { border-bottom: none; }
        .btn { background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; }
        .badge { background: #fee2e2; color: #dc2626; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
    <a href="/logout" class="btn" style="background: #ef4444;">Logout</a>
</div>

<div class="grid">
    <div class="card">
        <h2>Grocery List</h2>
        <div style="margin-top: 1rem;">
            <?php if (empty($groceries)): ?>
                <p style="color: #6b7280; font-size: 14px;">All caught up!</p>
            <?php else: ?>
                <?php foreach ($groceries as $item): ?>
                    <div class="list-item">
                        <span><?= htmlspecialchars($item['item_name']) ?></span>
                        <span style="color: #6b7280;">Qty: <?= htmlspecialchars($item['quantity']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h2>Upcoming (7 Days)</h2>
        <div style="margin-top: 1rem;">
            <?php if (empty($events)): ?>
                <p style="color: #6b7280; font-size: 14px;">No upcoming events.</p>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="list-item">
                        <span><?= htmlspecialchars($event['title']) ?></span>
                        <span style="color: #6b7280; font-size: 14px;"><?= date('M j, g:i A', strtotime($event['start_time'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h2>My Pending Chores</h2>
        <div style="margin-top: 1rem;">
            <?php if (empty($chores)): ?>
                <p style="color: #6b7280; font-size: 14px;">No pending chores assigned.</p>
            <?php else: ?>
                <?php foreach ($chores as $chore): ?>
                    <div class="list-item">
                        <div>
                            <strong><?= htmlspecialchars($chore['title']) ?></strong>
                            <?php if ($chore['points_value'] > 0): ?>
                                <span class="badge"><?= $chore['points_value'] ?> pts</span>
                            <?php endif; ?>
                        </div>
                        <span style="color: #d97706; font-size: 14px;"><?= htmlspecialchars($chore['status']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>