-- =========================================================
-- TABLE VIRTUAL CLASS
-- =========================================================

CREATE TABLE IF NOT EXISTS virtualclass (
    idClass INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    description TEXT NULL,
    lienAcces VARCHAR(255) NOT NULL,
    plateforme VARCHAR(50) NOT NULL,
    capacite INT NOT NULL DEFAULT 30,
    idCourse INT NULL,
    CONSTRAINT fk_virtualclass_course
        FOREIGN KEY (idCourse) REFERENCES course(idCourse)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLE SESSION
-- =========================================================

CREATE TABLE IF NOT EXISTS session (
    idSession INT AUTO_INCREMENT PRIMARY KEY,
    dateSession DATE NOT NULL,
    heureDebut TIME NOT NULL,
    heureFin TIME NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'planifiee',
    idClass INT NOT NULL,
    CONSTRAINT fk_session_virtualclass
        FOREIGN KEY (idClass) REFERENCES virtualclass(idClass)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
USE elite_forum;

CREATE TABLE IF NOT EXISTS role (
    idRole INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL
);

INSERT INTO role (idRole, nom) VALUES (1, 'admin'), (2, 'etudiant'), (3, 'enseignant') ON DUPLICATE KEY UPDATE nom=nom;

CREATE TABLE IF NOT EXISTS user (
    idUser INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    motDePasse VARCHAR(255) NOT NULL,
    idRole INT NOT NULL,
    telephone VARCHAR(20) NULL,
    dateNaissance DATE NULL,
    photo VARCHAR(255) NULL,
    statut VARCHAR(20) DEFAULT 'actif',
    bio TEXT NULL,
    last_login DATETIME NULL,
    FOREIGN KEY (idRole) REFERENCES role(idRole)
);

CREATE TABLE IF NOT EXISTS course (
    idCourse INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT NULL,
    niveau VARCHAR(50) NULL,
    duree INT NULL,
    langue VARCHAR(50) NULL,
    prix DECIMAL(10,2) NULL,
    statut VARCHAR(20) DEFAULT 'publie'
);

CREATE TABLE IF NOT EXISTS forum (
    IdForum INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT NULL,
    dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    IdCourse INT NULL,
    FOREIGN KEY (IdCourse) REFERENCES course(idCourse) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS post (
    IdPost INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    datePost DATETIME DEFAULT CURRENT_TIMESTAMP,
    pieceJointe VARCHAR(255) NULL,
    IdUser INT NOT NULL,
    IdForum INT NOT NULL,
    FOREIGN KEY (IdUser) REFERENCES user(idUser) ON DELETE CASCADE,
    FOREIGN KEY (IdForum) REFERENCES forum(IdForum) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_rating (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idForum INT NOT NULL,
    idUser INT NOT NULL,
    note TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_forum_user (idForum, idUser),
    FOREIGN KEY (idForum) REFERENCES forum(IdForum) ON DELETE CASCADE,
    FOREIGN KEY (idUser) REFERENCES user(idUser) ON DELETE CASCADE
);

-- =========================================================
-- TABLES QUIZ / ÉVALUATION (intégration module eval)
-- =========================================================

CREATE TABLE IF NOT EXISTS quiz (
    idQuiz INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    duree INT NOT NULL DEFAULT 20,
    seuilReussite INT NOT NULL DEFAULT 60,
    niveau VARCHAR(50) NOT NULL DEFAULT 'Débutant',
    statut VARCHAR(20) NOT NULL DEFAULT 'Actif',
    idCourse INT NULL,
    CONSTRAINT fk_quiz_course
        FOREIGN KEY (idCourse) REFERENCES course(idCourse)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS question (
    idQuestion INT AUTO_INCREMENT PRIMARY KEY,
    enonce TEXT NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'QCU',
    choixA VARCHAR(500) NULL,
    choixB VARCHAR(500) NULL,
    choixC VARCHAR(500) NULL,
    choixD VARCHAR(500) NULL,
    reponses_json LONGTEXT NULL,
    bonneReponse VARCHAR(500) NOT NULL,
    note INT NOT NULL DEFAULT 1,
    explication TEXT NULL,
    idQuiz INT NULL,
    niveau VARCHAR(50) NOT NULL DEFAULT 'Débutant',
    CONSTRAINT fk_question_quiz
        FOREIGN KEY (idQuiz) REFERENCES quiz(idQuiz)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_result (
    idResult INT AUTO_INCREMENT PRIMARY KEY,
    idQuiz INT NOT NULL,
    idUser INT NULL,
    score DECIMAL(10,2) NOT NULL DEFAULT 0,
    totalPoints DECIMAL(10,2) NOT NULL DEFAULT 0,
    pourcentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    statut VARCHAR(20) NOT NULL DEFAULT 'echoue',
    tabSwitchCount INT NOT NULL DEFAULT 0,
    inactivityTime INT NOT NULL DEFAULT 0,
    fastAnswerFlag TINYINT(1) NOT NULL DEFAULT 0,
    datePassage DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_result_quiz
        FOREIGN KEY (idQuiz) REFERENCES quiz(idQuiz)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_result_user
        FOREIGN KEY (idUser) REFERENCES user(idUser)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_lock (
    idLock INT AUTO_INCREMENT PRIMARY KEY,
    idQuiz INT NOT NULL,
    idUser INT NULL,
    sessionKey VARCHAR(128) NOT NULL,
    reason VARCHAR(255) NULL,
    isLocked TINYINT(1) NOT NULL DEFAULT 1,
    lockedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    unlockedAt DATETIME NULL,
    unlockedBy VARCHAR(100) NULL,
    CONSTRAINT fk_lock_quiz
        FOREIGN KEY (idQuiz) REFERENCES quiz(idQuiz)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLE POST RATING (notation des posts)
-- =========================================================

CREATE TABLE IF NOT EXISTS post_rating (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    idPost    INT NOT NULL,
    note      TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idPost) REFERENCES post(IdPost) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
