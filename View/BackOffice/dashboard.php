<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BackOffice Cours</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display:flex; background-color:var(--black); margin:0; }
        .admin-sidebar { width:280px; height:100vh; background:rgba(10,10,10,0.95); border-right:1px solid var(--glass-border); position:fixed; top:0; left:0; display:flex; flex-direction:column; padding:2rem 1.5rem; z-index:100; }
        .admin-sidebar .logo { font-size:2rem; margin-bottom:3rem; text-align:center; text-decoration:none; color:inherit; }
        .admin-nav { display:flex; flex-direction:column; gap:1rem; list-style:none; padding:0; margin:0; }
        .admin-nav li a { display:flex; align-items:center; gap:1rem; color:var(--light-gray); text-decoration:none; padding:1rem 1.5rem; border-radius:12px; font-weight:500; transition:all 0.3s; }
        .admin-nav li a:hover, .admin-nav li a.active { background:rgba(234,179,8,0.1); color:var(--accent); transform:translateX(5px); box-shadow:inset 2px 0 0 var(--accent); }
        .admin-content { margin-left:280px; flex:1; padding:2.5rem 4rem; min-height:100vh; background:radial-gradient(circle at top right, rgba(234,179,8,0.05) 0%, transparent 40%); }
        .panel { padding:1.4rem; }
    </style>
</head>
<body>
<aside class="admin-sidebar">
    <a href="dashboard.php" class="logo">e-lite<span>.</span></a>
    <ul class="admin-nav">
        <li><a href="dashboard.php" class="active"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="/gestioncours/View/BackOffice/course/list.php"><i class="fas fa-book-open"></i> Liste des Cours</a></li>
        <li><a href="/gestioncours/View/BackOffice/course/add.php"><i class="fas fa-plus-circle"></i> Ajouter un Cours</a></li>
        <li><a href="/gestioncours/View/BackOffice/enrollment/list.php"><i class="fas fa-user-graduate"></i> Inscriptions</a></li>
        <li><a href="/gestioncours/View/BackOffice/support_course/list.php"><i class="fas fa-folder-open"></i> Supports</a></li>
        <li><a href="/gestioncours/View/BackOffice/certificate/list.php"><i class="fas fa-certificate"></i> Certificats</a></li>
        <li><a href="/gestioncours/View/FrontOffice/course/index.php" style="margin-top:2rem;"><i class="fas fa-external-link-alt"></i> Voir le Site</a></li>
    </ul>
</aside>
<main class="admin-content">
    <div class="glass-card panel">
        <h1>BackOffice Gestion Cours</h1>
        <p>Utilise le menu pour gérer les cours, consulter les inscriptions et les supports.</p>
    </div>
</main>
</body>
</html>