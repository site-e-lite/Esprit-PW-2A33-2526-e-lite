<?php
require_once __DIR__ . '/../../../Controller/CertificateController.php';

$baseUrl    = '/gestioncours';
$pageTitle  = 'Mes Certificats';

// Simulation utilisateur connecté (remplacer par session réelle)
$currentUserId = 1;

$certController = new CertificateController();
$certificates   = $certController->getByUser($currentUserId);

require_once __DIR__ . '/../../includes/header.php';
?>

<section>
    <h2><i class="fas fa-certificate" style="color:#10b981;"></i> Mes Certificats</h2>
    <p style="color:#aaa; margin-bottom:2rem;">
        Retrouvez ici tous vos certificats de réussite obtenus après avoir complété un cours à 100%.
    </p>

    <?php if (empty($certificates)): ?>
        <!-- État vide -->
        <div style="
            text-align:center; padding:4rem 2rem;
            background:rgba(255,255,255,.03); border:1px dashed rgba(255,255,255,.1);
            border-radius:16px;
        ">
            <i class="fas fa-certificate" style="font-size:4rem; color:#374151; margin-bottom:1rem; display:block;"></i>
            <p style="color:#6b7280; font-size:1.1rem;">Aucun certificat obtenu pour le moment.</p>
            <p style="color:#4b5563; font-size:.95rem; margin-top:.5rem;">
                Complétez 100% des leçons d'un cours pour obtenir votre certificat.
            </p>
            <a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php"
               class="btn-primary" style="margin-top:2rem; display:inline-flex;">
                <i class="fas fa-book-open"></i> Explorer les cours
            </a>
        </div>

    <?php else: ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:2rem;">
            <?php foreach ($certificates as $cert): ?>
                <div class="glass-card" style="padding:2rem; position:relative; overflow:hidden;">

                    <!-- Décoration fond -->
                    <div style="
                        position:absolute; top:-20px; right:-20px;
                        width:120px; height:120px; border-radius:50%;
                        background:radial-gradient(circle,rgba(16,185,129,.15),transparent);
                        pointer-events:none;
                    "></div>

                    <!-- En-tête -->
                    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
                        <div style="
                            width:56px; height:56px; border-radius:50%;
                            background:linear-gradient(135deg,#10b981,#059669);
                            display:flex; align-items:center; justify-content:center;
                            flex-shrink:0;
                        ">
                            <i class="fas fa-certificate" style="color:#fff; font-size:1.4rem;"></i>
                        </div>
                        <div>
                            <p style="color:#10b981; font-size:.8rem; font-weight:700;
                                      text-transform:uppercase; letter-spacing:1px; margin:0;">
                                Certificat de réussite
                            </p>
                            <h3 style="margin:.2rem 0 0; font-size:1.15rem; color:#f4f4f5;">
                                <?= htmlspecialchars($cert['course_titre']) ?>
                            </h3>
                        </div>
                    </div>

                    <!-- Détails -->
                    <div style="
                        background:rgba(0,0,0,.25); border-radius:10px;
                        padding:1rem; margin-bottom:1.5rem;
                    ">
                        <div style="display:flex; justify-content:space-between; margin-bottom:.5rem;">
                            <span style="color:#6b7280; font-size:.85rem;">Utilisateur</span>
                            <span style="color:#d1d5db; font-size:.85rem; font-weight:600;">
                                Utilisateur #<?= (int)$cert['user_id'] ?>
                            </span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:.5rem;">
                            <span style="color:#6b7280; font-size:.85rem;">Date d'obtention</span>
                            <span style="color:#d1d5db; font-size:.85rem; font-weight:600;">
                                <?= htmlspecialchars(date('d/m/Y', strtotime($cert['date_obtained']))) ?>
                            </span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:#6b7280; font-size:.85rem;">Progression</span>
                            <span style="color:#4ade80; font-size:.85rem; font-weight:700;">100% ✓</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display:flex; gap:.8rem; flex-wrap:wrap;">
                        <a href="<?= $baseUrl ?>/View/FrontOffice/certificate/view.php?id=<?= (int)$cert['id'] ?>"
                           class="btn-outline" style="flex:1; justify-content:center; padding:.7rem 1rem; font-size:.9rem;">
                            <i class="fas fa-eye"></i> Voir
                        </a>
                        <a href="<?= $baseUrl ?>/View/FrontOffice/course/show.php?id=<?= (int)$cert['course_id'] ?>"
                           style="flex:1; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
                                  color:#d1d5db; padding:.7rem 1rem; border-radius:50px; text-decoration:none;
                                  font-size:.9rem; font-weight:600; display:flex; align-items:center;
                                  justify-content:center; gap:.5rem;">
                            <i class="fas fa-book"></i> Cours
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
