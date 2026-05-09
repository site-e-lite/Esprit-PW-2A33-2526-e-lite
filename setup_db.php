<?php
/**
 * Database Setup Script
 * Run this once to initialize the database with sample data
 */

require_once __DIR__ . '/config.php';

try {
    $db = Config::getConnexion();
    
    echo "<h2>Database Setup</h2>";
    
    // 1. Create sample user if it doesn't exist
    $stmt = $db->query("SELECT COUNT(*) FROM user WHERE idUser = 8");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("
            INSERT INTO user (idUser, nom, prenom, email, motDePasse, idRole, telephone, dateNaissance, photo, statut)
            VALUES (8, 'Dupont', 'Jean', 'jean.dupont@example.com', '".md5('password123')."', 2, '+33612345678', '1995-05-15', 'https://ui-avatars.com/api/?name=Jean+Dupont', 'actif')
        ");
        echo "<p>✓ User #8 created</p>";
    } else {
        echo "<p>✓ User #8 already exists</p>";
    }
    
    // 2. Create sample course if none exist
    $stmt = $db->query("SELECT COUNT(*) FROM course");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("
            INSERT INTO course (titre, description, niveau, duree, langue, prix, statut)
            VALUES 
            ('Introduction à PHP', 'Apprenez les bases de PHP', 'debutant', 40, 'Français', 49.99, 'publie'),
            ('JavaScript Avancé', 'Maîtrisez JavaScript et ES6+', 'avance', 60, 'Français', 79.99, 'publie')
        ");
        echo "<p>✓ Sample courses created</p>";
    } else {
        $stmt = $db->query("SELECT COUNT(*) FROM course");
        echo "<p>✓ ".  $stmt->fetchColumn() ." courses already exist</p>";
    }
    
    echo "<p><strong>Database setup complete!</strong></p>";
    echo "<p><a href='View/FrontOffice/index.php'>Go to Front Office</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error during setup:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
