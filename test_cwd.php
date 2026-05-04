<?php
session_start();
echo "Current working directory: " . getcwd() . "\n";
echo "Script directory: " . __DIR__ . "\n";
echo "Test path: uploads/profile_pictures/\n";
echo "Absolute test path: " . __DIR__ . "/uploads/profile_pictures/\n";
echo "Directory exists (relative): " . (is_dir('uploads/profile_pictures/') ? 'YES' : 'NO') . "\n";
echo "Directory exists (absolute): " . (is_dir(__DIR__ . '/uploads/profile_pictures/') ? 'YES' : 'NO') . "\n";
?>
