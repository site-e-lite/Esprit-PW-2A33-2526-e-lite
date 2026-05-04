<?php if (session_status() == PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify code - e‑lite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0a0a0f;
            --glass: rgba(20, 20, 30, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --accent: #eab308;
            --accent-glow: rgba(234, 179, 8, 0.3);
            --text-light: #f0f0f0;
            --text-muted: #a0a0b0;
            --card-radius: 28px;
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var(--bg-dark);
            font-family: 'Inter', sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(234, 179, 8, 0.03) 0%, transparent 50%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: var(--card-radius);
            padding: 2rem;
            width: 90%;
            max-width: 450px;
        }
        h2 {
            font-size: 2rem;
            background: linear-gradient(135deg, #fff, var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 1rem;
        }
        .form-group { margin-bottom: 1.5rem; }
        label { color: var(--text-muted); display: block; margin-bottom: 0.5rem; }
        input {
            width: 100%;
            padding: 0.8rem;
            background: rgba(0,0,0,0.4);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: var(--text-light);
            font-size: 1rem;
        }
        input:focus { outline: none; border-color: var(--accent); }
        button {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, var(--accent), #fde047);
            color: #0a0a0f;
            border: none;
            border-radius: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        .error { background: rgba(239,68,68,0.2); border: 1px solid #ef4444; color: #f87171; padding: 0.8rem; border-radius: 12px; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="glass-card">
    <h2>Verify code</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>6-digit code</label>
            <input type="text" name="code" maxlength="6" required placeholder="123456">
        </div>
        <button type="submit">Verify</button>
    </form>
</div>
</body>
</html>
