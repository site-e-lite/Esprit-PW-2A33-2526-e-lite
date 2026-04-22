<div class="auth-container glass-card">
    <h2><i class="fas fa-user-plus"></i> Inscription</h2>
    <?php if (isset($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="/register">
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
        <div class="form-group"><label>Mot de passe (min. 6 caractères)</label><input type="password" name="password" required></div>
        <div class="form-group"><label>Confirmer mot de passe</label><input type="password" name="confirm_password" required></div>
        <button type="submit" class="btn-primary" style="width:100%"><i class="fas fa-check"></i> S'inscrire</button>
    </form>
    <p style="margin-top:1.5rem; text-align:center">Déjà inscrit ? <a href="/login" class="btn-outline" style="display:inline-block; padding:0.5rem 1rem">Connectez-vous</a></p>
</div>
