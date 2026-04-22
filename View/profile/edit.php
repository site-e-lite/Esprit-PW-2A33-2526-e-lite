<div class="profile-container glass-card">
    <h2><i class="fas fa-user-edit"></i> Modifier mon profil</h2>
    <?php if (isset($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert success"><?= htmlspecialchars($_SESSION['flash']['success']); unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>
    <form method="POST" action="/profile">
        <div class="form-group"><label>Nom</label><input type="text" name="nom" value="<?= htmlspecialchars($user['nom']??'') ?>" required></div>
        <div class="form-group"><label>Prénom</label><input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']??'') ?>" required></div>
        <div class="form-group"><label>Téléphone</label><input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone']??'') ?>"></div>
        <div class="form-group"><label>Bio</label><textarea name="bio"><?= htmlspecialchars($user['bio']??'') ?></textarea></div>
        <button type="submit" class="btn-primary" style="width:100%"><i class="fas fa-save"></i> Mettre à jour</button>
    </form>
    <hr style="margin: 2rem 0; border-color: var(--glass-border);">
    <h3 style="color: #ef4444;"><i class="fas fa-trash-alt"></i> Zone dangereuse</h3>
    <form method="POST" action="/profile/delete" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement votre compte ? Cette action est irréversible.');">
        <button type="submit" class="btn-danger" style="width:100%"><i class="fas fa-user-slash"></i> Supprimer mon compte</button>
    </form>
</div>
