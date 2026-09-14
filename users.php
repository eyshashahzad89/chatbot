<?php

session_start();

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

require_once 'db_helpers.php';

$email      = $_SESSION['email'];
$safeEmail  = htmlspecialchars($email, ENT_QUOTES);
$users      = list_users();
$trashCount = count_trashed_messages($email);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users · User Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <script>
        (function () {
            try {
                const saved = localStorage.getItem('theme');
                if (saved === 'light' || saved === 'dark') {
                    document.documentElement.setAttribute('data-theme', saved);
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="app-body">

<div class="app" id="app">

    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">UA</span>
            <div>
                <strong>User Admin</strong>
                <small>Console</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a class="nav-item" href="chat.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Chat
            </a>
            <a class="nav-item active" href="users.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Users
                <span class="badge"><?= count($users) ?></span>
            </a>
            <a class="nav-item" href="trash.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                Trash
                <?php if ($trashCount > 0): ?>
                    <span class="badge"><?= $trashCount ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($email, 0, 1)) ?></div>
            <div class="meta">
                <strong><?= $safeEmail ?></strong>
                <small>Signed in</small>
            </div>
            <a class="logout" href="logout.php" title="Sign out">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
    </aside>

    <main class="main">

        <header class="main-header">
            <div class="header-left">
                <button class="icon-btn" id="sidebarToggle" title="Toggle sidebar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div>
                    <h1>Users</h1>
                    <p>All registered users in the system.</p>
                </div>
            </div>

            <div class="header-actions">
                <button class="icon-btn" id="themeToggle" title="Toggle theme">
                    <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>

                <a class="btn-ghost" href="chat.php">Back to chat</a>

                <a class="btn-logout" href="logout.php">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign out
                </a>
            </div>
        </header>

        <div class="table-wrap">
            <?php if (empty($users)): ?>
                <p class="empty">No users yet. Add one from the chat console.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>City</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><span class="mono"><?= htmlspecialchars($u['email']) ?></span></td>
                                <td><?= htmlspecialchars($u['name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($u['city'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
(function () {
    const app        = document.getElementById('app');
    const sidebarBtn = document.getElementById('sidebarToggle');
    const themeBtn   = document.getElementById('themeToggle');
    const root       = document.documentElement;

    if (localStorage.getItem('sidebar') === 'collapsed') {
        app.classList.add('sidebar-collapsed');
    }
    sidebarBtn.addEventListener('click', () => {
        app.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebar', app.classList.contains('sidebar-collapsed') ? 'collapsed' : 'open');
    });

    themeBtn.addEventListener('click', () => {
        const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const next    = current === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
    });
})();
</script>
</body>
</html>