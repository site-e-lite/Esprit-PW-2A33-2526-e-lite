<?php
// Explorer la structure du projet cour
$base = 'c:\\Users\\boujm\\Desktop\\boj web\\cour';

function listDir($dir, $indent = 0) {
    if (!is_dir($dir)) { echo str_repeat('  ', $indent) . "[NOT FOUND] $dir\n"; return; }
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        echo str_repeat('  ', $indent) . $item . (is_dir($path) ? '/' : '') . "\n";
        if (is_dir($path) && !in_array($item, ['.git', 'vendor', 'node_modules'])) {
            listDir($path, $indent + 1);
        }
    }
}

echo "=== STRUCTURE DU PROJET COUR ===\n";
listDir($base);
