<?php
/**
 * Script temporaire — crée un compte admin dans e_lite.
 * SUPPRIMER ce fichier après utilisation !
 */
require_once __DIR__ . '/config.php';

$pdo = Config::getConnexion();

$email    = 'admin@e-lite.local';
$password = 'Admin1234!';
$hash     = password_hash($password, PASSWORD_DEFAULT);

try {
    // Vérifie si l'email existe déjà
    $check = $pdo->prepare("SELECT idUser, role FROM user WHERE email = ?");
    $check->execute([$email]);
    $existing = $check->fetch();

    if ($existing) {
        // Met à jour le mot de passe et le rôle si le compte existe
        $upd = $pdo->prepare("UPDATE user SET motDePasse = ?, role = 'admin', statut = 'actif' WHERE email = ?");
        $upd->execute([$hash, $email]);
        echo "<p style='color:green'>✅ Compte mis à jour.</p>";
    } else {
        // Crée le compte admin
        $ins = $pdo->prepare(
            "INSERT INTO user (nom, prenom, email, motDePasse, role, statut) VALUES (?, ?, ?, ?, 'admin', 'actif')"
        );
        $ins->execute(['Admin', 'System', $email, $hash]);
        echo "<p style='color:green'>✅ Compte admin créé.</p>";
    }

    echo "<hr>";
    echo "<strong>Email :</strong> $email<br>";
    echo "<strong>Mot de passe :</strong> $password<br>";
    echo "<hr>";
    echo "<p style='color:red'><strong>⚠️ Supprime ce fichier immédiatement après connexion !</strong></p>";
    echo "<p><a href='/7ashwa/Esprit-PW-2A33-2526-e-lite-Gestion_Forum/login'>→ Aller à la page de connexion</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
