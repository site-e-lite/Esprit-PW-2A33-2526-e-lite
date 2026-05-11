<?php
require_once __DIR__ . '/../../../Controller/Forum/ForumController.php';
require_once __DIR__ . '/../../../Controller/Forum/PostController.php';
require_once __DIR__ . '/../../../Controller/Quiz/QuizController.php';

/** Chemin de cette page (sans query) — évite de poster vers /index.php racine qui perd le POST (redirect 302). */
$frontBasePath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}
$forumAssetsBase = $basePath . '/View/assets/Forum';
$isLoggedIn = isset($_SESSION['user_id']);
$roleName = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
$isAdminOrFormateur = in_array($roleName, ['admin', 'formateur', 'teacher', 'instructor'], true);

$forumController = new ForumController();
$postController = new PostController();
$frontOfficeUserId = Config::getOrCreateFrontOfficeUserId();

// Charger les quiz actifs pour la section Évaluations
$quizController = new QuizController();
$quizActifsResult = $quizController->afficherQuizsActifs();
$quizActifs = $quizActifsResult ? $quizActifsResult->fetchAll() : [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'rate_forum') {
        $idForum = intval($_POST['idForum']  ?? 0);
        $note    = intval($_POST['note']     ?? 0);
        $idUser  = intval($_POST['idUser']   ?? $frontOfficeUserId);
        if ($idForum > 0 && $note >= 1 && $note <= 5) {
            $newAvg = $forumController->raterForum($idForum, $note, $idUser);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'avg' => $newAvg]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'rate_post') {
        $idP   = intval($_POST['idPost'] ?? 0);
        $note  = intval($_POST['note']   ?? 0);
        if ($idP > 0 && $note >= 1 && $note <= 5) {
            $success = $postController->raterPost($idP, $note);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'chatbot_query') {
        header('Content-Type: application/json');
        try {
            require_once __DIR__ . '/../../../Controller/Forum/ChatbotController.php';
            $chatbot = new ChatbotController();
            $res = $chatbot->handleRequest($_POST['query'] ?? '');
            echo json_encode($res);
        } catch (Exception $e) {
            echo json_encode(['type' => 'text', 'message' => "L'IA est temporairement indisponible. Réessayez.", 'data' => []]);
        }
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'chatbot_summarize') {
        header('Content-Type: application/json');
        try {
            require_once __DIR__ . '/../../../Controller/Forum/ChatbotController.php';
            $chatbot = new ChatbotController();
            $s = $chatbot->summarizeThread(intval($_POST['idForum'] ?? 0));
            echo json_encode(['summary' => $s]);
        } catch (Exception $e) {
            echo json_encode(['summary' => "Résumé IA indisponible. Réessayez."]);
        }
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] == 'add_forum') {
        require_once __DIR__ . '/../../../Controller/Forum/ChatbotController.php';
        $chatbot = new ChatbotController();
        
        $combinedText = $_POST['titre'] . " " . $_POST['description'];
        $evaluation = $chatbot->evaluateContentRisk($combinedText);
        
        if ($evaluation['risk'] === 'High') {
            $errorMsg = urlencode("Risque élevé : " . $evaluation['reason']);
            header('Location: ' . $frontBasePath . '?error=toxic&msg=' . $errorMsg . '#forum');
            exit;
        }

        $forum = new Forum($_POST['titre'], $_POST['description'], $_POST['idCourse'] ?? 0);
        if (!$forumController->addForum($forum)) {
            header('Location: ' . $frontBasePath . '?error=no_course&msg=' . urlencode('Aucun cours en base : ajoute au moins un cours (back-office ou SQL) avant de créer un forum. ID cours 0 = premier cours disponible.') . '#forum');
            exit;
        }
        header('Location: ' . $frontBasePath . '#forum');
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] == 'add_post') {
        require_once __DIR__ . '/../../../Controller/Forum/ChatbotController.php';
        $chatbot = new ChatbotController();
        
        $evaluation = $chatbot->evaluateContentRisk($_POST['contenu']);
        
        if ($evaluation['risk'] === 'High') {
            $errorMsg = urlencode("Risque élevé : " . $evaluation['reason']);
            header('Location: ' . $frontBasePath . '?error=toxic&msg=' . $errorMsg . '#forum');
            exit;
        }

        $idF = max(1, (int) ($_POST['idForum'] ?? 1));
        $post = new Post(trim((string) ($_POST['contenu'] ?? '')), $frontOfficeUserId, $idF, '');
        if (!$postController->addPost($post)) {
            header('Location: ' . $frontBasePath . '?error=post&msg=' . urlencode("Enregistrement impossible (utilisateur ou forum invalide). Réessayez.") . '#forum');
            exit;
        }
        header('Location: ' . $frontBasePath . '#forum');
        exit;
    }
}

