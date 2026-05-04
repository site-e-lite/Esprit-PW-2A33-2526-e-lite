</main>
<?php if (isset($_SESSION['user_id'])): ?>
<aside id="sidebar" class="sidebar">
    <div class="sidebar-section"><h3><i class="fas fa-book"></i> Cours</h3><button class="sidebar-btn" onclick="alert('Module en construction')">Explorer</button></div>
    <div class="sidebar-section"><h3><i class="fas fa-chalkboard-user"></i> Classes Virtuelles</h3><button class="sidebar-btn" onclick="alert('Bientôt disponible')">Rejoindre</button></div>
    <div class="sidebar-section"><h3><i class="fas fa-puzzle-piece"></i> Quiz</h3><button class="sidebar-btn" onclick="alert('Quiz en préparation')">Commencer</button></div>
    <div class="sidebar-section"><h3><i class="fas fa-comments"></i> Forum</h3><button class="sidebar-btn" onclick="alert('Forum à venir')">Discuter</button></div>
    <div class="sidebar-section language-selector"><h3><i class="fas fa-language"></i> Langue</h3><div id="google_translate_element"></div></div>
</aside>
<?php endif; ?>
<footer>
    <div class="footer-links"><a href="#">À propos</a><a href="#">Confidentialité</a><a href="#">Contact</a></div>
    <p>© 2026 e-lite – Plateforme Éco-Digitale</p>
</footer>
<script src="/View/assets/user.js"></script>
<script type="text/javascript">function googleTranslateElementInit(){new google.translate.TranslateElement({pageLanguage:"fr",includedLanguages:"en,fr,ar,es,de,it,zh-CN",layout:google.translate.TranslateElement.InlineLayout.SIMPLE},"google_translate_element");}</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
