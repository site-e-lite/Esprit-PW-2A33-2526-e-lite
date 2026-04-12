<?php
require_once __DIR__ . '/../../Controller/VirtualClassController.php';

$controller = new VirtualClassController();

// ─── HANDLE POST ACTIONS ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    switch ($_POST['action']) {
        // Courses
        case 'add_course':    $controller->addCourse($_POST); break;
        case 'edit_course':   $controller->editCourse((int)$_POST['idCourse'], $_POST); break;
        case 'delete_course': $controller->deleteCourse((int)$_POST['idCourse']); break;
        // Classes
        case 'add_class':    $controller->addClass($_POST); break;
        case 'edit_class':   $controller->editClass((int)$_POST['idClass'], $_POST); break;
        case 'delete_class': $controller->deleteClass((int)$_POST['idClass']); break;
        // Sessions
        case 'add_session':    $controller->addSession($_POST); break;
        case 'edit_session':   $controller->editSession((int)$_POST['idSession'], $_POST); break;
        case 'delete_session': $controller->deleteSession((int)$_POST['idSession']); break;
    }
}

// ─── FETCH DATA ─────────────────────────────────────────────────────────────
$courses  = $controller->getAllCourses();
$classes  = $controller->getAllClasses();
$sessions = $controller->getAllSessions();

$error   = isset($_GET['error'])   ? htmlspecialchars(urldecode($_GET['error']))   : null;
$success = isset($_GET['success']) ? htmlspecialchars(urldecode($_GET['success'])) : null;
$activeTab = $_GET['tab'] ?? 'courses';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | Classes Virtuelles — BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; min-height: 100vh; overflow: hidden; background: var(--dark-bg); }

        /* ── Sidebar ── */
        .sidebar {
            width: 280px; background: rgba(5,5,5,0.8); border-right: 1px solid var(--glass-border);
            padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem; height: 100vh; flex-shrink: 0;
        }
        .sidebar .nav-links { display: flex; flex-direction: column; gap: 0.8rem; }
        .sidebar .nav-links a {
            display: flex; align-items: center; gap: 1rem; padding: 0.9rem 1rem; border-radius: 12px;
            color: var(--light-gray); text-decoration: none; font-weight: 500; transition: all 0.3s;
        }
        .sidebar .nav-links a:hover, .sidebar .nav-links a.active {
            background: rgba(234,179,8,0.1); color: var(--accent);
        }
        .sidebar .nav-links a::after { display: none; }

        /* ── Main ── */
        .main-content {
            flex: 1; padding: 2.5rem; overflow-y: auto; height: 100vh;
            background: radial-gradient(circle at top right, rgba(234,179,8,0.05), transparent 40%);
        }

        /* ── Tabs ── */
        .tab-bar { display: flex; gap: 0.5rem; margin-bottom: 2.5rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 0; }
        .tab-btn {
            padding: 0.8rem 1.8rem; border: none; background: transparent;
            color: var(--light-gray); font-family: var(--font-main); font-size: 0.95rem;
            font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent;
            margin-bottom: -1px; transition: all 0.25s; border-radius: 8px 8px 0 0;
        }
        .tab-btn:hover { color: var(--text-main); }
        .tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); background: rgba(234,179,8,0.05); }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ── Table ── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { padding: 1rem 1.2rem; color: var(--light-gray); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); text-align: left; }
        .data-table td { padding: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.02); vertical-align: middle; }
        .data-table tr { transition: background 0.2s; }
        .data-table tr:hover td { background: rgba(255,255,255,0.02); }

        .badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .badge-green { background: rgba(16,185,129,0.1); color: var(--green-eco); }
        .badge-yellow { background: rgba(234,179,8,0.1); color: var(--accent); }
        .badge-red { background: rgba(239,68,68,0.1); color: #ef4444; }
        .badge-blue { background: rgba(59,130,246,0.1); color: #3b82f6; }

        /* ── Table action btns ── */
        .action-btn {
            width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--glass-border);
            display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
            background: transparent; color: var(--light-gray); font-size: 0.85rem; transition: all 0.2s;
        }
        .action-btn:hover { border-color: var(--accent); color: var(--accent); }
        .action-btn.del:hover { border-color: #ef4444; color: #ef4444; }

        /* ── Alert banners ── */
        .alert { padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; font-weight: 500; }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: var(--green-eco); }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; }

        /* ── Modal overrides ── */
        .modal { max-width: 720px; }

        /* ── JS Validation Errors ── */
        .field-error {
            display: none; color: #ef4444; font-size: 0.8rem; margin-top: 0.35rem;
            align-items: center; gap: 0.4rem;
        }
        .field-error.visible { display: flex; }
        .input-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.15) !important;
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════
     MODALS — OVERLAY
════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalOverlay"></div>