$db = Config::getConnexion();
$stmtForums = $db->query("
    SELECT f.*,
           COUNT(DISTINCT p.idPost) AS postCount,
           NULL AS avgRating,
           0    AS ratingCount
    FROM forum f
    LEFT JOIN post p ON p.idForum = f.idForum
    GROUP BY f.idForum
    ORDER BY f.dateCreation DESC LIMIT 10
");
$frontForums = $stmtForums->fetchAll(PDO::FETCH_ASSOC);

function getForumPosts($db, $idForum) {
    $stmt = $db->prepare("SELECT * FROM post WHERE idForum = :idForum ORDER BY datePost ASC");
    $stmt->execute(['idForum' => $idForum]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | E-Learning d'Excellence & IA</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($forumAssetsBase, ENT_QUOTES, 'UTF-8') ?>/index.css?v=<?= time() ?>">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ════════════════════════════════════
           STAR RATING WIDGET (inline)
        ════════════════════════════════════ */
        .star-widget { display: flex; flex-direction: column; align-items: center; gap: 0.3rem; }
        .star-widget .stars { display: flex; gap: 2px; }
        .star-widget .star {
            font-size: 1.1rem; cursor: pointer;
            color: rgba(255,255,255,0.15);
            transition: color 0.18s, transform 0.18s;
        }
        .star-widget .star.filled { color: #f59e0b; }
        .star-widget .star:hover  { transform: scale(1.25); }
        .star-widget .avg-label {
            font-size: 0.78rem; color: var(--light-gray);
            white-space: nowrap;
        }

        /* ════════════════════════════════════
           CHATBOT UI
        ════════════════════════════════════ */
        .chatbot-trigger {
            position: fixed; bottom: 30px; right: 30px;
            width: 60px; height: 60px; border-radius: 50%;
            background: var(--accent); color: #000;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; cursor: pointer; z-index: 1000;
            box-shadow: 0 8px 32px rgba(234,179,8,0.3);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .chatbot-trigger:hover { transform: scale(1.1) rotate(5deg); }
        .chatbot-window {
            position: fixed; bottom: 100px; right: 30px;
            width: 350px; height: 500px; border-radius: 20px;
            background: rgba(15, 15, 15, 0.85); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            display: flex; flex-direction: column; overflow: hidden;
            z-index: 1000; transform: translateY(20px); opacity: 0; pointer-events: none;
            transition: all 0.3s ease;
        }
        .chatbot-window.active { transform: translateY(0); opacity: 1; pointer-events: all; }
        .chat-header { padding: 1.2rem; background: rgba(255,255,255,0.05); display: flex; align-items: center; gap: 0.8rem; border-bottom: 1px solid var(--glass-border); }
        .chat-header .bot-icon { width: 35px; height: 35px; background: var(--accent); color: #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .chat-messages { flex: 1; padding: 1rem; overflow-y: auto; display: flex; flex-direction: column; gap: 0.8rem; }
        .msg { max-width: 80%; padding: 0.8rem 1rem; border-radius: 15px; font-size: 0.9rem; line-height: 1.4; animation: slideInChat 0.3s ease; }
        .msg.bot { background: rgba(255,255,255,0.05); align-self: flex-start; color: var(--text-main); border-bottom-left-radius: 2px; }
        .msg.user { background: var(--accent); color: #000; align-self: flex-end; border-bottom-right-radius: 2px; font-weight: 500; }
        .chat-input { padding: 1rem; display: flex; gap: 0.5rem; background: rgba(0,0,0,0.2); }
        .chat-input input { flex: 1; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); padding: 0.6rem 1rem; border-radius: 20px; color: #fff; outline: none; }
        @keyframes slideInChat { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: 0; } }
    </style>
</head>
<body>
    
    <!-- NOTIFICATION MODERATION IA -->
    <?php if(isset($_GET['error']) && $_GET['error'] == 'toxic'): ?>
    <div id="aiModerationAlert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; background: rgba(220, 38, 38, 0.9); backdrop-filter: blur(10px); color: white; padding: 1rem 2rem; border-radius: 12px; border: 1px solid #f87171; box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4); display: flex; align-items: center; gap: 1rem; animation: slideDown 0.5s ease-out;">
        <i class="fas fa-shield-alt" style="font-size: 1.5rem;"></i>
        <div>
            <strong style="display: block; font-size: 1.1rem; margin-bottom: 0.2rem;">Modération IA : Contenu Bloqué</strong>
            <span style="font-size: 0.9rem;"><?= htmlspecialchars($_GET['msg'] ?? 'Votre message contient un vocabulaire inapproprié.') ?></span>
        </div>
        <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem; margin-left: 1rem;">&times;</button>
    </div>
    <style>
        @keyframes slideDown { from { top: -50px; opacity: 0; } to { top: 20px; opacity: 1; } }
    </style>
    <script>
        // Auto-hide after 8 seconds
        setTimeout(() => {
            const alert = document.getElementById('aiModerationAlert');
            if(alert) alert.style.opacity = '0';
            setTimeout(() => { if(alert) alert.style.display = 'none'; }, 500);
        }, 8000);
    </script>
    <?php elseif (isset($_GET['error']) && in_array($_GET['error'], ['no_course', 'forum_db', 'post'], true)): ?>
    <div style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; background: rgba(180, 83, 9, 0.95); color: white; padding: 1rem 2rem; border-radius: 12px; border: 1px solid #fbbf24; max-width: min(560px, 92vw);">
        <strong>Forum</strong>
        <p style="margin: 0.4rem 0 0;"><?= htmlspecialchars($_GET['msg'] ?? 'Impossible d\'enregistrer le forum.', ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <?php endif; ?>

    <!-- OVERLAYS & MODALS (Gestions Forms) -->
    <div class="modal-overlay" id="modalOverlay"></div>

    <!-- 1. GESTION UTILISATEURS MODALS -->
    <!-- Inscription Modal -->
    <div class="modal" id="modalRegister">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Créer un compte</h3>
            <button class="close-btn" onclick="closeModal('modalRegister')">&times;</button>
        </div>
        <div class="modal-body">
            <form class="glass-form">
                <div class="form-grid">
                    <div class="form-group"><label>Nom</label><input type="text" placeholder="Votre nom"></div>
                    <div class="form-group"><label>Prénom</label><input type="text" placeholder="Votre prénom"></div>
                    <div class="form-group"><label>Email</label><input type="email" placeholder="email@exemple.com"></div>
                    <div class="form-group"><label>Mot de passe</label><input type="password"></div>
                    <div class="form-group"><label>Téléphone</label><input type="tel" placeholder="+33..."></div>
                    <div class="form-group"><label>Date de Naissance</label><input type="date"></div>
                    <div class="form-group"><label>Rôle</label>
                        <select>
                            <option value="student">Étudiant</option>
                            <option value="instructor">Formateur</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Photo de Profil (URL)</label><input type="url"></div>
                </div>
                <button type="button" class="btn-primary w-100 mt-3" onclick="closeModal('modalRegister')"><i class="fas fa-check-circle"></i> S'inscrire (Authentification sécurisée)</button>
            </form>
        </div>
    </div>

    <!-- Profil Modal (Modification) -->
    <div class="modal" id="modalProfile">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Modifier le Profil</h3>
            <button class="close-btn" onclick="closeModal('modalProfile')">&times;</button>
        </div>
        <div class="modal-body">
            <form class="glass-form">
                <div class="form-group"><label>Statut Actuel</label><input type="text" value="Actif" disabled></div>
                <div class="form-group"><label>Nouveau Mot de Passe</label><input type="password"></div>
                <button type="button" class="btn-primary w-100 mt-3" onclick="closeModal('modalProfile')">Mettre à jour</button>
            </form>
        </div>
    </div>

    <!-- 2. GESTION COURS MODALS -->
    <div class="modal" id="modalAddCourse">
        <div class="modal-header">
            <h3><i class="fas fa-book-open"></i> Ajouter un Cours</h3>
            <button class="close-btn" onclick="closeModal('modalAddCourse')">&times;</button>
        </div>
        <div class="modal-body">
            <form class="glass-form form-grid">
                <div class="form-group"><label>Titre</label><input type="text"></div>
                <div class="form-group"><label>Prix (€)</label><input type="number"></div>
                <div class="form-group full-width"><label>Description</label><textarea></textarea></div>
                <div class="form-group"><label>Niveau</label>
                    <select><option>Débutant</option><option>Intermédiaire</option><option>Avancé</option></select>
                </div>
                <div class="form-group"><label>Langue</label><input type="text" value="Français"></div>
                <div class="form-group"><label>Durée (heures)</label><input type="number"></div>
                <div class="form-group"><label>Image (URL)</label><input type="url"></div>
                <button type="button" class="btn-primary full-width mt-3" onclick="closeModal('modalAddCourse')">Ajouter le cours</button>
            </form>
        </div>
    </div>

    <!-- Enrollment Form (Version Forte) -->
    <div class="modal" id="modalEnrollment">
        <div class="modal-header">
            <h3><i class="fas fa-graduation-cap"></i> Inscription au Cours</h3>
            <p class="subtitle">Votre parcours intelligent commence ici.</p>
            <button class="close-btn" onclick="closeModal('modalEnrollment')">&times;</button>
        </div>
        <div class="modal-body">
            <form class="glass-form form-grid">
                <div class="form-group"><label>Niveau Initial</label>
                    <select><option>Novice</option><option>Amateur</option><option>Expérimenté</option></select>
                </div>
                <div class="form-group"><label>Mode d'Accès</label>
                    <select><option>Digital 100% (Éco)</option><option>Hybride</option></select>
                </div>
                <div class="form-group full-width"><label>Objectif Personnel</label>
                    <textarea placeholder="Que souhaitez-vous accomplir ?"></textarea>
                </div>
                <div class="form-group full-width"><label>Engagement (Heures/Semaine)</label>
                    <input type="range" min="1" max="40" value="5" oninput="this.nextElementSibling.value = this.value + ' h/semaine'">
                    <output>5 h/semaine</output>
                </div>
                <button type="button" class="btn-primary full-width mt-3" onclick="closeModal('modalEnrollment')"><i class="fas fa-rocket"></i> Lancer le suivi IA progressif</button>
            </form>
        </div>
    </div>

    <!-- 3. GESTION EVALUATION MODALS -->
    <div class="modal" id="modalAddQuiz">
        <div class="modal-header">
            <h3><i class="fas fa-tasks"></i> Ajouter un Quiz</h3>
            <button class="close-btn" onclick="closeModal('modalAddQuiz')">&times;</button>
        </div>
        <div class="modal-body">
            <form class="glass-form">
                <div class="form-group"><label>Titre du Quiz</label><input type="text"></div>
                <div class="form-group"><label>Seuil de Réussite (%)</label><input type="number" min="0" max="100"></div>
                <button type="button" class="btn-primary w-100" onclick="closeModal('modalAddQuiz')">Enregistrer le Quiz</button>
            </form>
        </div>
    </div>

    <!-- 4. GESTION FORUM MODALS -->
    <div class="modal" id="modalAddForum">
        <div class="modal-header">
            <h3><i class="fas fa-comments"></i> Créer un Forum</h3>
            <button class="close-btn" onclick="closeModal('modalAddForum')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="" method="POST" class="glass-form form-grid" onsubmit="return validateForum(this)">
                <input type="hidden" name="action" value="add_forum">
                <div class="form-group full-width"><label>Titre</label><input type="text" name="titre" placeholder="Minimum 3 caractères"></div>
                <div class="form-group full-width"><label>Description</label><textarea name="description" placeholder="Minimum 10 caractères"></textarea></div>
                <div class="form-group"><label>ID cours (0 = premier cours en base)</label><input type="number" name="idCourse" value="0" min="0"></div>
                <div style="display: flex; flex-direction: column; gap: 0.8rem; margin-top: 1rem;" class="full-width">
                    <button type="button" class="btn-outline" style="border-color: var(--accent); color: var(--accent); padding: 0.6rem;" onclick="checkDuplicates(this.form)">
                        <i class="fas fa-robot"></i> Vérifier si ce sujet existe déjà (IA)
                    </button>
                    <button type="submit" class="btn-primary" style="padding: 0.8rem;">Créer le forum</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modalAddPost">
        <div class="modal-header">
            <h3><i class="fas fa-reply"></i> Ajouter un Post</h3>
            <button class="close-btn" onclick="closeModal('modalAddPost')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="" method="POST" class="glass-form" onsubmit="return validatePost(this)">
                <input type="hidden" name="action" value="add_post">
                <input type="hidden" name="idForum" id="reply_forum_id">
                <div class="form-group"><label>Votre Réponse</label><textarea name="contenu" style="min-height: 120px;" placeholder="Tapez 5 caractères minimum..."></textarea></div>
                <div style="display: flex; flex-direction: column; gap: 0.8rem; margin-top: 1rem;">
                    <button type="button" class="btn-outline" style="border-color: var(--accent); color: var(--accent); padding: 0.6rem;" onclick="checkDuplicates(this.form)">
                        <i class="fas fa-robot"></i> Voir les discussions similaires
                    </button>
                    <button type="submit" class="btn-primary" style="padding: 0.8rem;"><i class="fas fa-paper-plane"></i> Publier la réponse</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5. GESTION CLASSES VIRTUELLES MODALS -->
    <!-- Modale générique pour limiter la taille, utilisées pour Forum et Session -->
    <div class="modal" id="modalGeneric">
        <div class="modal-header">
            <h3 id="genericModalTitle">Titre</h3>
            <button class="close-btn" onclick="closeModal('modalGeneric')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="glass-form info-box">
                <p><i class="fas fa-info-circle"></i> Interface de gestion administrateur (statique pour démo).</p>
                <br>
                <button class="btn-primary w-100" onclick="closeModal('modalGeneric')">Fermer</button>
            </div>
        </div>
    </div>


    <!-- HEADER NAVIGATION -->
    <header id="main-header">
        <nav>
            <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/forum" class="logo">e-lite<span>.</span></a>
            <ul class="nav-links">
                <li><a href="#accueil">Accueil</a></li>
                <li><a href="#cours">Cours & IA</a></li>
                <li><a href="#evaluations">Évaluations</a></li>
                <li><a href="#forum">Communauté</a></li>
                <li><a href="#classes">Classes Virtuelles</a></li>
            </ul>
            <div class="auth-buttons">
                <?php if ($isLoggedIn): ?>
                    <a class="btn-icon" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/profile" title="Mon Profil"><i class="fas fa-user-circle"></i></a>
                    <?php if ($isAdminOrFormateur): ?>
                        <a class="btn-outline" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/forum/manage">BackOffice Forum</a>
                    <?php endif; ?>
                    <a class="btn-outline" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                <?php else: ?>
                    <a class="btn-outline" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/login">Connexion</a>
                    <button class="btn-outline" onclick="openModal('modalRegister')">S'inscrire</button>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- HERO SECTION (Eco-responsable & Digital) -->
    <section id="accueil" class="hero reveal">
        <div class="hero-bg-anim"></div>
        <div class="hero-content">
            <div class="eco-badge"><i class="fas fa-leaf"></i> 100% Digital & Zéro Papier</div>
            <h1>Apprentissage <span class="text-gradient">Intelligent</span> & Engagé</h1>
            <p>Découvrez la puissance de l'IA pour personnaliser votre progression. Obtenez vos certificats numériques dans un environnement éco-responsable.</p>
            <div class="hero-actions">
                <a href="#cours" class="btn-primary">Explorer les Cours IA</a>
                <button class="btn-outline" onclick="openModal('modalAddCourse')">Mode Formateur</button>
            </div>
        </div>
    </section>

    <!-- STATS / DURABILITY -->
    <section class="stats-section reveal">
        <div class="stats-grid">
            <div class="stat-item glass-card">
                <i class="fas fa-tree accent-icon"></i>
                <h3>100%</h3>
                <p>Digital & Sans Papier</p>
            </div>
            <div class="stat-item glass-card">
                <i class="fas fa-robot accent-icon"></i>
                <h3>IA</h3>
                <p>Recommandations Précises</p>
            </div>
            <div class="stat-item glass-card">
                <i class="fas fa-certificate accent-icon"></i>
                <h3>24/7</h3>
                <p>Certificats Numériques</p>
            </div>
            <div class="stat-item glass-card">
                <i class="fas fa-users accent-icon"></i>
                <h3>10k+</h3>
                <p>Membres Actifs</p>
            </div>
        </div>
    </section>

    <!-- GESTION COURS & INSCRIPTION -->
    <section id="cours" class="gestion-section reveal">
        <div class="section-header">
            <h2>Nos Cours & <span class="text-gradient">Catalogue IA</span></h2>
            <p>Intelligence Artificielle : Cours recommandés pour vous selon votre niveau.</p>
        </div>

        <div class="courses-grid">
            <!-- Course 1 (IA Recommended) -->
            <div class="course-card glass-card top-course">
                <div class="ai-badge"><i class="fas fa-magic"></i> Recommandé par l'IA</div>
                <div class="course-img" style="background-image: url('https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80');"></div>
                <div class="course-info">
                    <span class="level">Débutant</span>
                    <h3>Développement Web Moderne</h3>
                    <p>Maitrisez JS et CSS en construisant des projets.</p>
                    <div class="course-meta">
                        <span><i class="far fa-clock"></i> 40h</span>
                        <span><i class="fas fa-euro-sign"></i> 49.99</span>
                    </div>
                    <button class="btn-primary w-100" onclick="openModal('modalEnrollment')">S'inscrire à ce cours (Enrollment)</button>
                </div>
            </div>

            <!-- Course 2 -->
            <div class="course-card glass-card">
                <div class="course-img" style="background-image: url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=600&q=80');"></div>
                <div class="course-info">
                    <span class="level">Avancé</span>
                    <h3>Machine Learning Avancé</h3>
                    <p>Découvrez les algorithmes derrière nos recommandations.</p>
                    <div class="course-meta">
                        <span><i class="far fa-clock"></i> 60h</span>
                        <span><i class="fas fa-euro-sign"></i> 89.99</span>
                    </div>
                    <button class="btn-outline w-100" onclick="openModal('modalEnrollment')">S'inscrire</button>
                </div>
            </div>
        </div>
    </section>

    <!-- GESTION EVALUATIONS → vrais quiz depuis la DB -->
    <section id="evaluations" class="gestion-section reveal dark-bg">
        <div class="section-header">
            <h2>Évaluations & <span class="text-gradient">Quiz Adaptatifs</span></h2>
            <p>Testez vos connaissances avec nos quiz. Calcul automatique des scores.</p>
        </div>

        <?php if (!empty($quizActifs)): ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px,1fr)); gap:1.5rem; max-width:1100px; margin:0 auto;">
            <?php foreach ($quizActifs as $quiz): ?>
            <div class="glass-card" style="padding:1.8rem; border-radius:20px; position:relative; overflow:hidden; display:flex; flex-direction:column; gap:1rem;">
                <!-- barre accent -->
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#eab308,#d97706);"></div>

                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.8rem;">
                    <h3 style="margin:0;font-size:1.1rem;flex:1;">
                        <?= htmlspecialchars($quiz['titre']) ?>
                    </h3>
                    <span style="background:rgba(234,179,8,0.15);color:#eab308;border-radius:999px;padding:0.25rem 0.7rem;font-size:0.78rem;font-weight:700;white-space:nowrap;">
                        <?= htmlspecialchars($quiz['niveau']) ?>
                    </span>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:0.8rem;color:rgba(255,255,255,0.55);font-size:0.85rem;">
                    <span><i class="fas fa-clock"></i> <?= htmlspecialchars($quiz['duree']) ?> min</span>
                    <span><i class="fas fa-percent"></i> Seuil <?= htmlspecialchars($quiz['seuilReussite']) ?>%</span>
                </div>

                <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/quiz/passer?id=<?= (int)$quiz['idQuiz'] ?>"
                   style="margin-top:auto;display:inline-flex;align-items:center;justify-content:center;gap:0.5rem;background:linear-gradient(135deg,#eab308,#d97706);color:#000;font-weight:700;padding:0.75rem 1.2rem;border-radius:12px;text-decoration:none;transition:transform 0.2s,box-shadow 0.3s;"
                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(234,179,8,0.35)'"
                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <i class="fas fa-play-circle"></i> Commencer
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="glass-card" style="padding:2.5rem;text-align:center;border-radius:20px;max-width:480px;margin:0 auto;">
            <i class="fas fa-clipboard-list" style="font-size:2.5rem;color:rgba(255,255,255,0.2);margin-bottom:1rem;display:block;"></i>
            <p style="color:rgba(255,255,255,0.5);margin:0;">Aucun quiz disponible pour le moment.</p>
            <?php if ($isAdminOrFormateur): ?>
            <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/quiz/admin"
               style="display:inline-flex;align-items:center;gap:0.5rem;margin-top:1.2rem;background:linear-gradient(135deg,#eab308,#d97706);color:#000;font-weight:700;padding:0.7rem 1.4rem;border-radius:12px;text-decoration:none;">
                <i class="fas fa-plus"></i> Créer un quiz
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- GESTION FORUM ET COMMU -->
    <section id="forum" class="gestion-section reveal">
        <div class="section-header">
            <h2>Forum & <span class="text-gradient">Discussions</span></h2>
            <p>Tri intelligent et suggestions de réponses IA pour booster l'entraide.</p>
            <button class="btn-outline mt-3" onclick="openModal('modalAddForum')"><i class="fas fa-plus"></i> Créer Forum</button>
        </div>

        <div class="pro-forum-container glass-card" style="padding: 0; overflow: hidden;">
            <!-- Header du Tableau Pro -->
            <div class="forum-header" style="display: grid; grid-template-columns: 3fr 1fr 1fr 1fr; padding: 1rem 2rem; background: rgba(255,255,255,0.05); border-bottom: 1px solid var(--glass-border); font-weight: 600; color: var(--light-gray); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">
                <div>Forum / Catégorie</div>
                <div style="text-align: center;">Statistiques</div>
                <div style="text-align: center;">Note ★</div>
                <div style="text-align: right;">Activité Récente</div>
            </div>
            
            <div class="forum-body">
            <?php if (!empty($frontForums)): ?>
                <?php foreach ($frontForums as $f): 
                    $posts = getForumPosts($db, $f['idForum']);
                    $postCount = count($posts);
                    // Obtenir le dernier post s'il y en a
                    $lastPost = $postCount > 0 ? end($posts) : null;
                ?>
                <!-- Ligne Principale du Forum (Cliquable) -->
                <div class="forum-row" style="display: grid; grid-template-columns: 3fr 1fr 1fr 1fr; padding: 1.5rem 2rem; border-bottom: 1px solid var(--glass-border); align-items: center; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.02)';" onmouseout="this.style.background='transparent';">

                    
                    <div style="display: flex; gap: 1.5rem; align-items: center;">
                        <div style="min-width: 50px; height: 50px; background: rgba(234,179,8,0.1); color: var(--accent); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div style="flex: 1;" onclick="toggleForumView(<?= $f['idForum'] ?>)">
                            <h3 style="margin: 0 0 0.3rem 0; font-size: 1.15rem; color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($f['titre']) ?></h3>
                            <p style="margin: 0; font-size: 0.85rem; color: var(--light-gray); line-height: 1.4;"><?= htmlspecialchars($f['description']) ?></p>
                        </div>
                    </div>

                    <div style="text-align: center; color: var(--light-gray); font-size: 0.85rem;" onclick="toggleForumView(<?= $f['idForum'] ?>)">
                        <strong style="color: var(--text-main); font-size: 1.2rem; display: block; margin-bottom: 0.2rem;"><?= $postCount ?></strong> Messages
                    </div>

                    <!-- Star rating widget for forum -->
                    <div class="star-widget" id="forumRating_<?= $f['idForum'] ?>" onclick="event.stopPropagation();">
                        <div class="stars">
                            <?php
                            $avg = floatval($f['avgRating'] ?? 0);
                            for ($s = 1; $s <= 5; $s++):
                            ?>
                            <span class="star <?= $avg >= $s ? 'filled' : '' ?>"
                                  onclick="rateForum(<?= $f['idForum'] ?>, <?= $s ?>)"
                                  data-forum="<?= $f['idForum'] ?>"
                                  data-val="<?= $s ?>"
                                  title="<?= $s ?> étoile(s)"
                                  onmouseover="hoverStars(this)"
                                  onmouseleave="resetStars(<?= $f['idForum'] ?>)">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="avg-label" id="forumAvgLabel_<?= $f['idForum'] ?>">
                            <?= $f['avgRating'] ? $f['avgRating'] . ' (' . $f['ratingCount'] . ' avis)' : 'Pas encore noté' ?>
                        </span>
                    </div>

                    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 0.3rem;" onclick="toggleForumView(<?= $f['idForum'] ?>)">
                        <?php if($lastPost): ?>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 0.8rem; color: var(--light-gray);">Par <strong style="color:var(--text-main);">#<?= $lastPost['idUser'] ?></strong></span>
                                <img src="https://ui-avatars.com/api/?name=User+<?= $lastPost['idUser'] ?>&background=333&color=fff" style="width: 24px; height: 24px; border-radius: 50%;">
                            </div>
                            <span style="font-size: 0.75rem; color: var(--accent);"><i class="far fa-clock"></i> <?= $lastPost['datePost'] ?></span>
                        <?php else: ?>
                            <span style="font-size: 0.85rem; color: var(--light-gray); font-style: italic;">Aucun message</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- VUE DES MESSAGES DU FORUM (Caché par défaut) -->
                <div id="forum-thread-<?= $f['idForum'] ?>" style="display: none; background: rgba(0,0,0,0.3); padding: 2rem; border-bottom: 1px solid var(--glass-border); box-shadow: inset 0 5px 15px rgba(0,0,0,0.5);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <h4 style="margin: 0; color: var(--accent); font-size: 1.1rem;"><i class="fas fa-stream"></i> Fil de discussion : <?= htmlspecialchars($f['titre']) ?></h4>
                        <div style="display: flex; gap: 0.8rem;">
                            <button class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem; border-color: var(--accent); color: var(--accent);" onclick="summarizeAI(<?= $f['idForum'] ?>)">
                                <i class="fas fa-robot"></i> Résumer par IA
                            </button>
                            <button class="btn-primary" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;" onclick="openReplyModal(<?= $f['idForum'] ?>)"><i class="fas fa-reply"></i> Nouveau Message</button>
                        </div>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php if($postCount > 0): ?>
                            <?php foreach($posts as $p): ?>
                                <?php $replyId = 'reply-' . $p['idPost']; $previewText = mb_substr($p['contenu'], 0, 80); ?>
                                <div class="post-card" id="post-<?= $p['idPost'] ?>" style="display: flex; flex-direction: column; gap: 0; background: rgba(255,255,255,0.02); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); overflow: hidden;">
                                    <!-- Main message row -->
                                    <div style="display: flex; gap: 2rem; padding: 1.5rem;">
                                        <!-- Auteur Block -->
                                        <div style="text-align: center; min-width: 90px;">
                                            <img src="https://ui-avatars.com/api/?name=User+<?= $p['idUser'] ?>&background=d4af37&color=000" style="width: 55px; height: 55px; border-radius: 50%; margin-bottom: 0.5rem; border: 2px solid var(--accent);">
                                            <strong style="display: block; font-size: 0.85rem; color: var(--text-main);">User #<?= htmlspecialchars($p['idUser']) ?></strong>
                                            <span style="font-size: 0.7rem; color: var(--accent); background: rgba(234,179,8,0.1); padding: 0.15rem 0.4rem; border-radius: 12px; display: inline-block; margin-top: 0.2rem;">Membre</span>
                                        </div>
                                        
                                        <!-- Contenu Block -->
                                        <div style="flex: 1; display: flex; flex-direction: column;">
                                            <div style="margin-bottom: 0.8rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.78rem; color: var(--light-gray); display: flex; justify-content: space-between; align-items: center;">
                                                <span><i class="far fa-clock"></i> Posté le <?= htmlspecialchars($p['datePost']) ?></span>
                                                <span style="font-size:0.75rem; color:var(--light-gray); opacity:0.6;">#<?= htmlspecialchars($p['idPost']) ?></span>
                                            </div>
                                            <p style="margin: 0 0 1rem 0; font-size: 0.95rem; line-height: 1.6; color: rgba(255,255,255,0.9); flex: 1;"><?= nl2br(htmlspecialchars($p['contenu'])) ?></p>
                                            <!-- Reply trigger button -->
                                            <div style="display: flex; justify-content: flex-end;">
                                                <button onclick="toggleInlineReply('<?= $replyId ?>', '<?= htmlspecialchars(addslashes($previewText), ENT_QUOTES) ?>', <?= $p['idUser'] ?>)" style="background: rgba(234,179,8,0.08); color: var(--accent); border: 1px solid rgba(234,179,8,0.3); padding: 0.35rem 0.9rem; border-radius: 20px; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(234,179,8,0.2)'" onmouseout="this.style.background='rgba(234,179,8,0.08)'">
                                                    <i class="fas fa-reply"></i> Répondre
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Inline Reply Form (hidden by default) -->
                                    <div id="<?= $replyId ?>" data-reply-box style="display: none; padding: 1.2rem 1.5rem 1.5rem 1.5rem; border-top: 1px solid rgba(234,179,8,0.15); background: rgba(234,179,8,0.03);">
                                        <form action="" method="POST" novalidate onsubmit="return validatePost(this)">
                                            <input type="hidden" name="action" value="add_post">
                                            <input type="hidden" name="idForum" value="<?= $f['idForum'] ?>">
                                            <div style="margin-bottom: 0.8rem;">
                                                <div id="<?= $replyId ?>-quote" style="background: rgba(0,0,0,0.3); border-left: 3px solid var(--accent); padding: 0.6rem 1rem; border-radius: 4px; font-size: 0.8rem; color: var(--light-gray); margin-bottom: 0.7rem; font-style: italic;"></div>
                                                <textarea name="contenu" id="<?= $replyId ?>-textarea" style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(234,179,8,0.2); color: var(--text-main); padding: 0.8rem; border-radius: 6px; resize: vertical; min-height: 80px; font-family: inherit; font-size: 0.9rem;" placeholder="Tapez votre réponse (min. 5 caractères)..."></textarea>
                                            </div>
                                            <div style="display: flex; justify-content: flex-end; gap: 0.7rem;">
                                                <button type="button" onclick="toggleInlineReply('<?= $replyId ?>')" style="background: transparent; color: var(--light-gray); border: 1px solid rgba(255,255,255,0.1); padding: 0.4rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">Annuler</button>
                                                <button type="submit" style="background: var(--accent); color: #000; border: none; padding: 0.4rem 1.2rem; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;"><i class="fas fa-paper-plane"></i> Envoyer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: var(--light-gray);">
                                <i class="far fa-frown" style="font-size: 2.5rem; opacity: 0.5; margin-bottom: 1rem;"></i>
                                <p style="font-size: 0.95rem;">Aucune discussion n'a été trouvée dans cette catégorie.<br>Soyez le premier à poser une question !</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 3rem; text-align: center; color: var(--light-gray);">Aucun forum n'est disponible pour le moment.</div>
            <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- GESTION CLASSES VIRTUELLES -->
    <?php
    require_once __DIR__ . '/../../../Controller/VirtualClass/SessionController.php';
    $sessionCtrl = new SessionController();
    $allSessions = $sessionCtrl->afficherSessions();
    $seancesDisponibles = array_values(array_filter($allSessions, function($s) {
        return in_array($s['statut'] ?? '', ['planifiee', 'en_cours']);
    }));
    usort($seancesDisponibles, fn($a, $b) =>
        strcmp($a['dateSession'] . $a['heureDebut'], $b['dateSession'] . $b['heureDebut'])
    );
    $moisFr = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    ?>
    <section id="classes" class="gestion-section dark-bg">
        <div class="section-header">
            <h2>Classes <span class="text-gradient">Virtuelles</span></h2>
            <p>Rejoignez une séance en direct sur votre classe virtuelle.</p>
            <?php if ($isAdminOrFormateur): ?>
                <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/virtualclass" class="btn-outline mt-3"><i class="fas fa-cog"></i> Gérer les Classes</a>
            <?php endif; ?>
        </div>

        <div class="classes-container">
        <?php if (empty($seancesDisponibles)): ?>
            <div style="text-align:center;padding:3rem;color:var(--light-gray);">
                <i class="fas fa-calendar-times" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem;"></i>
                <p>Aucune séance disponible pour le moment.</p>
            </div>
        <?php else: foreach ($seancesDisponibles as $s):
            $dateObj   = new DateTime($s['dateSession']);
            $isEnCours = ($s['statut'] === 'en_cours');
            $platIcons = ['Zoom'=>'fab fa-video','Meet'=>'fab fa-google','Teams'=>'fab fa-microsoft','Autre'=>'fas fa-laptop'];
            $platIcon  = $platIcons[$s['plateforme'] ?? ''] ?? 'fas fa-laptop';
        ?>
            <div class="class-card glass-card reveal<?= $isEnCours ? '' : '' ?>">
                <div class="class-date">
                    <span class="day"><?= $dateObj->format('d') ?></span>
                    <span class="month"><?= $moisFr[(int)$dateObj->format('n')] ?></span>
                </div>
                <div class="class-info">
                    <h3><?= htmlspecialchars($s['classTitre'] ?? 'Séance') ?></h3>
                    <p>
                        <i class="fas fa-clock"></i>
                        <?= htmlspecialchars(substr($s['heureDebut'],0,5)) ?> - <?= htmlspecialchars(substr($s['heureFin'],0,5)) ?>
                        &nbsp;|&nbsp;
                        <i class="<?= $platIcon ?>"></i>
                        <?= htmlspecialchars($s['plateforme'] ?? 'En ligne') ?>
                        <?php if (!empty($s['courseTitre'])): ?>
                            &nbsp;|&nbsp;<i class="fas fa-book-open"></i> <?= htmlspecialchars($s['courseTitre']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="class-action">
                    <span class="capacity"><?= (int)($s['classCapacite'] ?? 0) ?> places</span>
                    <?php if (!empty($s['lienAcces'])): ?>
                        <a href="<?= htmlspecialchars($s['lienAcces']) ?>" target="_blank" class="btn-primary" rel="noopener">Rejoindre</a>
                    <?php else: ?>
                        <button class="btn-primary" onclick="openGenericModal('Séance', 'Lien bientôt disponible.')">Rejoindre</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-content">
            <div class="logo">e-lite<span>.</span></div>
            <div class="footer-links">
                <a href="#accueil">Mentions Légales</a>
                <a href="#accueil"><i class="fas fa-leaf"></i> Charte Éco-responsabilité</a>
                <a href="#accueil">Contact</a>
            </div>
            <p>&copy; 2026 Plateforme e-lite. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- CHATBOT WIDGET -->
    <div class="chatbot-trigger" onclick="toggleChat()">
        <i class="fas fa-robot"></i>
    </div>
    <div class="chatbot-window" id="chatbotWindow">
        <div class="chat-header">
            <div class="bot-icon"><i class="fas fa-microchip"></i></div>
            <div>
                <strong style="display:block; font-size: 0.95rem;">e-lite AI Assistant</strong>
                <span style="font-size: 0.75rem; color: var(--green-eco);"><i class="fas fa-circle" style="font-size: 0.5rem;"></i> En ligne</span>
            </div>
            <button onclick="toggleChat()" style="margin-left: auto; background:none; border:none; color:#fff; cursor:pointer; font-size:1.2rem;">&times;</button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="msg bot">Bonjour ! Je suis l'assistant intelligent e-lite. Comment puis-je vous aider aujourd'hui ? 🤖</div>
        </div>
        <div class="chat-input">
            <input type="text" id="chatInput" placeholder="Posez votre question..." onkeypress="if(event.key==='Enter') sendChat()">
            <button onclick="sendChat()" style="background:var(--accent); border:none; width:35px; height:35px; border-radius:50%; cursor:pointer;"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <script src="<?= htmlspecialchars($forumAssetsBase, ENT_QUOTES, 'UTF-8') ?>/index.js?v=<?= time() ?>"></script>
    <script>
        /* Même URL que la page courante (évite fetch/index.php → mauvais script si URL = / ou router). */
        const FORUM_POST_URL = window.location.pathname || '/';

        /* ══════════════════════════════════════
           CHATBOT LOGIC
        ══════════════════════════════════════ */
        function toggleChat() {
            document.getElementById('chatbotWindow').classList.toggle('active');
        }

        function sendChat() {
            const input = document.getElementById('chatInput');
            const container = document.getElementById('chatMessages');
            const query = input.value.trim();
            if (!query) return;

            // Add user message
            container.innerHTML += `<div class="msg user">${query}</div>`;
            input.value = '';
            container.scrollTop = container.scrollHeight;

            // Typing indicator
            const typingId = 'typing-' + Date.now();
            container.innerHTML += `<div class="msg bot" id="${typingId}"><i>L'IA réfléchit...</i></div>`;
            container.scrollTop = container.scrollHeight;

            const fd = new FormData();
            fd.append('action', 'chatbot_query');
            fd.append('query', query);

            fetch(FORUM_POST_URL, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    const typing = document.getElementById(typingId);
                    if (data.type === 'text') {
                        typing.innerHTML = data.message;
                    } else if (data.type === 'threads') {
                        let html = `<p>${data.message}</p><ul style="margin-top:0.5rem; padding-left:1.2rem;">`;
                        data.data.forEach(t => {
                            html += `<li><a href="#forum" onclick="toggleForumView(${t.idForum})" style="color:var(--accent);">${t.titre}</a></li>`;
                        });
                        html += `</ul>`;
                        typing.innerHTML = html;
                    } else if (data.type === 'courses') {
                        let html = `<p>${data.message}</p>`;
                        data.data.forEach(c => {
                            html += `<div style="margin-top:0.5rem; padding:0.5rem; background:rgba(255,255,255,0.05); border-radius:8px; border-left:3px solid var(--accent);">
                                        <i class="fas fa-graduation-cap"></i> <a href="${c.link}" style="color:#fff; text-decoration:none;">${c.title}</a>
                                     </div>`;
                        });
                        typing.innerHTML = html;
                    }
                    container.scrollTop = container.scrollHeight;
                });
        }

        function summarizeAI(idForum) {
            const fd = new FormData();
            fd.append('action', 'chatbot_summarize');
            fd.append('idForum', idForum);

            // Open chat and show loading
            if(!document.getElementById('chatbotWindow').classList.contains('active')) toggleChat();
            const container = document.getElementById('chatMessages');
            const typingId = 'summary-' + Date.now();
            container.innerHTML += `<div class="msg bot" id="${typingId}"><i>Génération du résumé du thread #${idForum} en cours...</i></div>`;
            container.scrollTop = container.scrollHeight;

            fetch(FORUM_POST_URL, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    document.getElementById(typingId).innerHTML = `<i class="fas fa-magic"></i> ${data.summary}`;
                    container.scrollTop = container.scrollHeight;
                });
        }

        /* ══════════════════════════════════════
           FORUM STAR RATING
        ══════════════════════════════════════ */
        function hoverStars(el) {
            const val  = parseInt(el.dataset.val);
            const fid  = el.dataset.forum;
            const wrap = document.getElementById('forumRating_' + fid);
            wrap.querySelectorAll('.star').forEach(s => {
                s.style.color = parseInt(s.dataset.val) <= val ? '#f59e0b' : 'rgba(255,255,255,0.15)';
            });
        }

        function resetStars(fid) {
            const wrap = document.getElementById('forumRating_' + fid);
            wrap.querySelectorAll('.star').forEach(s => {
                s.style.color = s.classList.contains('filled') ? '#f59e0b' : 'rgba(255,255,255,0.15)';
            });
        }

        function rateForum(idForum, note) {
            const fd = new FormData();
            fd.append('action', 'rate_forum');
            fd.append('idForum', idForum);
            fd.append('note', note);
            fd.append('idUser', <?= (int) $frontOfficeUserId ?>);

            fetch(FORUM_POST_URL, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const wrap = document.getElementById('forumRating_' + idForum);
                    const label = document.getElementById('forumAvgLabel_' + idForum);
                    // Update filled state
                    wrap.querySelectorAll('.star').forEach(s => {
                        const sv = parseInt(s.dataset.val);
                        if (sv <= note) s.classList.add('filled');
                        else            s.classList.remove('filled');
                        s.style.color = sv <= note ? '#f59e0b' : 'rgba(255,255,255,0.15)';
                    });
                    if (label) {
                        label.textContent = data.avg + ' ★ (Note enregistrée)';
                    }
                })
                .catch(err => console.error(err));
        }

        /* ══════════════════════════════════════
           POST STAR RATING
        ══════════════════════════════════════ */
        function ratePost(idPost, note) {
            const fd = new FormData();
            fd.append('action', 'rate_post');
            fd.append('idPost', idPost);
            fd.append('note', note);

            fetch(FORUM_POST_URL, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const stars = document.querySelectorAll('#postStars_' + idPost + ' .star');
                    stars.forEach(s => {
                        const sv = parseInt(s.dataset.val);
                        if (sv <= note) s.classList.add('filled');
                        else            s.classList.remove('filled');
                    });
                    const lbl = document.getElementById('postRatingLabel_' + idPost);
                    if (lbl) lbl.textContent = note + '/5 ★';
                })
                .catch(err => console.error(err));
        }

        function checkDuplicates(form) {
            const text = form.titre ? form.titre.value : form.contenu.value;
            if (text.trim().length < 4) {
                alert('Veuillez saisir un texte plus long pour la recherche.');
                return;
            }
            
            // Interaction avec le chatbot
            const input = document.getElementById('chatInput');
            input.value = "Existe-t-il déjà un sujet sur : " + text;
            if(!document.getElementById('chatbotWindow').classList.contains('active')) toggleChat();
            sendChat();
        }

        function openReplyModal(idForum) {
            document.getElementById('reply_forum_id').value = idForum;
            if (typeof openModal === 'function') {
                openModal('modalAddPost');
            }
        }

        function toggleForumView(idForum) {
            const threadDiv = document.getElementById('forum-thread-' + idForum);
            if (threadDiv.style.display === 'none' || threadDiv.style.display === '') {
                // Smooth slide open
                threadDiv.style.display = 'block';
                threadDiv.style.opacity = '0';
                threadDiv.style.transform = 'translateY(-8px)';
                setTimeout(() => {
                    threadDiv.style.transition = 'opacity 0.3s, transform 0.3s';
                    threadDiv.style.opacity = '1';
                    threadDiv.style.transform = 'translateY(0)';
                }, 10);
            } else {
                threadDiv.style.display = 'none';
            }
        }

        function toggleInlineReply(replyId, quotedText, authorId) {
            const box = document.getElementById(replyId);
            if (!box) return;
            const isVisible = box.style.display !== 'none';

            if (isVisible) {
                box.style.display = 'none';
            } else {
                // Close any other open reply boxes (only outer containers, not sub-elements)
                document.querySelectorAll('[data-reply-box]').forEach(el => {
                    if (el.id !== replyId) el.style.display = 'none';
                });

                box.style.display = 'block';

                // Set up quote text
                const quoteDiv = document.getElementById(replyId + '-quote');
                if (quoteDiv && quotedText) {
                    quoteDiv.innerHTML = '<i class="fas fa-quote-left" style="margin-right:0.4rem;"></i><strong>User #' + authorId + ' :</strong> ' + quotedText + (quotedText.length >= 80 ? '...' : '');
                } else if (quoteDiv) {
                    quoteDiv.style.display = 'none';
                }

                // Pre-fill textarea with @mention
                const ta = document.getElementById(replyId + '-textarea');
                if (ta && authorId) {
                    ta.value = '@User#' + authorId + ' ';
                    ta.focus();
                    // Place cursor at end
                    const len = ta.value.length;
                    ta.setSelectionRange(len, len);
                }
            }
        }
    </script>
</body>
</html>
