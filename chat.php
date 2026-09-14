<?php

session_start();

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

require_once 'db_helpers.php';

$email      = $_SESSION['email'];
$safeEmail  = htmlspecialchars($email, ENT_QUOTES);
$history    = get_messages($email);
$users      = list_users();
$trashCount = count_trashed_messages($email);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Console · User Admin</title>
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
            <a class="nav-item active" href="chat.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Chat
            </a>
            <a class="nav-item" href="users.php">
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

        <div class="sidebar-tips">
            <p class="tip-title">Quick commands</p>
            <button class="tip" data-cmd="list">list</button>
            <button class="tip" data-cmd="add the user john@xyz.com with phone +92332">add a user</button>
            <button class="tip" data-cmd="update john@xyz.com city to Lahore">update a city</button>
            <button class="tip" data-cmd="remove john@xyz.com">remove a user</button>
        </div>

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
                    <h1>Chat console</h1>
                    <p>Manage users with plain-language commands.</p>
                </div>
            </div>

            <div class="header-actions">
                <button class="icon-btn" id="themeToggle" title="Toggle theme">
                    <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>

                <form method="post" action="clear_chat.php" onsubmit="return confirm('Move all chat messages to trash?');">
                    <button class="btn-ghost" type="submit">Clear history</button>
                </form>

                <a class="btn-logout" href="logout.php">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign out
                </a>
            </div>
        </header>

        <section id="chatLog" class="chat-log" aria-live="polite">
            <?php if (empty($history)): ?>
                <div class="message bot">
                    <div class="avatar-mini bot">AI</div>
                    <div class="bubble">
                        <p>Hello. I can add, update, delete, or list users for you.</p>
                        <ul class="examples">
                            <li>add the user john@xyz.com with phone +92332</li>
                            <li>remove john@xyz.com</li>
                            <li>update john@xyz.com city to Lahore</li>
                            <li>list</li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($history as $msg): ?>
                    <div class="message <?= $msg['sender'] === 'user' ? 'user' : 'bot' ?>">
                        <div class="avatar-mini <?= $msg['sender'] === 'user' ? 'user' : 'bot' ?>">
                            <?= $msg['sender'] === 'user' ? 'You' : 'AI' ?>
                        </div>
                        <div class="bubble">
                            <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                            <time><?= date('H:i', strtotime($msg['created_at'])) ?></time>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <form id="composer" class="composer" autocomplete="off">
            <input id="message" type="text" placeholder="Type a command… e.g. add the user john@xyz.com" autofocus>
            <button class="btn-primary" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Send
            </button>
        </form>

    </main>
</div>

<script>
(function () {
    const app        = document.getElementById('app');
    const sidebarBtn = document.getElementById('sidebarToggle');
    const themeBtn   = document.getElementById('themeToggle');
    const root       = document.documentElement;

    // Sidebar collapse
    if (localStorage.getItem('sidebar') === 'collapsed') {
        app.classList.add('sidebar-collapsed');
    }
    sidebarBtn.addEventListener('click', () => {
        app.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebar', app.classList.contains('sidebar-collapsed') ? 'collapsed' : 'open');
    });

    // Theme toggle
    themeBtn.addEventListener('click', () => {
        const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const next    = current === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
    });

    // Chat logic
    const chatLog   = document.getElementById('chatLog');
    const composer  = document.getElementById('composer');
    const messageIn = document.getElementById('message');

    function scrollDown() { chatLog.scrollTop = chatLog.scrollHeight; }

    function timeNow() {
        const d = new Date();
        return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }

    function renderMessage(text, kind) {
        const wrap = document.createElement('div');
        wrap.className = 'message ' + kind;

        const avatar = document.createElement('div');
        avatar.className = 'avatar-mini ' + kind;
        avatar.textContent = kind === 'user' ? 'You' : 'AI';

        const bubble = document.createElement('div');
        bubble.className = 'bubble';

        const p = document.createElement('p');
        p.textContent = text;

        const t = document.createElement('time');
        t.textContent = timeNow();

        bubble.appendChild(p);
        bubble.appendChild(t);
        wrap.appendChild(avatar);
        wrap.appendChild(bubble);
        chatLog.appendChild(wrap);
        scrollDown();
    }

    function showTyping() {
        const wrap = document.createElement('div');
        wrap.className = 'message bot typing';
        wrap.id = 'typingIndicator';

        const avatar = document.createElement('div');
        avatar.className = 'avatar-mini bot';
        avatar.textContent = 'AI';

        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.innerHTML = '<span class="dot"></span><span class="dot"></span><span class="dot"></span>';

        wrap.appendChild(avatar);
        wrap.appendChild(bubble);
        chatLog.appendChild(wrap);
        scrollDown();
    }

    function hideTyping() {
        const el = document.getElementById('typingIndicator');
        if (el) el.remove();
    }

    async function send(text) {
        renderMessage(text, 'user');
        showTyping();

        try {
            const res  = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await res.json();
            hideTyping();
            renderMessage(data.reply || 'No response.', 'bot');
        } catch {
            hideTyping();
            renderMessage('Network error. Please try again.', 'bot');
        }
    }

    composer.addEventListener('submit', e => {
        e.preventDefault();
        const text = messageIn.value.trim();
        if (!text) return;
        messageIn.value = '';
        send(text);
    });

    document.querySelectorAll('.tip').forEach(btn => {
        btn.addEventListener('click', () => {
            messageIn.value = btn.dataset.cmd;
            messageIn.focus();
        });
    });

    window.addEventListener('load', scrollDown);
})();
</script>
</body>
</html>