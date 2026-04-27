-- ============================================================
--  E-LITE Database Schema
--  Drop & recreate the e_lite database from scratch
-- ============================================================

DROP DATABASE IF EXISTS e_lite;
CREATE DATABASE IF NOT EXISTS e_lite
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE e_lite;

-- --------------------------------------------------------
-- Table: user
-- --------------------------------------------------------
CREATE TABLE user (
    idUser        INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(50)  NOT NULL,
    prenom        VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    motDePasse    VARCHAR(255) NOT NULL,
    role          ENUM('etudiant', 'formateur', 'admin') NOT NULL DEFAULT 'etudiant',
    telephone     VARCHAR(20),
    dateNaissance DATE,
    photo         VARCHAR(255),
    statut        ENUM('actif', 'inactif', 'banni') DEFAULT 'actif',
    bio           TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: course
-- --------------------------------------------------------
CREATE TABLE course (
    idCourse    INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(100) NOT NULL,
    description TEXT         NOT NULL,
    niveau      ENUM('debutant', 'intermediaire', 'avance') NOT NULL,
    duree       INT          NOT NULL,
    statut      ENUM('brouillon', 'publie', 'archive') DEFAULT 'brouillon',
    langue      VARCHAR(30)  DEFAULT 'Français',
    prix        DECIMAL(10,2) DEFAULT 0.00,
    image       VARCHAR(255),
    objectifs   TEXT,
    prerequis   TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: enrollment
-- --------------------------------------------------------
CREATE TABLE enrollment (
    idEnrollment      INT AUTO_INCREMENT PRIMARY KEY,
    idUser            INT NOT NULL,
    idCourse          INT NOT NULL,
    niveauInitial     ENUM('debutant', 'intermediaire', 'avance') NOT NULL,
    objectifPersonnel TEXT NOT NULL,
    engagement        INT  NOT NULL,
    modeAcces         ENUM('gratuit', 'payant') NOT NULL,
    dateInscription   DATETIME DEFAULT CURRENT_TIMESTAMP,
    progression       INT      DEFAULT 0,
    derniereActivite  DATETIME DEFAULT NULL,
    tempsTotalPasse   INT      DEFAULT 0,
    statut            ENUM('actif', 'termine', 'abandonne') DEFAULT 'actif',
    noteFinale        DECIMAL(5,2) DEFAULT NULL,
    certificatObtenu  BOOLEAN DEFAULT FALSE,
    CONSTRAINT fk_enrollment_user   FOREIGN KEY (idUser)   REFERENCES user(idUser)     ON DELETE CASCADE,
    CONSTRAINT fk_enrollment_course FOREIGN KEY (idCourse) REFERENCES course(idCourse) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: quiz
-- --------------------------------------------------------
CREATE TABLE quiz (
    idQuiz         INT AUTO_INCREMENT PRIMARY KEY,
    titre          VARCHAR(100) NOT NULL,
    duree          INT NOT NULL,
    seuilReussite  INT NOT NULL,
    niveau         ENUM('debutant', 'intermediaire', 'avance') NOT NULL,
    statut         ENUM('actif', 'inactif') DEFAULT 'actif',
    idCourse       INT NOT NULL,
    CONSTRAINT fk_quiz_course FOREIGN KEY (idCourse) REFERENCES course(idCourse) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: question
-- --------------------------------------------------------
CREATE TABLE question (
    idQuestion  INT AUTO_INCREMENT PRIMARY KEY,
    enonce      TEXT NOT NULL,
    type        ENUM('QCM', 'vrai_faux') NOT NULL,
    choixA      VARCHAR(255),
    choixB      VARCHAR(255),
    choixC      VARCHAR(255),
    choixD      VARCHAR(255),
    bonneReponse VARCHAR(50) NOT NULL,
    note        DECIMAL(5,2) NOT NULL,
    explication TEXT,
    idQuiz      INT NOT NULL,
    CONSTRAINT fk_question_quiz FOREIGN KEY (idQuiz) REFERENCES quiz(idQuiz) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: forum
-- --------------------------------------------------------
CREATE TABLE forum (
    idForum      INT AUTO_INCREMENT PRIMARY KEY,
    titre        VARCHAR(100) NOT NULL,
    description  TEXT,
    dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    idCourse     INT NOT NULL,
    CONSTRAINT fk_forum_course FOREIGN KEY (idCourse) REFERENCES course(idCourse) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: post
-- --------------------------------------------------------
CREATE TABLE post (
    idPost       INT AUTO_INCREMENT PRIMARY KEY,
    contenu      TEXT NOT NULL,
    datePost     DATETIME DEFAULT CURRENT_TIMESTAMP,
    pieceJointe  VARCHAR(255),
    idUser       INT NOT NULL,
    idForum      INT NOT NULL,
    CONSTRAINT fk_post_user  FOREIGN KEY (idUser)  REFERENCES user(idUser)   ON DELETE CASCADE,
    CONSTRAINT fk_post_forum FOREIGN KEY (idForum) REFERENCES forum(idForum) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: virtualclass
-- --------------------------------------------------------
CREATE TABLE virtualclass (
    idClass     INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(100) NOT NULL,
    description TEXT,
    lienAcces   VARCHAR(255) NOT NULL,
    plateforme  ENUM('Zoom', 'Meet', 'Teams', 'Autre') NOT NULL,
    idCourse    INT NOT NULL,
    CONSTRAINT fk_virtualclass_course FOREIGN KEY (idCourse) REFERENCES course(idCourse) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: session
-- --------------------------------------------------------
CREATE TABLE session (
    idSession   INT AUTO_INCREMENT PRIMARY KEY,
    dateSession DATE NOT NULL,
    heureDebut  TIME NOT NULL,
    heureFin    TIME NOT NULL,
    statut      ENUM('planifiee', 'en_cours', 'terminee', 'annulee') DEFAULT 'planifiee',
    capacite    INT NOT NULL,
    idClass     INT NOT NULL,
    CONSTRAINT fk_session_class FOREIGN KEY (idClass) REFERENCES virtualclass(idClass) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
