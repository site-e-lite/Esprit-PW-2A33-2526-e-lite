<div class="admin-dashboard">
    <h1><i class="fas fa-users-cog"></i> Administration des utilisateurs</h1>
    <p><a href="/profile" class="btn-outline"><i class="fas fa-user-edit"></i> Modifier mon profil</a></p>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert success"><?= $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>

    <table class="user-table">
        <thead>
            <tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['idUser'] ?></td>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td><?= htmlspecialchars($u['prenom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role_nom']) ?></td>
                <td><?= $u['statut'] === 'actif' ? '<span class="badge active">Actif</span>' : '<span class="badge inactive">Banni</span>' ?></td>
                <td class="actions">
                    <?php if ($u['idUser'] != $_SESSION['user_id']): ?>
                        <!-- Ban (soft delete) button -->
                        <form method="POST" action="/admin/dashboard" style="display:inline-block;">
                            <input type="hidden" name="user_id" value="<?= $u['idUser'] ?>">
                            <button type="submit" name="ban_user" class="btn-warning" onclick="return confirm('Bannir cet utilisateur ? Il pourra être réactivé plus tard.')" title="Bannir (désactiver)"><i class="fas fa-gavel"></i> Bannir</button>
                        </form>
                        <!-- Permanent Delete button -->
                        <form method="POST" action="/admin/dashboard" style="display:inline-block;">
                            <input type="hidden" name="user_id" value="<?= $u['idUser'] ?>">
                            <button type="submit" name="delete_user_permanent" class="btn-danger" onclick="return confirm('Supprimer définitivement cet utilisateur ? Cette action est irréversible.')" title="Supprimer définitivement"><i class="fas fa-trash-alt"></i> Supprimer</button>
                        </form>
                        <!-- Role change dropdown (only for admin users) -->
                        <?php if (strtolower($_SESSION['role_nom'] ?? '') === 'admin'): ?>
                            <form method="POST" action="/admin/dashboard" style="display:inline-block;">
                                <input type="hidden" name="user_id" value="<?= $u['idUser'] ?>">
                                <select name="new_role" class="role-select">
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= $role['idRole'] ?>" <?= $role['idRole'] == $u['idRole'] ? 'selected' : '' ?>><?= htmlspecialchars($role['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="change_role" class="btn-primary"><i class="fas fa-sync-alt"></i> Changer rôle</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="self-action"><i class="fas fa-user-check"></i> (vous)</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
