<?php if (session_status() == PHP_SESSION_NONE) session_start(); session_destroy(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password reset - e‑lite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0a0a0f;
            --glass: rgba(20, 20, 30, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --accent: #eab308;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var(--bg-dark);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 2rem;
            width: 90%;
            max-width: 450px;
        }
        h2 { color: #10b981; margin-bottom: 1rem; }
        a { color: var(--accent); text-decoration: none; font-weight: 600; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="glass-card">
    <h2>✅ Password reset successful</h2>
    <p>You can now <a href="/login">log in</a> with your new password.</p>
</div>
</body>
</html>
