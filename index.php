<?php
session_start();
require_once __DIR__ . '/config/config.php'; // pour la BDD si besoin
include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section id="accueil" class="hero">
    <div class="hero-content">
        <h1>Libérez votre potentiel</h1>
        <h2>Excellence en e‑learning</h2>
        <a href="/e-lite/modules/courses/" class="btn-primary">Explorer les cours</a>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="stats-grid">
        <div class="stat-item">
            <h3>50+</h3>
            <p>Cours disponibles</p>
        </div>
        <div class="stat-item">
            <h3>10k+</h3>
            <p>Apprenants actifs</p>
        </div>
        <div class="stat-item">
            <h3>95%</h3>
            <p>Taux de réussite</p>
        </div>
        <div class="stat-item">
            <h3>24/7</h3>
            <p>Accès illimité</p>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="about-content">
        <h2>À propos d'e‑lite</h2>
        <p>Chez e‑lite, nous croyons au pouvoir transformateur de l'éducation en ligne. Notre plateforme offre une expérience d'apprentissage complète, conçue pour vous préparer à réussir dans votre carrière et au‑delà.</p>
        <p>Notre campus numérique est un hub d'excellence académique, avec des outils de pointe et une communauté dynamique de formateurs et d'apprenants. Nous nous engageons à créer un environnement inclusif où chacun peut s'épanouir.</p>
    </div>
    <div class="about-image">
        <img src="/e-lite/assets/uploads/pic/a1.jpg" alt="e-lite campus numérique">
    </div>
</section>

<!-- Academics Tabs Section -->
<section class="academics-section">
    <h2 class="section-title">Nos programmes</h2>
    
    <div class="tabs">
        <button class="tab-btn active" data-tab="undergrad">Débutant</button>
        <button class="tab-btn" data-tab="grad">Intermédiaire</button>
        <button class="tab-btn" data-tab="lifelong">Avancé</button>
    </div>
    
    <div id="undergrad" class="tab-content active">
        <div class="program-card">
            <h3>Développement Web</h3>
            <ul>
                <li>HTML, CSS, JavaScript</li>
                <li>PHP & MySQL</li>
                <li>Projets pratiques</li>
                <li>Certificat inclus</li>
            </ul>
        </div>
        <div class="program-card">
            <h3>Data Science</h3>
            <ul>
                <li>Python pour débutants</li>
                <li>Analyse de données</li>
                <li>Pandas & Matplotlib</li>
                <li>Projet final</li>
            </ul>
        </div>
        <div class="program-card">
            <h3>Marketing Digital</h3>
            <ul>
                <li>SEO & SEA</li>
                <li>Réseaux sociaux</li>
                <li>Email marketing</li>
                <li>Certification</li>
            </ul>
        </div>
    </div>
    
    <div id="grad" class="tab-content">
        <div class="program-card">
            <h3>Intelligence Artificielle</h3>
            <ul>
                <li>Machine Learning</li>
                <li>Deep Learning</li>
                <li>TensorFlow</li>
                <li>Projet IA</li>
            </ul>
        </div>
        <div class="program-card">
            <h3>Cybersécurité</h3>
            <ul>
                <li>Sécurité réseau</li>
                <li>Tests d'intrusion</li>
                <li>Cryptographie</li>
                <li>Certification</li>
            </ul>
        </div>
        <div class="program-card">
            <h3>Cloud Computing</h3>
            <ul>
                <li>AWS / Azure</li>
                <li>Docker & Kubernetes</li>
                <li>Architecture cloud</li>
                <li>Projet final</li>
            </ul>
        </div>
    </div>
    
    <div id="lifelong" class="tab-content">
        <div class="program-card">
            <h3>Leadership & Management</h3>
            <ul>
                <li>Gestion d'équipe</li>
                <li>Prise de décision</li>
                <li>Communication</li>
                <li>Certificat</li>
            </ul>
        </div>
        <div class="program-card">
            <h3>Architecture Logicielle</h3>
            <ul>
                <li>Design Patterns</li>
                <li>Microservices</li>
                <li>Domain-Driven Design</li>
                <li>Projet avancé</li>
            </ul>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>