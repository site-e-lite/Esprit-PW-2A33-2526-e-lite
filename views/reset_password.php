<?php if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['code_verified']) || $_SESSION['code_verified'] !== true) {
    header('Location: /forgot');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New password - e‑lite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0a0a0f;
            --glass: rgba(20, 20, 30, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --accent: #eab308;
            --text-muted: #a0a0b0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var(--bg-dark);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
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
            color: white;
        }
        button {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, var(--accent), #fde047);
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
    <h2>New password</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>New password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirm password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit">Reset password</button>
    </form>
</div>
</body>
</html>
