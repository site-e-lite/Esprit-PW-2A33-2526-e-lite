<div class="auth-container glass-card">
    <h2><i class="fas fa-user-plus"></i> Inscription</h2>
    <?php if (isset($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= $basePath ?? '' ?>/register">
        <div class="form-group"><label>Nom</label><input type="text" name="nom" required></div>
        <div class="form-group"><label>Prénom</label><input type="text" name="prenom" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group">
            <label>Rôle</label>
            <select name="role">
                <?php foreach ($roles as $role): ?>
                    <?php if (strtolower($role['nom']) !== 'admin'): ?>
                        <option value="<?= $role['idRole'] ?>"><?= htmlspecialchars($role['nom']) ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Mot de passe (min. 6)</label><input type="password" name="password" required></div>
        <div class="form-group"><label>Confirmer</label><input type="password" name="confirm_password" required></div>
        <button type="submit" class="btn-primary">S'inscrire</button>
    </form>
    <p><a href="<?= $basePath ?? '' ?>/login">Déjà inscrit ? Connectez-vous</a></p>
</div>
