<?php
// dashboard.php - BackOffice Admin Panel
// Réutilise l'esthétique "Black Edition & Eco-Digital" du FrontOffice
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | e-lite BackOffice</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Inclure le même CSS esthétique -->
    <link rel="stylesheet" href="../assets/index.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Supplémentaire Spécifique au layout du Dashboard Admin -->
    <style>
        body {
            display: flex; /* Layout Sidebar + Main */
            background-color: var(--black);
            margin: 0;
            overflow-x: hidden;
        }

        /* Désactiver le header fixe du front-office s'il gène, ou le cacher */
        #front-header { display: none; }

        /* Sidebar Administrative */
        .admin-sidebar {
            width: 280px;
            height: 100vh;
            background: rgba(10, 10, 10, 0.95);
            border-right: 1px solid var(--glass-border);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding: 2rem 1.5rem;
            z-index: 100;
        }

        .admin-sidebar .logo {
            font-size: 2rem;
            margin-bottom: 3rem;
            text-align: center;
        }

        .admin-nav {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            list-style: none;
        }

        .admin-nav li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--light-gray);
            text-decoration: none;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .admin-nav li a i { font-size: 1.2rem; width: 20px; text-align: center;}

        .admin-nav li a:hover, .admin-nav li a.active {
            background: rgba(234, 179, 8, 0.1);
            color: var(--accent);
            transform: translateX(5px);
            box-shadow: inset 2px 0 0 var(--accent);
        }

        .logout-btn {
            margin-top: auto;
            color: #ef4444 !important;
        }
        .logout-btn:hover { background: rgba(239, 68, 68, 0.1) !important; box-shadow: inset 2px 0 0 #ef4444 !important;}

        /* Contenu Principal */
        .admin-content {
            margin-left: 280px; /* Largeur de la sidebar */
            flex: 1;
            padding: 2.5rem 4rem;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(234, 179, 8, 0.05) 0%, transparent 40%);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            position: relative;
            background: transparent;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            padding: 0;
            border: none;
        }

        .admin-header h1 {
            font-size: 2.5rem;
            margin: 0;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--accent);
        }

        /* Dashboard Sections */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        @media (max-width: 1100px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }

        /* Tables Admin (Intégrées dans glass-card) */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            color: var(--text-main);
        }

        .admin-table th, .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
        }

        .admin-table th {
            color: var(--light-gray);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .admin-table tbody tr {
            transition: background 0.3s;
        }

        .admin-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-active { background: rgba(16, 185, 129, 0.2); color: var(--green-eco); border: 1px solid var(--green-eco);}
        .status-pending { background: rgba(234, 179, 8, 0.2); color: var(--accent); border: 1px solid var(--accent);}
        
        .action-btn { background: none; border: none; color: var(--light-gray); cursor: pointer; transition: color 0.3s; font-size: 1.1rem; margin-right: 0.8rem;}
        .action-btn:hover { color: var(--accent); }
        .action-btn.delete:hover { color: #ef4444; }

    </style>
</head>
<body>

    <!-- OVERLAYS & MODALS (On peut réutiliser les mêmes JS/CSS pour créer des modales d'ajout ici aussi) -->
    <div class="modal-overlay" id="modalOverlay"></div>

    <!-- Modale Ajout Rapide (Exemple Réutilisé) -->
    <div class="modal" id="modalQuickAdd">
        <div class="modal-header">
            <h3><i class="fas fa-magic"></i> Action Rapide Admin</h3>
            <button class="close-btn" onclick="closeModal('modalQuickAdd')">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: var(--light-gray); margin-bottom: 1.5rem;">Ceci est un exemple de modale d'administration (ex: bannir un utilisateur, valider un cours).</p>
            <form class="glass-form">
                <div class="form-group"><label>Raison / Note</label><textarea required></textarea></div>
                <button type="button" class="btn-primary w-100 mt-3" onclick="closeModal('modalQuickAdd')">Confirmer l'exécution</button>
            </form>
        </div>
    </div>


    <!-- SIDEBAR NAVIGATION -->
    <aside class="admin-sidebar">
        <a href="#" class="logo">e-lite<span>.</span><div style="font-size:0.8rem; letter-spacing:3px; color:var(--light-gray); font-family:var(--font-main);text-transform:uppercase;">BackOffice</div></a>
        
        <ul class="admin-nav">
            <li><a href="#" class="active"><i class="fas fa-home"></i> Vue d'ensemble</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Gest. Utilisateurs</a></li>
            <li><a href="#"><i class="fas fa-book-open"></i> Gest. Cours & Inscr.</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Gest. Évaluations</a></li>
            <li><a href="forum.php"><i class="fas fa-comments"></i> Gest. Forum</a></li>
            <li><a href="#"><i class="fas fa-video"></i> Classes Virtuelles</a></li>
            
            <li><a href="../FrontOffice/index.html" style="margin-top:2rem; border: 1px dashed var(--glass-border);"><i class="fas fa-external-link-alt"></i> Voir le Site</a></li>
            <li><a href="#" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- CONTENT -->
    <main class="admin-content">
        
        <header class="admin-header reveal">
            <div>
                <h1>Bonjour, <span class="text-gradient">Administrateur</span></h1>
                <p style="color: var(--light-gray); margin-top: 0.5rem;">Voici le résumé de l'activité sur la plateforme intelligente.</p>
            </div>
            <div class="admin-profile">
                <div style="text-align: right;">
                    <strong style="display: block; color: var(--text-main);">Super Admin</strong>
                    <span style="font-size: 0.85rem; color: var(--accent);">Connecté</span>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=d4af37&color=000" alt="Admin Avatar">
            </div>
        </header>

        <!-- KPI STATS GRID (Réutilisation de stats-grid) -->
        <section class="stats-grid reveal" style="margin-top: 0;">
            <div class="stat-item glass-card" style="padding: 1.5rem;">
                <i class="fas fa-users accent-icon" style="font-size: 2rem;"></i>
                <h3 style="font-size: 2rem; margin: 0.5rem 0;">1,432</h3>
                <p style="font-size: 0.9rem;">Apprenants Inscrits</p>
            </div>
            <div class="stat-item glass-card" style="padding: 1.5rem;">
                <i class="fas fa-book accent-icon" style="font-size: 2rem;"></i>
                <h3 style="font-size: 2rem; margin: 0.5rem 0;">45</h3>
                <p style="font-size: 0.9rem;">Cours Actifs</p>
            </div>
            <div class="stat-item glass-card" style="padding: 1.5rem;">
                <i class="fas fa-brain accent-icon" style="font-size: 2rem;"></i>
                <h3 style="font-size: 2rem; margin: 0.5rem 0;">8,901</h3>
                <p style="font-size: 0.9rem;">Recommandations IA</p>
            </div>
            <div class="stat-item glass-card" style="padding: 1.5rem; background: rgba(16, 185, 129, 0.05); border-color: rgba(16,185,129,0.3);">
                <i class="fas fa-leaf" style="color: var(--green-eco); font-size: 2rem;"></i>
                <h3 style="font-size: 2rem; margin: 0.5rem 0; color: var(--green-eco);">100%</h3>
                <p style="font-size: 0.9rem; color: var(--green-eco);">Objectif Zéro Papier</p>
            </div>
        </section>

        <!-- DASHBOARD GRID : Tables & Activités -->
        <div class="dashboard-grid">
            <!-- Table des Dernières Inscriptions (Enrollments) -->
            <div class="glass-card reveal">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3><i class="fas fa-graduation-cap accent-icon"></i> Nouvelles Inscriptions (Enrollments)</h3>
                    <button class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="openModal('modalQuickAdd')"><i class="fas fa-filter"></i> Filtrer</button>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID User</th>
                            <th>Cours Complet</th>
                            <th>Format</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#U-892</td>
                            <td><strong>Data Science IA</strong><br><span style="color:var(--light-gray); font-size:0.8rem;">Obj: Dev Perso.</span></td>
                            <td>100% Digital</td>
                            <td><span class="status-badge status-active">En cours</span></td>
                            <td>
                                <button class="action-btn" title="Voir"><i class="fas fa-eye"></i></button>
                                <button class="action-btn" title="Éditer"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>#U-104</td>
                            <td><strong>React & Node.js</strong><br><span style="color:var(--light-gray); font-size:0.8rem;">Obj: Carrière</span></td>
                            <td>Hybride</td>
                            <td><span class="status-badge status-pending">En Attente</span></td>
                            <td>
                                <button class="action-btn" title="Voir"><i class="fas fa-eye"></i></button>
                                <button class="action-btn" title="Éditer"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>#U-665</td>
                            <td><strong>Arch. Logicielle</strong><br><span style="color:var(--light-gray); font-size:0.8rem;">Obj: Diplôme</span></td>
                            <td>100% Digital</td>
                            <td><span class="status-badge status-active">En cours</span></td>
                            <td>
                                <button class="action-btn" title="Voir"><i class="fas fa-eye"></i></button>
                                <button class="action-btn" title="Éditer"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Panel Activité IA & Forums -->
            <div class="glass-card reveal">
                <h3><i class="fas fa-robot accent-icon"></i> Activité IA Récente</h3>
                <p style="color:var(--light-gray); font-size:0.9rem; margin-bottom: 1.5rem;">Interventions de l'IA sur la plateforme aujourd'hui.</p>
                
                <div style="display:flex; flex-direction:column; gap:1.2rem;">
                    <div style="display:flex; gap:1rem; align-items:flex-start; padding-bottom: 1rem; border-bottom: 1px solid var(--glass-border);">
                        <div style="background: rgba(234,179,8,0.1); width: 40px; height: 40px; border-radius: 50%; display:grid; place-items:center; color: var(--accent);">
                            <i class="fas fa-reply"></i>
                        </div>
                        <div>
                            <strong style="display:block; font-size:0.95rem;">Suggestion Réponse Forum</strong>
                            <span style="font-size:0.8rem; color:var(--light-gray);">Dans "Bug Vercel 404" (G. Forum)</span>
                        </div>
                        <span style="margin-left:auto; font-size:0.8rem; color:var(--accent);">Il y a 5 min</span>
                    </div>

                    <div style="display:flex; gap:1rem; align-items:flex-start; padding-bottom: 1rem; border-bottom: 1px solid var(--glass-border);">
                        <div style="background: rgba(16,185,129,0.1); width: 40px; height: 40px; border-radius: 50%; display:grid; place-items:center; color: var(--green-eco);">
                            <i class="fas fa-magic"></i>
                        </div>
                        <div>
                            <strong style="display:block; font-size:0.95rem;">Recommandation Cours</strong>
                            <span style="font-size:0.8rem; color:var(--light-gray);">Profil "Alice" (G. Utilisateurs)</span>
                        </div>
                        <span style="margin-left:auto; font-size:0.8rem; color:var(--accent);">Il y a 1h</span>
                    </div>

                    <div style="display:flex; gap:1rem; align-items:flex-start;">
                        <div style="background: rgba(59,130,246,0.1); width: 40px; height: 40px; border-radius: 50%; display:grid; place-items:center; color: #3b82f6;">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <strong style="display:block; font-size:0.95rem;">Niveau Quiz Ajusté</strong>
                            <span style="font-size:0.8rem; color:var(--light-gray);">Quiz Algorithmique (G. Éval)</span>
                        </div>
                        <span style="margin-left:auto; font-size:0.8rem; color:var(--accent);">Il y a 2h</span>
                    </div>
                </div>

                <button class="btn-primary w-100 mt-3" style="font-size: 0.9rem;" onclick="openModal('modalQuickAdd')">Voir tous les logs système</button>
            </div>
        </div>
    </main>

    <!-- Inclusion du JS -->
    <script src="../assets/index.js"></script>
</body>
</html>
