<?php
/**
 * Branche forum : point d’entrée = View/FrontOffice/index.php
 *
 * Depuis la racine du projet :
 *   php -S localhost:8000 router.php
 *
 * Racine du site : http://localhost:8000/
 * Back-office :     http://localhost:8000/View/Forum/BackOffice/dashboard.php (etc.)
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$root = realpath(__DIR__);
$rel = ltrim(str_replace('\\', '/', $uri), '/');
$full = $rel !== '' ? realpath(__DIR__ . DIRECTORY_SEPARATOR . $rel) : false;

if (
    $uri !== '/'
    && $full !== false
    && $root !== false
    && strpos($full, $root) === 0
    && is_file($full)
) {
    return false;
}

require __DIR__ . '/View/Forum/FrontOffice/index.php';