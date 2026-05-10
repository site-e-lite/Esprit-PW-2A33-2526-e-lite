<section class="auth-page">
<div class="auth-container glass-card">
    <h2><i class="fas fa-key"></i> Connexion</h2>
    <p class="auth-subtitle">Connecte-toi pour acceder au forum front office ou back office selon ton role.</p>
    <?php if (isset($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= $basePath ?? '' ?>/login" class="auth-form">
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Mot de passe</label><input type="password" name="password" required></div>
        <div class="form-group">
            <label>Combien font <?= $_SESSION['captcha_a'] ?> + <?= $_SESSION['captcha_b'] ?> ?</label>
            <input type="text" name="captcha" required>
        </div>
        <button type="submit" class="btn-primary w-100">Se connecter</button>
    </form>
    <div class="social-login">
        <p class="social-divider"><span>Ou se connecter avec</span></p>
        <div class="social-buttons">
            <a href="/gestioncours/google_login.php" class="social-btn google"><i class="fab fa-google"></i> Google</a>
        </div>
    </div>
    <p class="auth-links"><a href="<?= $basePath ?? '' ?>/forgot">Mot de passe oublie ?</a></p>
    <p class="auth-links">Pas encore de compte ? <a href="<?= $basePath ?? '' ?>/register">Inscription</a></p>
</div>
</section>
