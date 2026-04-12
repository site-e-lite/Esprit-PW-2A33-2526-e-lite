<?php
require_once __DIR__ . '/../../Controller/ForumController.php';
require_once __DIR__ . '/../../Controller/PostController.php';

$forumController = new ForumController();
$postController = new PostController();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_forum') {
        $forum = new Forum($_POST['titre'], $_POST['description'], $_POST['idCourse']);
        $forumController->addForum($forum);
        header('Location: index.php#forum');
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] == 'add_post') {
        $idF = isset($_POST['idForum']) ? $_POST['idForum'] : 1;
        $post = new Post($_POST['contenu'], 8, $idF, ''); // Default fake User #8
        $postController->addPost($post);
        header('Location: index.php#forum');
        exit;
    }
}

$db = Config::getConnexion();
$stmtForums = $db->query("SELECT * FROM forum ORDER BY dateCreation DESC LIMIT 10");
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
    <link rel="stylesheet" href="../assets/index.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

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
            <form action="index.php" method="POST" class="glass-form form-grid" onsubmit="return validateForum(this)">
                <input type="hidden" name="action" value="add_forum">
                <div class="form-group full-width"><label>Titre</label><input type="text" name="titre" placeholder="Minimum 3 caractères"></div>
                <div class="form-group full-width"><label>Description</label><textarea name="description" placeholder="Minimum 10 caractères"></textarea></div>
                <div class="form-group"><label>ID Cours Associé</label><input type="number" name="idCourse" value="0"></div>
                <button type="submit" class="btn-primary full-width mt-3">Créer le forum</button>
            </form>
        </div>
    </div>

    <div class="modal" id="modalAddPost">
        <div class="modal-header">
            <h3><i class="fas fa-reply"></i> Ajouter un Post</h3>
            <button class="close-btn" onclick="closeModal('modalAddPost')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="index.php" method="POST" class="glass-form" onsubmit="return validatePost(this)">
                <input type="hidden" name="action" value="add_post">
                <input type="hidden" name="idForum" id="reply_forum_id">
                <div class="form-group"><label>Votre Réponse</label><textarea name="contenu" style="min-height: 120px;" placeholder="Tapez 5 caractères minimum..."></textarea></div>
                <button type="submit" class="btn-primary w-100 mt-3"><i class="fas fa-paper-plane"></i> Publier la réponse</button>
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
            <a href="#" class="logo">e-lite<span>.</span></a>
            <ul class="nav-links">
                <li><a href="#accueil">Accueil</a></li>
                <li><a href="#cours">Cours & IA</a></li>
                <li><a href="#evaluations">Évaluations</a></li>
                <li><a href="#forum">Communauté</a></li>
                <li><a href="#classes">Classes Virtuelles</a></li>
            </ul>
            <div class="auth-buttons">
                <button class="btn-icon" onclick="openModal('modalProfile')" title="Mon Profil"><i class="fas fa-user-circle"></i></button>
                <button class="btn-outline" onclick="openModal('modalRegister')">S'inscrire</button>
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

    <!-- GESTION EVALUATIONS -->
    <section id="evaluations" class="gestion-section reveal dark-bg">
        <div class="section-header">
            <h2>Évaluations & <span class="text-gradient">Quiz Adaptatifs</span></h2>
            <p>Calcul automatique des scores et adaptation du niveau via IA simple.</p>
            <button class="btn-outline mt-3" onclick="openModal('modalAddQuiz')"><i class="fas fa-plus"></i> Editer Quiz (Admin)</button>
        </div>

        <div class="quiz-container glass-card">
            <div class="quiz-header">
                <h3>Quiz : Algorithmique (Adaptatif)</h3>
                <span class="status pulse">En cours...</span>
            </div>
            <div class="question-box">
                <span class="question-type">Question Choix Multiple</span>
                <h4>Quelle structure de donnée utilise le principe LIFO ?</h4>
                <div class="options">
                    <button class="quiz-option">A. File (Queue)</button>
                    <button class="quiz-option">B. Pile (Stack)</button>
                    <button class="quiz-option">C. Arbre binaire</button>
                </div>
                <div class="ai-feedback mt-3" style="display:none;" id="quizFeedback">
                    <i class="fas fa-robot"></i> <strong>Explication IA :</strong> La bonne réponse est B. Le niveau de la prochaine question va augmenter.
                </div>
            </div>
        </div>
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
            <div class="forum-header" style="display: grid; grid-template-columns: 3fr 1fr 1fr; padding: 1rem 2rem; background: rgba(255,255,255,0.05); border-bottom: 1px solid var(--glass-border); font-weight: 600; color: var(--light-gray); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">
                <div>Forum / Catégorie</div>
                <div style="text-align: center;">Statistiques</div>
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
                <div class="forum-row" style="display: grid; grid-template-columns: 3fr 1fr 1fr; padding: 1.5rem 2rem; border-bottom: 1px solid var(--glass-border); align-items: center; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.02)';" onmouseout="this.style.background='transparent';" onclick="toggleForumView(<?= $f['idForum'] ?>)">
                    
                    <div style="display: flex; gap: 1.5rem; align-items: center;">
                        <div style="min-width: 50px; height: 50px; background: rgba(234,179,8,0.1); color: var(--accent); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0 0 0.3rem 0; font-size: 1.15rem; color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($f['titre']) ?></h3>
                            <p style="margin: 0; font-size: 0.85rem; color: var(--light-gray); line-height: 1.4;"><?= htmlspecialchars($f['description']) ?></p>
                        </div>
                    </div>

                    <div style="text-align: center; color: var(--light-gray); font-size: 0.85rem;">
                        <strong style="color: var(--text-main); font-size: 1.2rem; display: block; margin-bottom: 0.2rem;"><?= $postCount ?></strong> Messages
                    </div>

                    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 0.3rem;">
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
                        <button class="btn-primary" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;" onclick="openReplyModal(<?= $f['idForum'] ?>)"><i class="fas fa-reply"></i> Nouveau Message</button>
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
                                        <form action="index.php" method="POST" novalidate onsubmit="return validatePost(this)">
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
    <section id="classes" class="gestion-section reveal dark-bg">
        <div class="section-header">
            <h2>Classes <span class="text-gradient">Virtuelles</span></h2>
            <p>Plannification intelligente et gestion automatique des places.</p>
            <button class="btn-outline mt-3" onclick="openGenericModal('Ajouter Session', 'Création de Session et Classe Virtuelle')"><i class="fas fa-video"></i> Ajouter Session</button>
        </div>

        <div class="classes-container">
            <div class="class-card glass-card">
                <div class="class-date">
                    <span class="day">15</span>
                    <span class="month">Avril</span>
                </div>
                <div class="class-info">
                    <h3>Masterclass: Architecture Logicielle</h3>
                    <p><i class="fas fa-clock"></i> 18:00 - 20:00 | <i class="fas fa-laptop"></i> Zoom</p>
                </div>
                <div class="class-action">
                    <span class="capacity">45/50 places</span>
                    <button class="btn-primary">Rejoindre</button>
                </div>
            </div>

            <div class="class-card glass-card full">
                <div class="class-date">
                    <span class="day">18</span>
                    <span class="month">Avril</span>
                </div>
                <div class="class-info">
                    <h3>Q&A Conception UML</h3>
                    <p><i class="fas fa-clock"></i> 10:00 - 11:30 | <i class="fas fa-laptop"></i> Microsoft Teams</p>
                </div>
                <div class="class-action">
                    <span class="capacity full-text">Complet</span>
                    <button class="btn-outline" disabled>Place Épuisée</button>
                </div>
            </div>
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

    <script src="../assets/index.js"></script>
    <script>
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
