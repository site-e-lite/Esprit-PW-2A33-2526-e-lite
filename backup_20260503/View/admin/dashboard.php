<?php
/** @var array $users */
/** @var array $roles */
/** @var int $currentRoleId */
?>
<div class="admin-dashboard glass-card">
    <h1>Administration des utilisateurs</h1>
    <p><a href="/profile" class="btn-outline">Modifier mon profil</a></p>
    <table class="user-table">
        <thead>
            <tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['idUser'] ?></td>
                <td><?= htmlspecialchars($u['nom']).' '.htmlspecialchars($u['prenom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role_nom']) ?></td>
                <td><span class="status-badge <?= $u['statut'] === 'actif' ? 'status-active' : 'status-inactive' ?>"><?= $u['statut'] === 'actif' ? 'Actif' : 'Banni' ?></span></td>
                <td class="actions">
                    <?php if ($u['idUser'] != $_SESSION['user_id']): ?>
                        <!-- Ban button -->
                        <form method="POST" style="display:inline-block">
                            <input type="hidden" name="user_id" value="<?= $u['idUser'] ?>">
                            <button type="submit" name="ban_user" class="btn-outline">Bannir</button>
                        </form>
                        <!-- Delete button -->
                        <form method="POST" style="display:inline-block">
                            <input type="hidden" name="user_id" value="<?= $u['idUser'] ?>">
                            <button type="submit" name="delete_user" class="btn-danger" onclick="return confirm('Supprimer définitivement ?')">Supprimer</button>
                        </form>
                        <!-- Role change dropdown (always shown for debugging) -->
                        <form method="POST" style="display:inline-block">
                            <input type="hidden" name="user_id" value="<?= $u['idUser'] ?>">
                            <select name="new_role">
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['idRole'] ?>" <?= $r['idRole'] == $u['idRole'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="change_role" class="btn-primary">Changer rôle</button>
                        </form>
                    <?php else: ?>
                        <span class="self-badge">(vous)</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
