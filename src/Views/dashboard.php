<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Household Dashboard</title>
    <style>
        :root { --primary: #2563eb; --primary-hover: #1d4ed8; --success: #10b981; --danger: #ef4444; --bg: #f8fafc; --card: #ffffff; --text: #0f172a; --text-muted: #64748b; --border: #e2e8f0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 1.5rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        h1 { margin: 0; font-size: 1.75rem; color: #1e293b; }
        .action-bar { display: flex; gap: 0.75rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        @media (min-width: 768px) { .grid { grid-template-columns: repeat(3, 1fr); } }
        .card { background: var(--card); border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05); border: 1px solid var(--border); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.75rem; }
        .card-header h2 { margin: 0; font-size: 1.25rem; color: #334155; }
        .list-item { display: flex; justify-content: space-between; align-items: center; padding: 0.875rem 0; border-bottom: 1px solid var(--border); }
        .list-item:last-child { border-bottom: none; padding-bottom: 0; }
        .item-primary { font-weight: 500; color: #1e293b; }
        .item-secondary { color: var(--text-muted); font-size: 0.875rem; }
        .btn { background: var(--primary); color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; cursor: pointer; text-decoration: none; font-size: 0.875rem; font-weight: 600; transition: background 0.15s; }
        .btn:hover { background: var(--primary-hover); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.75rem; border-radius: 6px; }
        .btn-success { background: var(--success); }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: var(--danger); }
        .btn-danger:hover { background: #dc2626; }
        .btn-outline { background: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-outline:hover { background: #eff6ff; }
        .badge { background: #fef2f2; color: #dc2626; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; margin-left: 0.5rem; }
        .empty-state { color: var(--text-muted); font-size: 0.875rem; text-align: center; padding: 2rem 0; font-style: italic; }
    </style>
</head>
<body>

<div class="header">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h1>
    <a href="/logout" class="btn btn-danger">Logout</a>
</div>

<div class="action-bar">
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'adult'): ?>
        <a href="/users/new" class="btn btn-outline">+ Add Family Member</a>
    <?php endif; ?>
</div>

<div class="grid">
    <div class="card">
        <div class="card-header">
            <h2>Grocery List</h2>
            <a href="/grocery/new" class="btn btn-sm btn-outline">+ Add</a>
        </div>
        <div>
            <?php if (empty($groceries)): ?>
                <div class="empty-state">All caught up! List is empty.</div>
            <?php else: ?>
                <?php foreach ($groceries as $item): ?>
                    <div class="list-item">
                        <span class="item-primary"><?= htmlspecialchars($item['item_name']) ?></span>
                        <span class="item-secondary">Qty: <?= htmlspecialchars($item['quantity']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Upcoming (7 Days)</h2>
            <a href="/events/new" class="btn btn-sm btn-success">+ Event</a>
        </div>
        <div>
            <?php if (empty($events)): ?>
                <div class="empty-state">No upcoming events scheduled.</div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="list-item">
                        <span class="item-primary"><?= htmlspecialchars($event['title']) ?></span>
                        <span class="item-secondary"><?= date('M j, g:i A', strtotime($event['start_time'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>My Pending Chores</h2>
            <button class="btn btn-sm btn-outline">+ Add</button> </div>
        <div>
            <?php if (empty($chores)): ?>
                <div class="empty-state">No pending chores assigned to you.</div>
            <?php else: ?>
                <?php foreach ($chores as $chore): ?>
                    <div class="list-item">
                        <div>
                            <span class="item-primary"><?= htmlspecialchars($chore['title']) ?></span>
                            <?php if ($chore['points_value'] > 0): ?>
                                <span class="badge"><?= $chore['points_value'] ?> pts</span>
                            <?php endif; ?>
                        </div>
                        <span style="color: #d97706; font-size: 0.875rem; font-weight: 500; text-transform: capitalize;">
                            <?= htmlspecialchars($chore['status']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>