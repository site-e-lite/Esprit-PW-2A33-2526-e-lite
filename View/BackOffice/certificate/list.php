<?php
/**
 * View/BackOffice/certificate/list.php
 * Interface admin — liste de tous les certificats générés.
 */
require_once __DIR__ . '/../../../Controller/CertificateController.php';

$baseUrl    = '/gestioncours';
$pageTitle  = 'BackOffice — Certificats';

$certController = new CertificateController();
$certificates   = $certController->listAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<section>
    <h2><i class="fas fa-certificate" style="color:#10b981;"></i> Gestion des Certificats</h2>
    <p style="color:#aaa; margin-bottom:2rem;">
        Liste de tous les certificats générés automatiquement lors de la complétion d'un cours à 100%.
    </p>

    <!-- Stat rapide -->
    <div style="display:flex; gap:1.5rem; margin-bottom:2rem; flex-wrap:wrap;">
        <div class="glass-card" style="padding:1.2rem 2rem; display:flex; align-items:center; gap:1rem;">
            <i class="fas fa-certificate" style="color:#10b981; font-size:1.8rem;"></i>
            <div>
                <p style="color:#aaa; font-size:.8rem; margin:0;">Total certificats</p>
                <p style="font-size:1.8rem; font-weight:800; margin:0; color:#f4f4f5;">
                    <?= count($certificates) ?>
                </p>
            </div>
        </div>
    </div>

    <?php if (empty($certificates)): ?>
        <div style="
            text-align:center; padding:4rem 2rem;
            background:rgba(255,255,255,.03); border:1px dashed rgba(255,255,255,.1);
            border-radius:16px;
        ">
            <i class="fas fa-certificate" style="font-size:3rem; color:#374151; display:block; margin-bottom:1rem;"></i>
            <p style="color:#6b7280;">Aucun certificat généré pour le moment.</p>
        </div>

    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Référence</th>
                    <th>Utilisateur</th>
                    <th>Cours</th>
                    <th>Date d'obtention</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certificates as $cert): ?>
                    <tr>
                        <td><?= (int)$cert['id'] ?></td>
                        <td>
                            <code style="color:#10b981; background:rgba(16,185,129,.1);
                                         padding:.2rem .5rem; border-radius:4px; font-size:.85rem;">
                                CERT-<?= str_pad((string)$cert['id'], 6, '0', STR_PAD_LEFT) ?>
                            </code>
                        </td>
                        <td>
                            <span style="color:#d1d5db;">Utilisateur #<?= (int)$cert['user_id'] ?></span>
                        </td>
                        <td>
                            <a href="<?= $baseUrl ?>/View/FrontOffice/course/show.php?id=<?= (int)$cert['course_id'] ?>"
                               style="color:#a78bfa; font-weight:600;">
                                <?= htmlspecialchars($cert['course_titre']) ?>
                            </a>
                        </td>
                        <td>
                            <span style="color:#9ca3af;">
                                <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($cert['date_obtained']))) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= $baseUrl ?>/View/FrontOffice/certificate/view.php?id=<?= (int)$cert['id'] ?>"
                               style="color:#10b981; font-weight:600; text-decoration:none;"
                               target="_blank">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
