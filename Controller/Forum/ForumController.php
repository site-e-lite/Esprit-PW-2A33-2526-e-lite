<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/Forum/Forum.php';

class ForumController {

    // ─────────────────────────────────────────────
    //  CRUD OPERATIONS
    // ─────────────────────────────────────────────

    /** Afficher tous les forums (avec filtrage optionnel + rating réel) */
    public function afficherForums($filters = []) {
        $db     = Config::getConnexion();
        $where  = [];
        $params = [];

        // Filter: keyword (title or description)
        if (!empty($filters['search'])) {
            $where[]          = '(f.titre LIKE :search OR f.description LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        // Filter: course id
        if (!empty($filters['idCourse'])) {
            $where[]            = 'f.idCourse = :idCourse';
            $params['idCourse'] = intval($filters['idCourse']);
        }
        // Filter: date from
        if (!empty($filters['dateFrom'])) {
            $where[]            = 'DATE(f.dateCreation) >= :dateFrom';
            $params['dateFrom'] = $filters['dateFrom'];
        }
        // Filter: date to
        if (!empty($filters['dateTo'])) {
            $where[]          = 'DATE(f.dateCreation) <= :dateTo';
            $params['dateTo'] = $filters['dateTo'];
        }
        // Filter: minimum average rating
        if (!empty($filters['minRating'])) {
            $where[]             = 'COALESCE(AVG(fr.note), 0) >= :minRating';
            $params['minRating'] = floatval($filters['minRating']);
        }

        $sql = "SELECT f.*,
                       COUNT(DISTINCT p.idPost)     AS postCount,
                       ROUND(AVG(fr.note), 1)       AS avgRating,
                       COUNT(DISTINCT fr.id)        AS ratingCount
                FROM   forum f
                LEFT JOIN post         p  ON p.idForum  = f.idForum
                LEFT JOIN forum_rating fr ON fr.idForum = f.idForum"
             . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '')
             . " GROUP BY f.idForum
                ORDER BY f.dateCreation DESC";

        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query;
        } catch (Exception $e) {
            // Fallback sans forum_rating si la table n'existe pas encore
            $sqlFallback = "SELECT f.*,
                               COUNT(DISTINCT p.idPost) AS postCount,
                               NULL AS avgRating,
                               0    AS ratingCount
                            FROM forum f
                            LEFT JOIN post p ON p.idForum = f.idForum"
                         . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '')
                         . " GROUP BY f.idForum
                            ORDER BY f.dateCreation DESC";
            try {
                $q2 = $db->prepare($sqlFallback);
                $q2->execute($params);
                return $q2;
            } catch (Exception $e2) {
                die('Erreur: ' . $e2->getMessage());
            }
        }
    }

    /**
     * idCourse obligatoire (FK vers course). 0 ou invalide → premier cours disponible.
     */
    private function resolveForumCourseId($rawIdCourse): ?int {
        $id = (int) $rawIdCourse;
        $db = Config::getConnexion();
        if ($id > 0) {
            $s = $db->prepare('SELECT idCourse FROM course WHERE idCourse = ?');
            $s->execute([$id]);
            if ($s->fetchColumn()) {
                return $id;
            }
        }
        $fallback = $db->query('SELECT idCourse FROM course ORDER BY idCourse ASC LIMIT 1')->fetchColumn();
        return $fallback ? (int) $fallback : null;
    }

    /** Ajouter un forum ; retourne false si aucun cours en base ou erreur SQL */
    public function addForum($forum) {
        $idCourse = $this->resolveForumCourseId($forum->getIdCourse());
        if ($idCourse === null) {
            return false;
        }
        $sql = 'INSERT INTO forum (titre, description, idCourse)
                VALUES (:titre, :description, :idCourse)';
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'titre'       => $forum->getTitre(),
                'description' => $forum->getDescription(),
                'idCourse'    => $idCourse,
            ]);
        } catch (Exception $e) {
            error_log('addForum: ' . $e->getMessage());
            return false;
        }
    }

    /** Modifier un forum */
    public function updateForum($forum, $id) {
        try {
            $db    = Config::getConnexion();
            $query = $db->prepare(
                'UPDATE forum SET
                    titre       = :titre,
                    description = :description,
                    idCourse    = :idCourse
                 WHERE idForum  = :idForum'
            );
            $resolved = $this->resolveForumCourseId($forum->getIdCourse());
            if ($resolved === null) {
                throw new PDOException('Aucun cours valide pour ce forum.');
            }
            $query->execute([
                'titre'       => $forum->getTitre(),
                'description' => $forum->getDescription(),
                'idCourse'    => $resolved,
                'idForum'     => $id
            ]);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /** Supprimer un forum */
    public function deleteForum($id) {
        $sql = "DELETE FROM forum WHERE idForum = :id";
        $db  = Config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /** Récupérer un forum par son ID */
    public function getForumById($id) {
        $sql = "SELECT * FROM forum WHERE idForum = :id";
        $db  = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  STATISTICS
    // ─────────────────────────────────────────────

    /** Retourne les statistiques globales du module forum */
    public function getStats() {
        $db = Config::getConnexion();
        try {
            $stats = [];

            // Total forums
            $stats['totalForums'] = $db->query("SELECT COUNT(*) FROM forum")->fetchColumn();

            // Total posts
            $stats['totalPosts'] = $db->query("SELECT COUNT(*) FROM post")->fetchColumn();

            // Posts created in the last 24 hours
            $stats['posts24h'] = $db->query(
                "SELECT COUNT(*) FROM post WHERE datePost >= NOW() - INTERVAL 1 DAY"
            )->fetchColumn();

            // Average global rating (real data from forum_rating)
            try {
                $avgRow = $db->query("SELECT ROUND(AVG(note), 1) FROM forum_rating")->fetchColumn();
                $stats['avgRating'] = $avgRow ? (float) $avgRow : 0;
                $stats['totalRatings'] = $db->query("SELECT COUNT(*) FROM forum_rating")->fetchColumn();
            } catch (Exception $e) {
                $stats['avgRating']    = 0;
                $stats['totalRatings'] = 0;
            }

            // Most active forum (most posts)
            $row = $db->query(
                "SELECT f.titre, COUNT(p.idPost) AS c
                 FROM forum f
                 LEFT JOIN post p ON p.idForum = f.idForum
                 GROUP BY f.idForum
                 ORDER BY c DESC LIMIT 1"
            )->fetch();
            $stats['topForum']      = $row ? $row['titre'] : '—';
            $stats['topForumPosts'] = $row ? $row['c']     : 0;

            // Posts per forum (for chart)
            $stats['postsPerForum'] = $db->query(
                "SELECT f.titre, COUNT(p.idPost) AS c
                 FROM forum f
                 LEFT JOIN post p ON p.idForum = f.idForum
                 GROUP BY f.idForum
                 ORDER BY c DESC
                 LIMIT 8"
            )->fetchAll();

            // Rating distribution (real data)
            $stats['ratingDist'] = $this->getRatingDistribution();

            return $stats;
        } catch (Exception $e) {
            die('Erreur stats: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  RATING
    // ─────────────────────────────────────────────

    /**
     * Enregistre ou met à jour la note d'un utilisateur pour un forum.
     * Retourne la nouvelle moyenne ou null en cas d'erreur.
     */
    public function raterForum($idForum, $note, $idUser = 1) {
        $db = Config::getConnexion();
        try {
            // Créer la table si elle n'existe pas encore
            $db->exec("CREATE TABLE IF NOT EXISTS forum_rating (
                id        INT AUTO_INCREMENT PRIMARY KEY,
                idForum   INT NOT NULL,
                idUser    INT NOT NULL,
                note      TINYINT NOT NULL,
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_forum_user (idForum, idUser),
                FOREIGN KEY (idForum) REFERENCES forum(IdForum) ON DELETE CASCADE,
                FOREIGN KEY (idUser)  REFERENCES user(idUser)   ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Upsert : si l'utilisateur a déjà noté ce forum, on met à jour
            $stmt = $db->prepare(
                'INSERT INTO forum_rating (idForum, idUser, note)
                 VALUES (:idForum, :idUser, :note)
                 ON DUPLICATE KEY UPDATE note = :note2, createdAt = CURRENT_TIMESTAMP'
            );
            $stmt->execute([
                'idForum' => $idForum,
                'idUser'  => $idUser,
                'note'    => $note,
                'note2'   => $note,
            ]);

            // Recalculer la moyenne
            $avg = $db->prepare(
                'SELECT ROUND(AVG(note), 1) FROM forum_rating WHERE idForum = :idForum'
            );
            $avg->execute(['idForum' => $idForum]);
            return (float) $avg->fetchColumn();
        } catch (Exception $e) {
            error_log('raterForum: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retourne la distribution des notes (1-5) pour tous les forums.
     */
    public function getRatingDistribution(): array {
        $db   = Config::getConnexion();
        $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        try {
            $rows = $db->query(
                'SELECT note, COUNT(*) AS cnt FROM forum_rating GROUP BY note'
            )->fetchAll();
            foreach ($rows as $r) {
                $dist[(int)$r['note']] = (int)$r['cnt'];
            }
        } catch (Exception $e) {
            // table peut ne pas exister encore — retourner zéros
        }
        return $dist;
    }
}
?>
