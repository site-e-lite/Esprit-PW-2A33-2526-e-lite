</main>
<?php if (isset($_SESSION['user_id'])): ?>
<aside id="sidebar" class="sidebar">
    <div class="sidebar-section">
        <h3><i class="fas fa-book"></i> Cours</h3>
        <button class="sidebar-btn" onclick="alert('Module en construction')"><i class="fas fa-play-circle"></i> Explorer</button>
    </div>
    <div class="sidebar-section">
        <h3><i class="fas fa-chalkboard-user"></i> Classes Virtuelles</h3>
        <button class="sidebar-btn" onclick="alert('Bientôt disponible')"><i class="fas fa-video"></i> Rejoindre</button>
    </div>
    <div class="sidebar-section">
        <h3><i class="fas fa-puzzle-piece"></i> Quiz</h3>
        <button class="sidebar-btn" onclick="alert('Quiz en préparation')"><i class="fas fa-tasks"></i> Commencer</button>
    </div>
    <div class="sidebar-section">
        <h3><i class="fas fa-comments"></i> Forum</h3>
        <button class="sidebar-btn" onclick="alert('Forum à venir')"><i class="fas fa-comment-dots"></i> Discuter</button>
    </div>
</aside>
<?php endif; ?>
<footer>
    <div class="footer-links">
        <a href="#">À propos</a>
        <a href="#">Confidentialité</a>
        <a href="#">Contact</a>
    </div>
    <p>© 2026 e-lite – Plateforme Éco-Digitale</p>
</footer>
<script src="/View/assets/user.js"></script>
</body>
</html>
