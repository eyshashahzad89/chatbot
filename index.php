<?php

session_start();

if (isset($_SESSION['email'])) {
    header('Location: chat.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · User Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
<body class="login-body">

<div class="login-bg">
    <span class="orb orb-1"></span>
    <span class="orb orb-2"></span>
</div>

<main class="login-card">
    <div class="login-mark">UA</div>

    <h1>Sign in</h1>
    <p class="login-sub">Enter your registered email to continue.</p>

    <form id="loginForm" novalidate>
        <label class="field">
            <span>Email address</span>
            <div class="input-wrap">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-10 6L2 7"/>
                </svg>
                <input id="email" type="email" value="admin@xyz.com" autocomplete="email" autofocus>
            </div>
        </label>

        <button id="submitBtn" type="submit">
            <span class="btn-label">Continue</span>
        </button>

        <p id="error" class="error" role="alert"></p>
    </form>
</main>

<script>
const form      = document.getElementById('loginForm');
const emailBox  = document.getElementById('email');
const submitBtn = document.getElementById('submitBtn');
const errorBox  = document.getElementById('error');
const btnLabel  = submitBtn.querySelector('.btn-label');

form.addEventListener('submit', async e => {
    e.preventDefault();
    errorBox.textContent = '';

    const email = emailBox.value.trim();
    if (!email) {
        errorBox.textContent = 'Email is required.';
        return;
    }

    submitBtn.disabled = true;
    btnLabel.textContent = 'Signing in…';

    try {
        const res  = await fetch('login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });
        const data = await res.json();

        if (data.ok) {
            window.location.href = 'chat.php';
            return;
        }
        errorBox.textContent = data.error || 'Sign in failed.';
    } catch {
        errorBox.textContent = 'Network error. Please try again.';
    } finally {
        submitBtn.disabled = false;
        btnLabel.textContent = 'Continue';
    }
});
</script>
</body>
</html>