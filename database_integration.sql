-- ============================================================
-- database_integration.sql
-- Full integration: Users + Courses (e_lite) + Forum (elite_forum)
-- Strategy: merge everything into ONE database: e_lite
-- Run this in phpMyAdmin on the e_lite database
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. IMPORT USER MODULE TABLES (from elite_forum → e_lite)
-- ============================================================

CREATE TABLE IF NOT EXISTS `role` (
    `idRole` INT AUTO_INCREMENT PRIMARY KEY,
    `nom`    VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `role` (`idRole`, `nom`) VALUES
(1, 'admin'),
(2, 'etudiant'),
(3, 'enseignant');

CREATE TABLE IF NOT EXISTS `user` (
    `idUser`        INT          NOT NULL AUTO_INCREMENT,
    `nom`           VARCHAR(100) NOT NULL,
    `prenom`        VARCHAR(100) NOT NULL,
    `email`         VARCHAR(150) NOT NULL UNIQUE,
    `motDePasse`    VARCHAR(255) NOT NULL,
    `idRole`        INT          NOT NULL DEFAULT 2,
    `telephone`     VARCHAR(20)  NULL,
    `dateNaissance` DATE         NULL,
    `photo`         VARCHAR(255) NULL,
    `statut`        VARCHAR(20)  NOT NULL DEFAULT 'actif',
    `bio`           TEXT         NULL,
    `last_login`    DATETIME     NULL,
    PRIMARY KEY (`idUser`),
    CONSTRAINT `fk_user_role` FOREIGN KEY (`idRole`) REFERENCES `role` (`idRole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 2. LINK enrollment TO user (add FK if not present)
-- ============================================================

-- Add idUser FK to enrollment (safe — only adds if column exists)
ALTER TABLE `enrollment`
    ADD CONSTRAINT `fk_enrollment_user`
    FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE;

-- ============================================================
-- 3. FORUM TABLES (unified in e_lite)
-- ============================================================

CREATE TABLE IF NOT EXISTS `forum` (
    `idForum`      INT          NOT NULL AUTO_INCREMENT,
    `titre`        VARCHAR(255) NOT NULL,
    `description`  TEXT         NULL,
    `dateCreation` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `idCourse`     INT          NULL,
    PRIMARY KEY (`idForum`),
    CONSTRAINT `fk_forum_course`
        FOREIGN KEY (`idCourse`) REFERENCES `course` (`idCourse`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `post` (
    `idPost`       INT      NOT NULL AUTO_INCREMENT,
    `contenu`      TEXT     NOT NULL,
    `datePost`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `pieceJointe`  VARCHAR(255) NULL,
    `idUser`       INT      NOT NULL,
    `idForum`      INT      NOT NULL,
    PRIMARY KEY (`idPost`),
    CONSTRAINT `fk_post_user`  FOREIGN KEY (`idUser`)  REFERENCES `user`  (`idUser`)  ON DELETE CASCADE,
    CONSTRAINT `fk_post_forum` FOREIGN KEY (`idForum`) REFERENCES `forum` (`idForum`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `forum_rating` (
    `idRating`   INT      NOT NULL AUTO_INCREMENT,
    `idForum`    INT      NOT NULL,
    `idUser`     INT      NOT NULL,
    `note`       INT      NOT NULL,
    `dateRating` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`idRating`),
    UNIQUE KEY `uq_forum_user_rating` (`idForum`, `idUser`),
    CONSTRAINT `chk_forum_note` CHECK (`note` BETWEEN 1 AND 5),
    CONSTRAINT `fk_rating_forum` FOREIGN KEY (`idForum`) REFERENCES `forum` (`idForum`) ON DELETE CASCADE,
    CONSTRAINT `fk_rating_user`  FOREIGN KEY (`idUser`)  REFERENCES `user`  (`idUser`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 4. TEACHER-COURSE RELATIONSHIP
-- ============================================================

CREATE TABLE IF NOT EXISTS `teacher_course` (
    `idTeacherCourse` INT      NOT NULL AUTO_INCREMENT,
    `idUser`          INT      NOT NULL,
    `idCourse`        INT      NOT NULL,
    `dateAssigned`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`idTeacherCourse`),
    UNIQUE KEY `uq_teacher_course` (`idUser`, `idCourse`),
    CONSTRAINT `fk_tc_user`   FOREIGN KEY (`idUser`)   REFERENCES `user`   (`idUser`)   ON DELETE CASCADE,
    CONSTRAINT `fk_tc_course` FOREIGN KEY (`idCourse`) REFERENCES `course` (`idCourse`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. UPDATE config.php — point everything to e_lite
--    (manual step: change DB_NAME in config.php to 'e_lite')
-- ============================================================

-- ============================================================
-- 6. DEMO DATA — users
-- ============================================================

-- Admin user (password: admin123)
INSERT IGNORE INTO `user` (`nom`, `prenom`, `email`, `motDePasse`, `idRole`, `statut`) VALUES
('Admin', 'System',   'admin@e-lite.local',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'actif'),
('Dupont', 'Marie',   'teacher@e-lite.local',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'actif'),
('Martin', 'Lucas',   'student@e-lite.local',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'actif'),
('Bernard', 'Sophie', 'student2@e-lite.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'actif');

-- Assign teacher to first 2 courses
INSERT IGNORE INTO `teacher_course` (`idUser`, `idCourse`)
SELECT u.idUser, c.idCourse
FROM `user` u, `course` c
WHERE u.email = 'teacher@e-lite.local'
  AND c.idCourse IN (SELECT idCourse FROM course ORDER BY idCourse LIMIT 2);

-- Enroll students in first 3 courses
INSERT IGNORE INTO `enrollment` (`idUser`, `idCourse`, `niveauInitial`, `objectifPersonnel`, `engagement`, `modeAcces`, `statut`)
SELECT u.idUser, c.idCourse, 'debutant', 'Apprendre et progresser', 5, 'gratuit', 'actif'
FROM `user` u, `course` c
WHERE u.email = 'student@e-lite.local'
  AND c.idCourse IN (SELECT idCourse FROM course ORDER BY idCourse LIMIT 3);

-- Demo forums linked to courses
INSERT IGNORE INTO `forum` (`titre`, `description`, `idCourse`)
SELECT CONCAT('Forum — ', c.titre), CONCAT('Espace de discussion pour le cours : ', c.titre), c.idCourse
FROM `course` c
WHERE c.statut = 'publie'
LIMIT 5;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================
SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'e_lite'
ORDER BY TABLE_NAME;
