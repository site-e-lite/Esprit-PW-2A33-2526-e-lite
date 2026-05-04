<?php
require_once __DIR__ . '/../../Controller/QuizController.php';
$quizController = new QuizController();
// On charge uniquement les quiz actifs pour l'accueil public.
$quizResult = $quizController->afficherQuizsActifs();
$quizList = [];
if ($quizResult) {
    $quizList = $quizResult->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | E-Learning d'Excellence & IA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/index.css?v=20260503f">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Les modales servent ici de démo pour simuler des parcours utilisateur. -->
    <div class="modal-overlay" id="modalOverlay"></div>

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
                    <div class="form-group"><label>Email</label><input type="text" placeholder="email@exemple.com"></div>
                    <div class="form-group"><label>Mot de passe</label><input type="password"></div>
                    <div class="form-group"><label>Téléphone</label><input type="text" placeholder="+33..."></div>
                    <div class="form-group"><label>Date de Naissance</label><input type="text" placeholder="JJ/MM/AAAA"></div>
                    <div class="form-group"><label>Rôle</label>
                        <select>
                            <option value="student">Étudiant</option>
                            <option value="instructor">Formateur</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Photo de Profil (URL)</label><input type="text" placeholder="https://..."></div>
                </div>
                <button type="button" class="btn-primary w-100 mt-3" onclick="closeModal('modalRegister')"><i class="fas fa-check-circle"></i> S'inscrire (Authentification sécurisée)</button>
            </form>
        </div>
    </div>

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

    <div class="modal" id="modalAddCourse">
        <div class="modal-header">
            <h3><i class="fas fa-book-open"></i> Ajouter un Cours</h3>
            <button class="close-btn" onclick="closeModal('modalAddCourse')">&times;</button>
        </div>
        <div class="modal-body">
            <form class="glass-form form-grid">
                <div class="form-group"><label>Titre</label><input type="text"></div>
                <div class="form-group"><label>Prix (€)</label><input type="text"></div>
                <div class="form-group full-width"><label>Description</label><textarea></textarea></div>
                <div class="form-group"><label>Niveau</label>
                    <select><option>Débutant</option><option>Intermédiaire</option><option>Avancé</option></select>
                </div>
                <div class="form-group"><label>Langue</label><input type="text" value="Français"></div>
                <div class="form-group"><label>Durée (heures)</label><input type="text"></div>
                <div class="form-group"><label>Image (URL)</label><input type="text"></div>
                <button type="button" class="btn-primary full-width mt-3" onclick="closeModal('modalAddCourse')">Ajouter le cours</button>
            </form>
        </div>
    </div>

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
                    <input type="range" value="5" id="engagementRange">
                    <output id="engagementOutput">5 h/semaine</output>
                </div>
                <button type="button" class="btn-primary full-width mt-3" onclick="closeModal('modalEnrollment')"><i class="fas fa-rocket"></i> Lancer le suivi IA progressif</button>
            </form>
        </div>
    </div>

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


    <!-- Barre de navigation principale du site public. -->
    <header id="main-header">
        <nav>
            <a href="#" class="logo">e-lite<span>.</span></a>
            <ul class="nav-links">
                <li><a href="#accueil">Accueil</a></li>
                <li><a href="#cours">Cours & IA</a></li>
                <li><a href="#evaluations">Évaluations</a></li>
                <li><a href="#forum">Forum</a></li>
                <li><a href="#classes">Classes Virtuelles</a></li>
            </ul>
            <div class="auth-buttons">
                <button class="btn-icon" onclick="openModal('modalProfile')" title="Mon Profil"><i class="fas fa-user-circle"></i></button>
                <button class="btn-outline" onclick="openModal('modalRegister')">S'inscrire</button>
            </div>
        </nav>
    </header>

    <!-- Bloc d'accroche: il présente l'idée générale de la plateforme. -->
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

    <!-- Catalogue de cours affiché comme vitrine marketing. -->
    <section id="cours" class="gestion-section reveal">
        <div class="section-header">
            <h2>Nos Cours & <span class="text-gradient">Catalogue IA</span></h2>
            <p>Intelligence Artificielle : Cours recommandés pour vous selon votre niveau.</p>
        </div>

        <div class="courses-grid">
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

    <!-- Liste des quiz disponibles côté front-office. -->
    <section id="evaluations" class="gestion-section reveal dark-bg">
        <div class="section-header">
            <h2>Évaluations & <span class="text-gradient">Quiz Adaptatifs</span></h2>
            <p>Calcul automatique des scores et adaptation du niveau via IA simple.</p>
        </div>

        <div class="quiz-container glass-card">
            <?php if (!empty($quizList)): ?>
                <?php foreach ($quizList as $index => $quiz): ?>
                    <div class="quiz-header">
                        <h3><?= htmlspecialchars($quiz['titre']) ?></h3>
                        <span class="status pulse"><?= htmlspecialchars($quiz['statut']) ?></span>
                    </div>
                    <div class="question-box">
                        <div class="quiz-meta" style="display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:1rem; color:rgba(255,255,255,0.7);">
                            <span><i class="fas fa-clock"></i> <?= htmlspecialchars($quiz['duree']) ?> min</span>
                            <span><i class="fas fa-percent"></i> Seuil <?= htmlspecialchars($quiz['seuilReussite']) ?>%</span>
                            <span><i class="fas fa-layer-group"></i> Niveau <?= htmlspecialchars($quiz['niveau']) ?></span>
                        </div>
                        <a class="btn-primary" href="quiz.php?id=<?= htmlspecialchars($quiz['idQuiz']) ?>">Accéder à l’évaluation</a>
                    </div>
                    <?php if ($index !== count($quizList) - 1): ?>
                        <hr style="border-color:rgba(255,255,255,0.08); margin:2rem 0;">
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color:rgba(255,255,255,0.6);">
                    <p>Aucun quiz trouvé pour le moment. Ajoutez-en depuis le back office pour qu’ils s’affichent ici.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Sections de démonstration pour le forum et les classes virtuelles. -->
    <section id="forum" class="gestion-section reveal">
        <div class="section-header">
            <h2>Forum & <span class="text-gradient">Discussions</span></h2>
            <p>Échangez avec la communauté et consultez les sujets importants.</p>
            <button class="btn-outline mt-3" onclick="openGenericModal('Forum', 'Accédez à l’espace forum pour échanger sur les cours et les quiz.');"><i class="fas fa-comments"></i> Ouvrir le Forum</button>
        </div>

        <div class="forum-card glass-card">
            <div class="forum-summary">
                <p>Le forum est disponible comme espace de discussion. Cette version est une interface de démonstration.</p>
                <p style="color:rgba(255,255,255,0.65); font-size:0.9rem; margin-top:1rem;">Pour gérer les messages, utilisez le BackOffice.</p>
            </div>
        </div>
    </section>

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

    <script src="../assets/index.js?v=20260503c"></script>
    <script>

        function toggleInlineReply(replyId, quotedText, authorId) {
            const box = document.getElementById(replyId);
            if (!box) return;
            const isVisible = box.style.display !== 'none';

            if (isVisible) {
                box.style.display = 'none';
            } else {
                document.querySelectorAll('[data-reply-box]').forEach(el => {
                    if (el.id !== replyId) el.style.display = 'none';
                });

                box.style.display = 'block';

                const quoteDiv = document.getElementById(replyId + '-quote');
                if (quoteDiv && quotedText) {
                    quoteDiv.innerHTML = '<i class="fas fa-quote-left" style="margin-right:0.4rem;"></i><strong>User #' + authorId + ' :</strong> ' + quotedText + (quotedText.length >= 80 ? '...' : '');
                } else if (quoteDiv) {
                    quoteDiv.style.display = 'none';
                }

                const ta = document.getElementById(replyId + '-textarea');
                if (ta && authorId) {
                    ta.value = '@User#' + authorId + ' ';
                    ta.focus();
                    const len = ta.value.length;
                    ta.setSelectionRange(len, len);
                }
            }
        }
    </script>
</body>
</html>
