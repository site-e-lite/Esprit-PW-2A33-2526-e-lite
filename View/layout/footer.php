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
<?php if (!isset($_SESSION["user_id"])): ?>
<div class="footer-language">
    <div class="container" style="text-align: center; padding: 1rem 0;">
        <i class="fas fa-language"></i> <span id="google_translate_footer"></span>
    </div>
</div>
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
<script type="text/javascript">
    new google.translate.TranslateElement({
        pageLanguage: "fr",
        includedLanguages: "en,fr,ar,es,de,it,zh-CN",
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, "google_translate_element");

</script>
<script type="text/javascript">
function googleTranslateElementInit() {
    var elem = document.getElementById("google_translate_element");
    if (elem) {
        new google.translate.TranslateElement({
            pageLanguage: "fr",
            includedLanguages: "en,fr,ar,es,de,it,zh-CN",
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, "google_translate_element");
    } else {
        var footerElem = document.getElementById("google_translate_footer");
        if (footerElem) {
            new google.translate.TranslateElement({
                pageLanguage: "fr",
                includedLanguages: "en,fr,ar,es,de,it,zh-CN",
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, "google_translate_footer");
        }
    }
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
