<?php if (session_status() == PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - e‑lite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ============================================
           e-lite – Premium Dark Glass Design
           ============================================ */
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
            color: var(--text-light);
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
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.4);
            transition: var(--transition);
        }
        .glass-card:hover {
            border-color: rgba(234, 179, 8, 0.3);
            box-shadow: 0 25px 40px -12px rgba(234, 179, 8, 0.15);
        }
        h2 {
            font-size: 2rem;
            background: linear-gradient(135deg, #fff, var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 1.5rem;
        }
        .form-group { margin-bottom: 1.5rem; }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(0,0,0,0.4);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: var(--text-light);
            font-size: 1rem;
            transition: var(--transition);
        }
        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.2);
        }
        button {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, var(--accent), #fde047);
            color: #0a0a0f;
            border: none;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px var(--accent-glow);
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--accent-glow);
        }
        .error, .success {
            padding: 0.8rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .error { background: rgba(239,68,68,0.2); border: 1px solid #ef4444; color: #f87171; }
        .success { background: rgba(16,185,129,0.2); border: 1px solid #10b981; color: #10b981; }
        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="glass-card">
    <h2>Reset password</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Email address</label>
            <input type="email" name="email" required placeholder="you@example.com">
        </div>
        <button type="submit">Send reset code</button>
    </form>
</div>
</body>
</html>
