<?php
/**
 * Script one-shot : normalise les valeurs de niveau dans les tables quiz et question.
 * Corrige les incohérences : "debutant" → "Débutant", "intermediaire" → "Intermédiaire", etc.
 * Accéder une seule fois : http://localhost/7ashwa/.../fix_niveau.php
 */
require_once __DIR__ . '/config.php';
$db = Config::getConnexion();

echo "<h2>Fix Niveau — Normalisation</h2><ul>";

// Mapping : toutes les variantes possibles → valeur canonique
$mappings = [
    'Débutant'      => ["debutant", "débutant", "Debutant", "DEBUTANT", "DÉBUTANT", "debut", "beginner"],
    'Intermédiaire' => ["intermediaire", "intermédiaire", "Intermediaire", "INTERMEDIAIRE", "INTERMÉDIAIRE", "intermediate", "moyen", "Moyen"],
    'Avancé'        => ["avance", "avancé", "Avance", "AVANCE", "AVANCÉ", "advanced", "expert", "Expert"],
];

$tables = ['question', 'quiz'];

foreach ($tables as $table) {
    foreach ($mappings as $canonical => $variants) {
        foreach ($variants as $variant) {
            try {
                $stmt = $db->prepare("UPDATE `$table` SET niveau = ? WHERE BINARY niveau = ?");
                $stmt->execute([$canonical, $variant]);
                $count = $stmt->rowCount();
                if ($count > 0) {
                    echo "<li style='color:green'>✅ <strong>$table</strong> : '$variant' → '$canonical' ($count ligne(s))</li>";
                }
            } catch (Exception $e) {
                echo "<li style='color:orange'>⚠️ <strong>$table</strong> '$variant': " . htmlspecialchars($e->getMessage()) . "</li>";
            }
        }
    }
}

// Vérification finale
echo "</ul><h3>État actuel des niveaux :</h3><ul>";
foreach ($tables as $table) {
    try {
        $rows = $db->query("SELECT niveau, COUNT(*) as cnt FROM `$table` GROUP BY niveau ORDER BY niveau")->fetchAll();
        foreach ($rows as $r) {
            $niv = $r['niveau'] ?? '(NULL)';
            echo "<li><strong>$table</strong> — niveau = '<code>" . htmlspecialchars($niv) . "</code>' : {$r['cnt']} enregistrement(s)</li>";
        }
    } catch (Exception $e) {
        echo "<li style='color:red'>❌ Erreur lecture $table : " . htmlspecialchars($e->getMessage()) . "</li>";
    }
}

echo "</ul><p><strong>✅ Terminé.</strong> Tu peux supprimer ce fichier.</p>";
echo "<p><a href='/7ashwa/Esprit-PW-2A33-2526-e-lite-Gestion_Forum/quiz/admin/ajouter'>→ Retour à Ajouter Quiz</a></p>";
?>
