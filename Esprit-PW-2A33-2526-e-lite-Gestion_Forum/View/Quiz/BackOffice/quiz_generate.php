<?php
// Redirect to the new generate route
$__bp = rtrim(str_replace('\\', '/', substr(realpath(__DIR__ . '/../../..'), strlen(realpath($_SERVER['DOCUMENT_ROOT'])))), '/');
if ($__bp === '.' || $__bp === '') $__bp = '';
header('Location: ' . $__bp . '/quiz/admin/generer');
exit;
