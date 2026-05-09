<?php
/**
 * Entrée racine Apache — garde la bonne URL pour les assets en View/FrontOffice/
 * Usage : http://localhost/<dossier-projet>/
 */
header('Location: View/FrontOffice/index.php', true, 302);
exit;
