<div class="profile-container glass-card">
    <h2>Modifier mon profil</h2>
    <p class="profile-id"><strong>Votre ID :</strong> <?= $_SESSION['user_id'] ?></p>
    <?php if (isset($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert success"><?= htmlspecialchars($_SESSION['flash']['success']); unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= $basePath ?? '' ?>/profile" class="profile-form">
        <div class="form-group"><label>Nom</label><input type="text" name="nom" value="<?= htmlspecialchars($user['nom']??'') ?>" required></div>
        <div class="form-group"><label>Prénom</label><input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']??'') ?>" required></div>
        <div class="form-group"><label>Téléphone</label><input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone']??'') ?>"></div>
        <div class="form-group"><label>Bio</label><textarea name="bio"><?= htmlspecialchars($user['bio']??'') ?></textarea></div>
        <button type="submit" class="btn-primary profile-action">Mettre à jour</button>
    </form>

    <hr class="profile-separator">
    <h3><i class="fas fa-image"></i> Photo de profil</h3>
    <div class="profile-avatar-preview">
        <?php 
            $photoUrl = null;
            if (!empty($user['photo'])) {
                $photoUrl = ($basePath ?? '') . '/' . ltrim($user['photo'], '/');
            } else {
                $photoUrl = ($basePath ?? '') . '/assets/default-avatar.png';
            }
        ?>
        <img src="<?= htmlspecialchars($photoUrl) ?>" class="profile-avatar-img" alt="Profile picture">
    </div>
    <form method="POST" action="<?= $basePath ?? '' ?>/profile" enctype="multipart/form-data" class="profile-form">
        <div class="form-group">
            <label>Choisir une image (JPG, PNG, GIF, WEBP – max 5 Mo)</label>
            <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
        </div>
        <button type="submit" name="upload_avatar" value="1" class="btn-primary profile-action">Télécharger la photo</button>
    </form>
    <form method="POST" action="<?= $basePath ?? '' ?>/profile/remove-photo" class="profile-inline-form">
        <button type="submit" class="btn-outline remove-photo"><i class="fas fa-trash-alt"></i> Supprimer la photo</button>
    </form>

    <hr class="profile-separator">
    <h3 class="danger-title">Zone dangereuse</h3>
    <form method="POST" action="<?= $basePath ?? '' ?>/profile/delete" class="profile-inline-form" onsubmit="return confirm('Supprimer définitivement votre compte ?')">
        <button type="submit" class="btn-danger">Supprimer mon compte</button>
    </form>
</div>
