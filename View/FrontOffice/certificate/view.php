<?php
/**
 * View/FrontOffice/certificate/view.php
 * Affiche et permet d'imprimer un certificat individuel.
 */
require_once __DIR__ . '/../../../Controller/CertificateController.php';

$baseUrl       = '/gestioncours';
$currentUserId = 1; // Remplacer par session réelle

$certId         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$certController = new CertificateController();

// Récupère le certificat et vérifie qu'il appartient à l'utilisateur courant
$cert = $certController->getById($certId, $currentUserId);

if ($cert === null) {
    http_response_code(404);
    $pageTitle = 'Certificat introuvable';
    require_once __DIR__ . '/../../includes/header.php';
    echo '<section><p style="color:#ef4444;">Certificat introuvable ou accès non autorisé.</p>
          <p><a href="' . $baseUrl . '/View/FrontOffice/certificate/index.php">← Retour à mes certificats</a></p></section>';
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$pageTitle = 'Certificat — ' . $cert['course_titre'];
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Styles spécifiques à l'impression -->
<style>
@media print {
    header, footer, .no-print { display: none !important; }
    body { background: #fff !important; color: #000 !important; }
    .certificate-wrapper { box-shadow: none !important; border: 3px solid #10b981 !important; }
    .page-shell { padding-top: 0 !important; width: 100% !important; }
}
</style>

<section>
    <!-- Boutons actions (masqués à l'impression) -->
    <div class="no-print" style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
        <a href="<?= $baseUrl ?>/View/FrontOffice/certificate/index.php"
           style="color:#a78bfa; text-decoration:none; font-weight:600;">
            ← Retour à mes certificats
        </a>
        <button onclick="window.print()" class="btn-primary" style="margin-left:auto;">
            <i class="fas fa-print"></i> Imprimer / Télécharger PDF
        </button>
    </div>

    <!-- Certificat -->
    <div class="certificate-wrapper" style="
        max-width: 860px; margin: 0 auto;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        border: 2px solid #10b981;
        border-radius: 20px;
        padding: 4rem 5rem;
        text-align: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0,0,0,.6), 0 0 0 1px rgba(16,185,129,.2) inset;
    ">
        <!-- Décoration coins -->
        <div style="position:absolute; top:20px; left:20px; width:60px; height:60px;
                    border-top:3px solid #10b981; border-left:3px solid #10b981; border-radius:4px 0 0 0;"></div>
        <div style="position:absolute; top:20px; right:20px; width:60px; height:60px;
                    border-top:3px solid #10b981; border-right:3px solid #10b981; border-radius:0 4px 0 0;"></div>
        <div style="position:absolute; bottom:20px; left:20px; width:60px; height:60px;
                    border-bottom:3px solid #10b981; border-left:3px solid #10b981; border-radius:0 0 0 4px;"></div>
        <div style="position:absolute; bottom:20px; right:20px; width:60px; height:60px;
                    border-bottom:3px solid #10b981; border-right:3px solid #10b981; border-radius:0 0 4px 0;"></div>

        <!-- Fond décoratif -->
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
                    width:400px; height:400px; border-radius:50%;
                    background:radial-gradient(circle,rgba(16,185,129,.06),transparent 70%);
                    pointer-events:none;"></div>

        <!-- Logo plateforme -->
        <div style="font-size:2rem; font-weight:800; color:#f4f4f5; letter-spacing:-1px; margin-bottom:.5rem;">
            e-lite<span style="color:#eab308;">.</span>
        </div>
        <p style="color:#6b7280; font-size:.85rem; text-transform:uppercase; letter-spacing:3px; margin-bottom:3rem;">
            Plateforme e-learning
        </p>

        <!-- Icône certificat -->
        <div style="
            width:90px; height:90px; border-radius:50%; margin:0 auto 2rem;
            background:linear-gradient(135deg,#10b981,#059669);
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 8px 25px rgba(16,185,129,.4);
        ">
            <i class="fas fa-certificate" style="color:#fff; font-size:2.5rem;"></i>
        </div>

        <!-- Titre -->
        <p style="color:#10b981; font-size:.9rem; text-transform:uppercase; letter-spacing:4px;
                  font-weight:700; margin-bottom:1rem;">
            Certificat de Réussite
        </p>

        <p style="color:#9ca3af; font-size:1rem; margin-bottom:.8rem;">
            Ce certificat est décerné à
        </p>

        <!-- Nom utilisateur -->
        <h2 style="font-size:2.8rem; color:#f4f4f5; margin-bottom:1rem;
                   text-shadow:0 2px 10px rgba(255,255,255,.1);">
            Utilisateur #<?= (int)$cert['user_id'] ?>
        </h2>

        <p style="color:#9ca3af; font-size:1rem; margin-bottom:.8rem;">
            pour avoir complété avec succès le cours
        </p>

        <!-- Titre du cours -->
        <h3 style="font-size:1.8rem; color:#eab308; margin-bottom:2.5rem;
                   text-shadow:0 2px 10px rgba(234,179,8,.3);">
            <?= htmlspecialchars($cert['course_titre']) ?>
        </h3>

        <!-- Séparateur -->
        <div style="width:200px; height:2px; background:linear-gradient(90deg,transparent,#10b981,transparent);
                    margin:0 auto 2.5rem;"></div>

        <!-- Date et ID -->
        <div style="display:flex; justify-content:center; gap:4rem; flex-wrap:wrap;">
            <div>
                <p style="color:#6b7280; font-size:.8rem; text-transform:uppercase; letter-spacing:2px; margin-bottom:.3rem;">
                    Date d'obtention
                </p>
                <p style="color:#d1d5db; font-size:1.1rem; font-weight:700;">
                    <?= htmlspecialchars(date('d/m/Y', strtotime($cert['date_obtained']))) ?>
                </p>
            </div>
            <div>
                <p style="color:#6b7280; font-size:.8rem; text-transform:uppercase; letter-spacing:2px; margin-bottom:.3rem;">
                    Progression
                </p>
                <p style="color:#4ade80; font-size:1.1rem; font-weight:700;">100% ✓</p>
            </div>
            <div>
                <p style="color:#6b7280; font-size:.8rem; text-transform:uppercase; letter-spacing:2px; margin-bottom:.3rem;">
                    Référence
                </p>
                <p style="color:#d1d5db; font-size:1.1rem; font-weight:700;">
                    CERT-<?= str_pad((string)$cert['id'], 6, '0', STR_PAD_LEFT) ?>
                </p>
            </div>
        </div>

        <!-- Signature -->
        <div style="margin-top:3rem; padding-top:2rem;
                    border-top:1px solid rgba(255,255,255,.08);">
            <p style="color:#4b5563; font-size:.85rem;">
                Délivré par la plateforme <strong style="color:#10b981;">e-lite</strong>
                — Formation en ligne de qualité
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