<!-- ─── COURSE MODALS ─────────────────────────────────── -->
<div class="modal" id="modalAddCourse">
    <div class="modal-header">
        <h3><i class="fas fa-book-open"></i> Ajouter un Cours</h3>
        <button class="close-btn" onclick="closeModal('modalAddCourse')">&times;</button>
    </div>
    <div class="modal-body">
        <form id="formAddCourse" action="virtualclass.php" method="POST" class="glass-form" novalidate>
            <input type="hidden" name="action" value="add_course">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Titre *</label>
                    <input type="text" id="addCourseTitre" name="titre" placeholder="Titre du cours">
                    <span class="field-error" id="err-addCourseTitre"><i class="fas fa-exclamation-circle"></i> Le titre est obligatoire.</span>
                </div>
                <div class="form-group full-width"><label>Description</label><textarea name="description" placeholder="Description du cours"></textarea></div>
                <div class="form-group"><label>Niveau</label>
                    <select name="niveau">
                        <option value="Débutant">Débutant</option>
                        <option value="Intermédiaire">Intermédiaire</option>
                        <option value="Avancé">Avancé</option>
                    </select>
                </div>
                <div class="form-group"><label>Langue</label><input type="text" name="langue" value="Français"></div>
                <div class="form-group">
                    <label>Durée (heures)</label>
                    <input type="text" id="addCourseDuree" name="duree" value="1" placeholder="ex: 10">
                    <span class="field-error" id="err-addCourseDuree"><i class="fas fa-exclamation-circle"></i> Entrez un nombre entier positif.</span>
                </div>
                <div class="form-group">
                    <label>Prix (€)</label>
                    <input type="text" id="addCoursePrix" name="prix" value="0" placeholder="ex: 29.99">
                    <span class="field-error" id="err-addCoursePrix"><i class="fas fa-exclamation-circle"></i> Entrez un prix valide (≥ 0).</span>
                </div>
                <div class="form-group"><label>Statut</label>
                    <select name="statut">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                        <option value="brouillon">Brouillon</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image (URL)</label>
                    <input type="text" id="addCourseImage" name="image" placeholder="https://...">
                    <span class="field-error" id="err-addCourseImage"><i class="fas fa-exclamation-circle"></i> URL invalide (doit commencer par http:// ou https://).</span>
                </div>
            </div>
            <button type="button" class="btn-primary full-width mt-3" onclick="validateAddCourse()"><i class="fas fa-plus"></i> Ajouter le cours</button>
        </form>
    </div>
</div>

<div class="modal" id="modalEditCourse">
    <div class="modal-header">
        <h3><i class="fas fa-edit"></i> Modifier le Cours</h3>
        <button class="close-btn" onclick="closeModal('modalEditCourse')">&times;</button>
    </div>
    <div class="modal-body">
        <form id="formEditCourse" action="virtualclass.php" method="POST" class="glass-form" novalidate>
            <input type="hidden" name="action" value="edit_course">
            <input type="hidden" name="idCourse" id="editCourseId">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Titre *</label>
                    <input type="text" name="titre" id="editCourseTitre">
                    <span class="field-error" id="err-editCourseTitre"><i class="fas fa-exclamation-circle"></i> Le titre est obligatoire.</span>
                </div>
                <div class="form-group full-width"><label>Description</label><textarea name="description" id="editCourseDesc"></textarea></div>
                <div class="form-group"><label>Niveau</label>
                    <select name="niveau" id="editCourseNiveau">
                        <option value="Débutant">Débutant</option>
                        <option value="Intermédiaire">Intermédiaire</option>
                        <option value="Avancé">Avancé</option>
                    </select>
                </div>
                <div class="form-group"><label>Langue</label><input type="text" name="langue" id="editCourseLangue"></div>
                <div class="form-group">
                    <label>Durée (heures)</label>
                    <input type="text" name="duree" id="editCourseDuree">
                    <span class="field-error" id="err-editCourseDuree"><i class="fas fa-exclamation-circle"></i> Entrez un nombre entier positif.</span>
                </div>
                <div class="form-group">
                    <label>Prix (€)</label>
                    <input type="text" name="prix" id="editCoursePrix">
                    <span class="field-error" id="err-editCoursePrix"><i class="fas fa-exclamation-circle"></i> Entrez un prix valide (≥ 0).</span>
                </div>
                <div class="form-group"><label>Statut</label>
                    <select name="statut" id="editCourseStatut">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                        <option value="brouillon">Brouillon</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image (URL)</label>
                    <input type="text" name="image" id="editCourseImage">
                    <span class="field-error" id="err-editCourseImage"><i class="fas fa-exclamation-circle"></i> URL invalide.</span>
                </div>
            </div>
            <button type="button" class="btn-primary full-width mt-3" onclick="validateEditCourse()"><i class="fas fa-save"></i> Enregistrer</button>
        </form>
    </div>
</div>

<!-- ─── VIRTUALCLASS MODALS ───────────────────────────── -->
<div class="modal" id="modalAddClass">
    <div class="modal-header">
        <h3><i class="fas fa-video"></i> Ajouter une Classe Virtuelle</h3>
        <button class="close-btn" onclick="closeModal('modalAddClass')">&times;</button>
    </div>
    <div class="modal-body">
        <form id="formAddClass" action="virtualclass.php" method="POST" class="glass-form" novalidate>
            <input type="hidden" name="action" value="add_class">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Titre *</label>
                    <input type="text" id="addClassTitre" name="titre" placeholder="Titre de la classe">
                    <span class="field-error" id="err-addClassTitre"><i class="fas fa-exclamation-circle"></i> Le titre est obligatoire.</span>
                </div>
                <div class="form-group full-width"><label>Description</label><textarea name="description" placeholder="Description..."></textarea></div>
                <div class="form-group full-width">
                    <label>Lien d'accès *</label>
                    <input type="text" id="addClassLien" name="lienAcces" placeholder="https://zoom.us/...">
                    <span class="field-error" id="err-addClassLien"><i class="fas fa-exclamation-circle"></i> Un lien valide est requis (http:// ou https://).</span>
                </div>
                <div class="form-group"><label>Plateforme</label>
                    <select name="plateforme">
                        <option value="Zoom">Zoom</option>
                        <option value="Google Meet">Google Meet</option>
                        <option value="Microsoft Teams">Microsoft Teams</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cours Associé *</label>
                    <select id="addClassCourse" name="idCourse">
                        <option value="">-- Sélectionner un cours --</option>
                        <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['idCourse'] ?>"><?= htmlspecialchars($c['titre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="err-addClassCourse"><i class="fas fa-exclamation-circle"></i> Veuillez sélectionner un cours.</span>
                </div>
            </div>
            <button type="button" class="btn-primary full-width mt-3" onclick="validateAddClass()"><i class="fas fa-plus"></i> Ajouter la classe</button>
        </form>
    </div>
</div>

<div class="modal" id="modalEditClass">
    <div class="modal-header">
        <h3><i class="fas fa-edit"></i> Modifier la Classe Virtuelle</h3>
        <button class="close-btn" onclick="closeModal('modalEditClass')">&times;</button>
    </div>
    <div class="modal-body">
        <form id="formEditClass" action="virtualclass.php" method="POST" class="glass-form" novalidate>
            <input type="hidden" name="action" value="edit_class">
            <input type="hidden" name="idClass" id="editClassId">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Titre *</label>
                    <input type="text" name="titre" id="editClassTitre">
                    <span class="field-error" id="err-editClassTitre"><i class="fas fa-exclamation-circle"></i> Le titre est obligatoire.</span>
                </div>
                <div class="form-group full-width"><label>Description</label><textarea name="description" id="editClassDesc"></textarea></div>
                <div class="form-group full-width">
                    <label>Lien d'accès *</label>
                    <input type="text" name="lienAcces" id="editClassLien">
                    <span class="field-error" id="err-editClassLien"><i class="fas fa-exclamation-circle"></i> Un lien valide est requis.</span>
                </div>
                <div class="form-group"><label>Plateforme</label>
                    <select name="plateforme" id="editClassPlateforme">
                        <option value="Zoom">Zoom</option>
                        <option value="Google Meet">Google Meet</option>
                        <option value="Microsoft Teams">Microsoft Teams</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cours Associé *</label>
                    <select name="idCourse" id="editClassCourse">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['idCourse'] ?>"><?= htmlspecialchars($c['titre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="err-editClassCourse"><i class="fas fa-exclamation-circle"></i> Veuillez sélectionner un cours.</span>
                </div>
            </div>
            <button type="button" class="btn-primary full-width mt-3" onclick="validateEditClass()"><i class="fas fa-save"></i> Enregistrer</button>
        </form>
    </div>
</div>

<!-- ─── SESSION MODALS ────────────────────────────────── -->
<div class="modal" id="modalAddSession">
    <div class="modal-header">
        <h3><i class="fas fa-calendar-plus"></i> Ajouter une Session</h3>
        <button class="close-btn" onclick="closeModal('modalAddSession')">&times;</button>
    </div>
    <div class="modal-body">
        <form id="formAddSession" action="virtualclass.php" method="POST" class="glass-form" novalidate>
            <input type="hidden" name="action" value="add_session">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Classe Virtuelle *</label>
                    <select id="addSessionClass" name="idClass">
                        <option value="">-- Sélectionner une classe --</option>
                        <?php foreach ($classes as $cl): ?>
                        <option value="<?= $cl['idClass'] ?>"><?= htmlspecialchars($cl['titre']) ?> (<?= htmlspecialchars($cl['courseTitre'] ?? '') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="err-addSessionClass"><i class="fas fa-exclamation-circle"></i> Sélectionnez une classe virtuelle.</span>
                </div>
                <div class="form-group">
                    <label>Date *</label>
                    <input type="text" id="addSessionDate" name="dateSession" placeholder="AAAA-MM-JJ">
                    <span class="field-error" id="err-addSessionDate"><i class="fas fa-exclamation-circle"></i> Format requis: AAAA-MM-JJ (ex: 2026-05-20).</span>
                </div>
                <div class="form-group"><label>Statut</label>
                    <select name="statut">
                        <option value="planifiée">Planifiée</option>
                        <option value="en cours">En cours</option>
                        <option value="terminée">Terminée</option>
                        <option value="annulée">Annulée</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Heure Début *</label>
                    <input type="text" id="addSessionDebut" name="heureDebut" placeholder="HH:MM">
                    <span class="field-error" id="err-addSessionDebut"><i class="fas fa-exclamation-circle"></i> Format requis: HH:MM (ex: 09:30).</span>
                </div>
                <div class="form-group">
                    <label>Heure Fin *</label>
                    <input type="text" id="addSessionFin" name="heureFin" placeholder="HH:MM">
                    <span class="field-error" id="err-addSessionFin"><i class="fas fa-exclamation-circle"></i> Format requis: HH:MM et doit être après l'heure de début.</span>
                </div>
                <div class="form-group">
                    <label>Capacité (places) *</label>
                    <input type="text" id="addSessionCapacite" name="capacite" value="30" placeholder="ex: 30">
                    <span class="field-error" id="err-addSessionCapacite"><i class="fas fa-exclamation-circle"></i> Entrez un nombre entier ≥ 0.</span>
                </div>
            </div>
            <button type="button" class="btn-primary full-width mt-3" onclick="validateAddSession()"><i class="fas fa-plus"></i> Ajouter la session</button>
        </form>
    </div>
</div>

<div class="modal" id="modalEditSession">
    <div class="modal-header">
        <h3><i class="fas fa-edit"></i> Modifier la Session</h3>
        <button class="close-btn" onclick="closeModal('modalEditSession')">&times;</button>
    </div>
    <div class="modal-body">
        <form id="formEditSession" action="virtualclass.php" method="POST" class="glass-form" novalidate>
            <input type="hidden" name="action" value="edit_session">
            <input type="hidden" name="idSession" id="editSessionId">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Classe Virtuelle *</label>
                    <select name="idClass" id="editSessionClass">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($classes as $cl): ?>
                        <option value="<?= $cl['idClass'] ?>"><?= htmlspecialchars($cl['titre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="err-editSessionClass"><i class="fas fa-exclamation-circle"></i> Sélectionnez une classe virtuelle.</span>
                </div>
                <div class="form-group">
                    <label>Date *</label>
                    <input type="text" name="dateSession" id="editSessionDate" placeholder="AAAA-MM-JJ">
                    <span class="field-error" id="err-editSessionDate"><i class="fas fa-exclamation-circle"></i> Format requis: AAAA-MM-JJ.</span>
                </div>
                <div class="form-group"><label>Statut</label>
                    <select name="statut" id="editSessionStatut">
                        <option value="planifiée">Planifiée</option>
                        <option value="en cours">En cours</option>
                        <option value="terminée">Terminée</option>
                        <option value="annulée">Annulée</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Heure Début *</label>
                    <input type="text" name="heureDebut" id="editSessionDebut" placeholder="HH:MM">
                    <span class="field-error" id="err-editSessionDebut"><i class="fas fa-exclamation-circle"></i> Format requis: HH:MM.</span>
                </div>
                <div class="form-group">
                    <label>Heure Fin *</label>
                    <input type="text" name="heureFin" id="editSessionFin" placeholder="HH:MM">
                    <span class="field-error" id="err-editSessionFin"><i class="fas fa-exclamation-circle"></i> Format requis: HH:MM et doit être après l'heure de début.</span>
                </div>
                <div class="form-group">
                    <label>Capacité (places) *</label>
                    <input type="text" name="capacite" id="editSessionCapacite" placeholder="ex: 30">
                    <span class="field-error" id="err-editSessionCapacite"><i class="fas fa-exclamation-circle"></i> Entrez un nombre entier ≥ 0.</span>
                </div>
            </div>
            <button type="button" class="btn-primary full-width mt-3" onclick="validateEditSession()"><i class="fas fa-save"></i> Enregistrer</button>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     LAYOUT
════════════════════════════════════════════════════════ -->
<aside class="sidebar">
    <a href="#" class="logo">e-lite<span>.</span></a>
    <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--light-gray);">BackOffice Admin</div>
    <nav class="nav-links">
        <a href="dashboard.php"><i class="fas fa-chart-line"></i> Vue d'ensemble</a>
        <a href="dashboard.php#forums"><i class="fas fa-comments"></i> Forum (Statique)</a>
        <a href="#"><i class="fas fa-users"></i> Utilisateurs</a>
        <a href="#"><i class="fas fa-book-open"></i> Cours & IA</a>
        <a href="#"><i class="fas fa-tasks"></i> Évaluations</a>
        <a href="virtualclass.php" class="active"><i class="fas fa-video"></i> Classes Virtuelles</a>
        <a href="../FrontOffice/index.php" style="margin-top: auto; border-top: 1px solid var(--glass-border); border-radius: 0; padding-top: 1.5rem;">
            <i class="fas fa-globe"></i> Retour au Site
        </a>
    </nav>
</aside>

<main class="main-content">

    <!-- Page header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--glass-border);">
        <div>
            <h1 style="font-size: 1.9rem; margin: 0;"><i class="fas fa-video" style="color: var(--accent); margin-right: 0.8rem;"></i>Gestion — Classes Virtuelles</h1>
            <p style="color: var(--light-gray); margin-top: 0.5rem;">Gérez vos cours, classes et sessions depuis un espace centralisé.</p>
        </div>
        <a href="dashboard.php" class="btn-outline" style="padding: 0.6rem 1.4rem; font-size: 0.9rem;"><i class="fas fa-arrow-left"></i> Dashboard</a>
    </div>

    <!-- Alerts -->
    <?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <!-- ─── TAB BAR ─────────────────────────────────────── -->
    <div class="tab-bar">
        <button class="tab-btn <?= $activeTab === 'courses'  ? 'active' : '' ?>" onclick="switchTab('courses')"><i class="fas fa-book-open" style="margin-right:0.5rem;"></i>Cours (<?= count($courses) ?>)</button>
        <button class="tab-btn <?= $activeTab === 'classes'  ? 'active' : '' ?>" onclick="switchTab('classes')"><i class="fas fa-video" style="margin-right:0.5rem;"></i>Classes Virtuelles (<?= count($classes) ?>)</button>
        <button class="tab-btn <?= $activeTab === 'sessions' ? 'active' : '' ?>" onclick="switchTab('sessions')"><i class="fas fa-calendar-alt" style="margin-right:0.5rem;"></i>Sessions (<?= count($sessions) ?>)</button>
    </div>

    <!-- ═══ TAB: COURS ══════════════════════════════════════ -->
    <div id="tab-courses" class="tab-panel <?= $activeTab === 'courses' ? 'active' : '' ?>">
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem;">
                <h3 style="margin:0;"><i class="fas fa-book-open" style="color:var(--accent); margin-right:0.7rem;"></i>Liste des Cours</h3>
                <button class="btn-primary" style="padding:0.55rem 1.3rem; font-size:0.9rem;" onclick="openModal('modalAddCourse')"><i class="fas fa-plus"></i> Nouveau Cours</button>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Niveau</th>
                        <th>Durée</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($courses)): ?>
                <tr><td colspan="7" style="text-align:center; padding:3rem; color:var(--light-gray);"><i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:1rem; opacity:0.4;"></i>Aucun cours enregistré.</td></tr>
                <?php else: foreach ($courses as $c): ?>
                <tr>
                    <td style="color:var(--light-gray); font-size:0.85rem;">#<?= $c['idCourse'] ?></td>
                    <td><strong style="color:var(--text-main);"><?= htmlspecialchars($c['titre']) ?></strong>
                        <?php if ($c['description']): ?>
                        <div style="font-size:0.8rem; color:var(--light-gray); margin-top:0.2rem;"><?= htmlspecialchars(mb_substr($c['description'], 0, 60)) ?>...</div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($c['niveau'] ?? '—') ?></td>
                    <td><?= $c['duree'] ? $c['duree'] . ' h' : '—' ?></td>
                    <td><?= $c['prix'] ? number_format($c['prix'], 2) . ' €' : 'Gratuit' ?></td>
                    <td>
                        <?php
                        $statutClass = match($c['statut'] ?? '') {
                            'actif'     => 'badge-green',
                            'inactif'   => 'badge-red',
                            'brouillon' => 'badge-yellow',
                            default     => 'badge-blue',
                        };
                        ?>
                        <span class="badge <?= $statutClass ?>"><?= htmlspecialchars($c['statut'] ?? 'N/A') ?></span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                            <button class="action-btn"
                                onclick="openEditCourse(<?= $c['idCourse'] ?>, <?= htmlspecialchars(json_encode($c['titre']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($c['description'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($c['niveau'] ?? 'Débutant'), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($c['langue'] ?? 'Français'), ENT_QUOTES) ?>, <?= (int)($c['duree'] ?? 1) ?>, <?= (float)($c['prix'] ?? 0) ?>, <?= htmlspecialchars(json_encode($c['statut'] ?? 'actif'), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($c['image'] ?? ''), ENT_QUOTES) ?>)"
                                title="Modifier"><i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="virtualclass.php" onsubmit="return confirm('Supprimer ce cours ?');" style="display:inline;">
                                <input type="hidden" name="action" value="delete_course">
                                <input type="hidden" name="idCourse" value="<?= $c['idCourse'] ?>">
                                <button type="submit" class="action-btn del" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═══ TAB: CLASSES VIRTUELLES ════════════════════════ -->
    <div id="tab-classes" class="tab-panel <?= $activeTab === 'classes' ? 'active' : '' ?>">
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem;">
                <h3 style="margin:0;"><i class="fas fa-video" style="color:var(--accent); margin-right:0.7rem;"></i>Liste des Classes Virtuelles</h3>
                <button class="btn-primary" style="padding:0.55rem 1.3rem; font-size:0.9rem;" onclick="openModal('modalAddClass')"><i class="fas fa-plus"></i> Nouvelle Classe</button>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Cours Associé</th>
                        <th>Plateforme</th>
                        <th>Lien d'accès</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($classes)): ?>
                <tr><td colspan="6" style="text-align:center; padding:3rem; color:var(--light-gray);"><i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:1rem; opacity:0.4;"></i>Aucune classe virtuelle enregistrée.</td></tr>
                <?php else: foreach ($classes as $cl): ?>
                <tr>
                    <td style="color:var(--light-gray); font-size:0.85rem;">#<?= $cl['idClass'] ?></td>
                    <td><strong style="color:var(--text-main);"><?= htmlspecialchars($cl['titre']) ?></strong>
                        <?php if ($cl['description']): ?>
                        <div style="font-size:0.8rem; color:var(--light-gray); margin-top:0.2rem;"><?= htmlspecialchars(mb_substr($cl['description'], 0, 60)) ?>...</div>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-yellow"><?= htmlspecialchars($cl['courseTitre'] ?? '—') ?></span></td>
                    <td>
                        <?php
                        $platformIcon = match($cl['plateforme'] ?? '') {
                            'Zoom'            => 'fab fa-zoom',
                            'Google Meet'     => 'fab fa-google',
                            'Microsoft Teams' => 'fab fa-microsoft',
                            default           => 'fas fa-link',
                        };
                        ?>
                        <i class="<?= $platformIcon ?>" style="margin-right:0.4rem;"></i><?= htmlspecialchars($cl['plateforme'] ?? '—') ?>
                    </td>
                    <td><a href="<?= htmlspecialchars($cl['lienAcces']) ?>" target="_blank" style="color:var(--accent); font-size:0.85rem; text-decoration:none;"><i class="fas fa-external-link-alt"></i> Rejoindre</a></td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                            <button class="action-btn"
                                onclick="openEditClass(<?= $cl['idClass'] ?>, <?= htmlspecialchars(json_encode($cl['titre']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($cl['description'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($cl['lienAcces']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($cl['plateforme'] ?? 'Zoom'), ENT_QUOTES) ?>, <?= (int)$cl['idCourse'] ?>)"
                                title="Modifier"><i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="virtualclass.php" onsubmit="return confirm('Supprimer cette classe ? Les sessions associées seront également supprimées.');" style="display:inline;">
                                <input type="hidden" name="action" value="delete_class">
                                <input type="hidden" name="idClass" value="<?= $cl['idClass'] ?>">
                                <button type="submit" class="action-btn del" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═══ TAB: SESSIONS ══════════════════════════════════ -->
    <div id="tab-sessions" class="tab-panel <?= $activeTab === 'sessions' ? 'active' : '' ?>">
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem;">
                <h3 style="margin:0;"><i class="fas fa-calendar-alt" style="color:var(--accent); margin-right:0.7rem;"></i>Liste des Sessions</h3>
                <button class="btn-primary" style="padding:0.55rem 1.3rem; font-size:0.9rem;" onclick="openModal('modalAddSession')"><i class="fas fa-plus"></i> Nouvelle Session</button>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Classe Virtuelle</th>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Capacité</th>
                        <th>Statut</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($sessions)): ?>
                <tr><td colspan="7" style="text-align:center; padding:3rem; color:var(--light-gray);"><i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:1rem; opacity:0.4;"></i>Aucune session enregistrée.</td></tr>
                <?php else: foreach ($sessions as $s): ?>
                <tr>
                    <td style="color:var(--light-gray); font-size:0.85rem;">#<?= $s['idSession'] ?></td>
                    <td>
                        <strong style="color:var(--text-main);"><?= htmlspecialchars($s['classTitre'] ?? '—') ?></strong>
                        <?php if (!empty($s['courseTitre'])): ?>
                        <div style="font-size:0.78rem; color:var(--light-gray); margin-top:0.2rem;"><?= htmlspecialchars($s['courseTitre']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><i class="far fa-calendar" style="color:var(--accent); margin-right:0.4rem;"></i><?= htmlspecialchars($s['dateSession']) ?></td>
                    <td><i class="far fa-clock" style="color:var(--light-gray); margin-right:0.4rem;"></i><?= htmlspecialchars($s['heureDebut']) ?> → <?= htmlspecialchars($s['heureFin']) ?></td>
                    <td><i class="fas fa-users" style="color:var(--light-gray); margin-right:0.4rem;"></i><?= (int)$s['capacite'] ?> places</td>
                    <td>
                        <?php
                        $sClass = match($s['statut'] ?? '') {
                            'planifiée' => 'badge-blue',
                            'en cours'  => 'badge-yellow',
                            'terminée'  => 'badge-green',
                            'annulée'   => 'badge-red',
                            default     => 'badge-blue',
                        };
                        ?>
                        <span class="badge <?= $sClass ?>"><?= htmlspecialchars($s['statut'] ?? 'planifiée') ?></span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                            <button class="action-btn"
                                onclick="openEditSession(<?= $s['idSession'] ?>, <?= (int)$s['idClass'] ?>, <?= htmlspecialchars(json_encode($s['dateSession']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($s['heureDebut']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($s['heureFin']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($s['statut'] ?? 'planifiée'), ENT_QUOTES) ?>, <?= (int)$s['capacite'] ?>)"
                                title="Modifier"><i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="virtualclass.php" onsubmit="return confirm('Supprimer cette session ?');" style="display:inline;">
                                <input type="hidden" name="action" value="delete_session">
                                <input type="hidden" name="idSession" value="<?= $s['idSession'] ?>">
                                <button type="submit" class="action-btn del" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="../assets/index.js"></script>
<script>
    // ════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════
    function setSelectValue(elId, val) {
        const sel = document.getElementById(elId);
        if (!sel) return;
        for (let opt of sel.options) opt.selected = (opt.value == val);
    }

    function showErr(id, show = true) {
        const el = document.getElementById('err-' + id);
        const input = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('visible', show);
        if (input) input.classList.toggle('input-invalid', show);
    }

    function clearErrors(ids) {
        ids.forEach(id => showErr(id, false));
    }

    const reDate = /^\d{4}-\d{2}-\d{2}$/;
    const reTime = /^([01]\d|2[0-3]):[0-5]\d$/;
    const reUrl  = /^https?:\/\/.+/i;
    const reNum  = /^\d+(\.\d+)?$/;
    const reInt  = /^\d+$/;

    // ════════════════════════════════════════════════════════════
    // TAB SWITCHING
    // ════════════════════════════════════════════════════════════
    function switchTab(name) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    // ════════════════════════════════════════════════════════════
    // VALIDATE — ADD COURSE
    // ════════════════════════════════════════════════════════════
    function validateAddCourse() {
        const ids = ['addCourseTitre','addCourseDuree','addCoursePrix','addCourseImage'];
        clearErrors(ids);
        let ok = true;
        const titre = document.getElementById('addCourseTitre').value.trim();
        if (!titre) { showErr('addCourseTitre'); ok = false; }
        const duree = document.getElementById('addCourseDuree').value.trim();
        if (duree && (!reInt.test(duree) || parseInt(duree) < 1)) { showErr('addCourseDuree'); ok = false; }
        const prix = document.getElementById('addCoursePrix').value.trim();
        if (prix && (!reNum.test(prix) || parseFloat(prix) < 0)) { showErr('addCoursePrix'); ok = false; }
        const image = document.getElementById('addCourseImage').value.trim();
        if (image && !reUrl.test(image)) { showErr('addCourseImage'); ok = false; }
        if (ok) document.getElementById('formAddCourse').submit();
    }

    // ════════════════════════════════════════════════════════════
    // VALIDATE — EDIT COURSE
    // ════════════════════════════════════════════════════════════
    function validateEditCourse() {
        const ids = ['editCourseTitre','editCourseDuree','editCoursePrix','editCourseImage'];
        clearErrors(ids);
        let ok = true;
        if (!document.getElementById('editCourseTitre').value.trim()) { showErr('editCourseTitre'); ok = false; }
        const duree = document.getElementById('editCourseDuree').value.trim();
        if (duree && (!reInt.test(duree) || parseInt(duree) < 1)) { showErr('editCourseDuree'); ok = false; }
        const prix = document.getElementById('editCoursePrix').value.trim();
        if (prix && (!reNum.test(prix) || parseFloat(prix) < 0)) { showErr('editCoursePrix'); ok = false; }
        const image = document.getElementById('editCourseImage').value.trim();
        if (image && !reUrl.test(image)) { showErr('editCourseImage'); ok = false; }
        if (ok) document.getElementById('formEditCourse').submit();
    }

    // ════════════════════════════════════════════════════════════
    // EDIT COURSE — open modal
    // ════════════════════════════════════════════════════════════
    function openEditCourse(id, titre, desc, niveau, langue, duree, prix, statut, image) {
        document.getElementById('editCourseId').value     = id;
        document.getElementById('editCourseTitre').value  = titre;
        document.getElementById('editCourseDesc').value   = desc;
        document.getElementById('editCourseLangue').value = langue;
        document.getElementById('editCourseDuree').value  = duree;
        document.getElementById('editCoursePrix').value   = prix;
        document.getElementById('editCourseImage').value  = image;
        setSelectValue('editCourseNiveau', niveau);
        setSelectValue('editCourseStatut', statut);
        clearErrors(['editCourseTitre','editCourseDuree','editCoursePrix','editCourseImage']);
        openModal('modalEditCourse');
    }

    // ════════════════════════════════════════════════════════════
    // VALIDATE — ADD CLASS
    // ════════════════════════════════════════════════════════════
    function validateAddClass() {
        const ids = ['addClassTitre','addClassLien','addClassCourse'];
        clearErrors(ids);
        let ok = true;
        if (!document.getElementById('addClassTitre').value.trim()) { showErr('addClassTitre'); ok = false; }
        const lien = document.getElementById('addClassLien').value.trim();
        if (!lien || !reUrl.test(lien)) { showErr('addClassLien'); ok = false; }
        if (!document.getElementById('addClassCourse').value) { showErr('addClassCourse'); ok = false; }
        if (ok) document.getElementById('formAddClass').submit();
    }

    // ════════════════════════════════════════════════════════════
    // VALIDATE — EDIT CLASS
    // ════════════════════════════════════════════════════════════
    function validateEditClass() {
        const ids = ['editClassTitre','editClassLien','editClassCourse'];
        clearErrors(ids);
        let ok = true;
        if (!document.getElementById('editClassTitre').value.trim()) { showErr('editClassTitre'); ok = false; }
        const lien = document.getElementById('editClassLien').value.trim();
        if (!lien || !reUrl.test(lien)) { showErr('editClassLien'); ok = false; }
        if (!document.getElementById('editClassCourse').value) { showErr('editClassCourse'); ok = false; }
        if (ok) document.getElementById('formEditClass').submit();
    }

    // EDIT CLASS — open modal
    function openEditClass(id, titre, desc, lien, plateforme, idCourse) {
        document.getElementById('editClassId').value    = id;
        document.getElementById('editClassTitre').value = titre;
        document.getElementById('editClassDesc').value  = desc;
        document.getElementById('editClassLien').value  = lien;
        setSelectValue('editClassPlateforme', plateforme);
        setSelectValue('editClassCourse', idCourse);
        clearErrors(['editClassTitre','editClassLien','editClassCourse']);
        openModal('modalEditClass');
    }

    // ════════════════════════════════════════════════════════════
    // VALIDATE — ADD SESSION
    // ════════════════════════════════════════════════════════════
    function validateAddSession() {
        const ids = ['addSessionClass','addSessionDate','addSessionDebut','addSessionFin','addSessionCapacite'];
        clearErrors(ids);
        let ok = true;
        if (!document.getElementById('addSessionClass').value) { showErr('addSessionClass'); ok = false; }
        const date = document.getElementById('addSessionDate').value.trim();
        if (!reDate.test(date)) { showErr('addSessionDate'); ok = false; }
        const debut = document.getElementById('addSessionDebut').value.trim();
        if (!reTime.test(debut)) { showErr('addSessionDebut'); ok = false; }
        const fin = document.getElementById('addSessionFin').value.trim();
        if (!reTime.test(fin) || (reTime.test(debut) && fin <= debut)) { showErr('addSessionFin'); ok = false; }
        const cap = document.getElementById('addSessionCapacite').value.trim();
        if (!reInt.test(cap) || parseInt(cap) < 0) { showErr('addSessionCapacite'); ok = false; }
        if (ok) document.getElementById('formAddSession').submit();
    }

    // ════════════════════════════════════════════════════════════
    // VALIDATE — EDIT SESSION
    // ════════════════════════════════════════════════════════════
    function validateEditSession() {
        const ids = ['editSessionClass','editSessionDate','editSessionDebut','editSessionFin','editSessionCapacite'];
        clearErrors(ids);
        let ok = true;
        if (!document.getElementById('editSessionClass').value) { showErr('editSessionClass'); ok = false; }
        const date = document.getElementById('editSessionDate').value.trim();
        if (!reDate.test(date)) { showErr('editSessionDate'); ok = false; }
        const debut = document.getElementById('editSessionDebut').value.trim();
        if (!reTime.test(debut)) { showErr('editSessionDebut'); ok = false; }
        const fin = document.getElementById('editSessionFin').value.trim();
        if (!reTime.test(fin) || (reTime.test(debut) && fin <= debut)) { showErr('editSessionFin'); ok = false; }
        const cap = document.getElementById('editSessionCapacite').value.trim();
        if (!reInt.test(cap) || parseInt(cap) < 0) { showErr('editSessionCapacite'); ok = false; }
        if (ok) document.getElementById('formEditSession').submit();
    }

    // EDIT SESSION — open modal
    function openEditSession(id, idClass, date, debut, fin, statut, capacite) {
        document.getElementById('editSessionId').value       = id;
        document.getElementById('editSessionDate').value     = date;
        document.getElementById('editSessionDebut').value    = debut;
        document.getElementById('editSessionFin').value      = fin;
        document.getElementById('editSessionCapacite').value = capacite;
        setSelectValue('editSessionClass', idClass);
        setSelectValue('editSessionStatut', statut);
        clearErrors(['editSessionClass','editSessionDate','editSessionDebut','editSessionFin','editSessionCapacite']);
        openModal('modalEditSession');
    }

    // Auto-dismiss alerts after 4s
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => a.style.opacity = '0');
    }, 4000);
</script>
</body>
</html>
