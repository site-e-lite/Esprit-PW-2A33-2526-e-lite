</main>
<?php
$_projectRoot = realpath(__DIR__ . '/../..');  // View/layout/ → 2 levels up = project root
$_docRoot     = realpath($_SERVER['DOCUMENT_ROOT']);
$_rel         = str_replace('\\', '/', substr($_projectRoot, strlen($_docRoot)));
$basePath     = rtrim($_rel, '/');
if ($basePath === '.' || $basePath === '') $basePath = '';
?>
<?php if (isset($_SESSION['user_id'])): ?>
<aside id="sidebar" class="sidebar closed">
    <div class="sidebar-section"><h3><i class="fas fa-book"></i> Cours</h3><a class="sidebar-btn" href="<?= $basePath ?>/">Explorer</a></div>
    <div class="sidebar-section"><h3><i class="fas fa-chalkboard-user"></i> Classes Virtuelles</h3><a class="sidebar-btn" href="<?= $basePath ?>/forum#classes">Rejoindre</a></div>
    <div class="sidebar-section"><h3><i class="fas fa-puzzle-piece"></i> Quiz</h3><a class="sidebar-btn" href="<?= $basePath ?>/forum#evaluations">Commencer</a></div>
    <div class="sidebar-section"><h3><i class="fas fa-comments"></i> Forum</h3><a class="sidebar-btn" href="<?= $basePath ?>/forum#forum">Discuter</a></div>
    <div class="sidebar-section language-selector"><h3><i class="fas fa-language"></i> Langue</h3><div id="google_translate_element"></div></div>
</aside>
<?php endif; ?>
<footer>
    <div class="footer-links"><a href="<?= $basePath ?>/">À propos</a><a href="<?= $basePath ?>/">Confidentialité</a><a href="<?= $basePath ?>/forum">Contact</a></div>
    <p>© 2026 e-lite – Plateforme Éco-Digitale</p>
</footer>
<script src="<?= $basePath ?>/View/assets/User/user.js"></script>
<script type="text/javascript">function googleTranslateElementInit(){new google.translate.TranslateElement({pageLanguage:"fr",includedLanguages:"en,fr,ar,es,de,it,zh-CN",layout:google.translate.TranslateElement.InlineLayout.SIMPLE},"google_translate_element");}</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
