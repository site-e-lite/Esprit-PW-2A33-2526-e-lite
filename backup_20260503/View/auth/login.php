<div class="auth-container glass-card">
    <h2><i class="fas fa-key"></i> Connexion</h2>
    <?php if (isset($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert success"><?= $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>
    <form method="POST" action="/login">
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Mot de passe</label><input type="password" name="password" required></div>
        <button type="submit" class="btn-primary">Se connecter</button>
    </form>
    <div class="social-login">
        <p class="social-divider"><span>Ou se connecter avec</span></p>
        <div class="social-buttons">
            <a href="/login/google" class="social-btn google"><i class="fab fa-google"></i> Google</a>
        </div>
    </div>
    <p style="margin-top:1rem;"><a href="/forgot">Mot de passe oublié ?</a></p>
</div>
