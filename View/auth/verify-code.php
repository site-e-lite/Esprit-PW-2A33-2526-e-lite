<?php if (session_status() == PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérifier le code - e-lite</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(10px);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .auth-header i {
            color: #667eea;
            font-size: 32px;
        }
        
        .auth-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 5px;
            font-weight: bold;
            transition: all 0.3s;
            font-family: 'Courier New', monospace;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert.error {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }
        
        .alert.success {
            background-color: #efe;
            border: 1px solid #cfc;
            color: #3c3;
        }
        
        .alert i {
            flex-shrink: 0;
        }
        
        .form-links {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .form-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-links a:hover {
            color: #764ba2;
        }
        
        .info-box {
            background-color: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
            line-height: 1.5;
        }
        
        .attempt-warning {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2><i class="fas fa-shield-alt"></i> Vérifier le code</h2>
            <p>Entrez le code reçu par email</p>
        </div>
        
        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['flash']['success']) ?></span>
            </div>
            <?php unset($_SESSION['flash']['success']); ?>
        <?php endif; ?>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i> Entrez le code à 6 chiffres. Il expire dans 15 minutes.
        </div>
        
        <form method="POST" action="/verify-code">
            <div class="form-group">
                <label for="code">Code de vérification</label>
                <input type="text" id="code" name="code" pattern="[0-9]{6}" maxlength="6" placeholder="000000" required autocomplete="off" autofocus>
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="fas fa-check"></i> Vérifier le code
            </button>
            
            <?php if (isset($_SESSION['attempt_count'])): ?>
                <div class="attempt-warning">
                    <strong>Tentatives restantes:</strong> <?= 5 - $_SESSION['attempt_count'] ?>/5
                </div>
            <?php endif; ?>
        </form>
        
        <div class="form-links">
            <a href="/forgot"><i class="fas fa-arrow-left"></i> Envoyer un nouveau code</a>
        </div>
    </div>
</body>
</html>
