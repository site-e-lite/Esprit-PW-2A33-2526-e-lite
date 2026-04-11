-- Création de la base
CREATE DATABASE IF NOT EXISTS `e_lite` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `e_lite`;

-- Table User
CREATE TABLE `user` (
  `idUser` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `motDePasse` varchar(255) NOT NULL,
  `role` enum('etudiant','formateur','admin') DEFAULT 'etudiant',
  `telephone` varchar(20) DEFAULT NULL,
  `dateNaissance` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `statut` enum('actif','inactif','banni') DEFAULT 'actif',
  `bio` text DEFAULT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Course
CREATE TABLE `course` (
  `idCourse` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `niveau` enum('débutant','intermédiaire','avancé') NOT NULL,
  `duree` int(11) NOT NULL COMMENT 'en heures',
  `statut` enum('brouillon','publié','archivé') DEFAULT 'brouillon',
  `langue` varchar(30) DEFAULT 'Français',
  `prix` decimal(10,2) DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `objectifs` text DEFAULT NULL,
  `prerequis` text DEFAULT NULL,
  PRIMARY KEY (`idCourse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Enrollment (inscription)
CREATE TABLE `enrollment` (
  `idEnrollment` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `idCourse` int(11) NOT NULL,
  `niveauInitial` enum('débutant','intermédiaire','avancé') NOT NULL,
  `objectifPersonnel` text NOT NULL,
  `engagement` int(11) NOT NULL COMMENT 'heures/semaine',
  `modeAcces` enum('gratuit','payant') NOT NULL,
  `dateInscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `progression` tinyint DEFAULT 0 COMMENT 'pourcentage',
  `derniereActivite` datetime DEFAULT NULL,
  `tempsTotalPasse` int DEFAULT 0 COMMENT 'secondes',
  `statut` enum('actif','terminé','abandonné') DEFAULT 'actif',
  `noteFinale` decimal(5,2) DEFAULT NULL,
  `certificatObtenu` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`idEnrollment`),
  FOREIGN KEY (`idUser`) REFERENCES `user`(`idUser`) ON DELETE CASCADE,
  FOREIGN KEY (`idCourse`) REFERENCES `course`(`idCourse`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Quiz
CREATE TABLE `quiz` (
  `idQuiz` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) NOT NULL,
  `duree` int(11) NOT NULL COMMENT 'minutes',
  `seuilReussite` tinyint NOT NULL COMMENT '%',
  `niveau` enum('débutant','intermédiaire','avancé') NOT NULL,
  `statut` enum('brouillon','publié') DEFAULT 'brouillon',
  `idCourse` int(11) NOT NULL,
  PRIMARY KEY (`idQuiz`),
  FOREIGN KEY (`idCourse`) REFERENCES `course`(`idCourse`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Question
CREATE TABLE `question` (
  `idQuestion` int(11) NOT NULL AUTO_INCREMENT,
  `enonce` text NOT NULL,
  `type` enum('QCM','vrai_faux') NOT NULL,
  `choixA` varchar(255) DEFAULT NULL,
  `choixB` varchar(255) DEFAULT NULL,
  `choixC` varchar(255) DEFAULT NULL,
  `choixD` varchar(255) DEFAULT NULL,
  `bonneReponse` varchar(10) NOT NULL COMMENT 'A,B,C,D ou vrai/faux',
  `note` decimal(5,2) NOT NULL,
  `explication` text DEFAULT NULL,
  `idQuiz` int(11) NOT NULL,
  PRIMARY KEY (`idQuestion`),
  FOREIGN KEY (`idQuiz`) REFERENCES `quiz`(`idQuiz`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Forum
CREATE TABLE `forum` (
  `idForum` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `dateCreation` datetime DEFAULT CURRENT_TIMESTAMP,
  `idCourse` int(11) NOT NULL,
  PRIMARY KEY (`idForum`),
  FOREIGN KEY (`idCourse`) REFERENCES `course`(`idCourse`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Post
CREATE TABLE `post` (
  `idPost` int(11) NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `datePost` datetime DEFAULT CURRENT_TIMESTAMP,
  `pieceJointe` varchar(255) DEFAULT NULL,
  `idUser` int(11) NOT NULL,
  `idForum` int(11) NOT NULL,
  PRIMARY KEY (`idPost`),
  FOREIGN KEY (`idUser`) REFERENCES `user`(`idUser`) ON DELETE CASCADE,
  FOREIGN KEY (`idForum`) REFERENCES `forum`(`idForum`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table VirtualClass
CREATE TABLE `virtualclass` (
  `idClass` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `lienAcces` varchar(255) NOT NULL,
  `plateforme` enum('Zoom','Meet','Teams','Autre') NOT NULL,
  `idCourse` int(11) NOT NULL,
  PRIMARY KEY (`idClass`),
  FOREIGN KEY (`idCourse`) REFERENCES `course`(`idCourse`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Session
CREATE TABLE `session` (
  `idSession` int(11) NOT NULL AUTO_INCREMENT,
  `dateSession` date NOT NULL,
  `heureDebut` time NOT NULL,
  `heureFin` time NOT NULL,
  `statut` enum('planifiée','en cours','terminée','annulée') DEFAULT 'planifiée',
  `capacite` int(11) NOT NULL,
  `idClass` int(11) NOT NULL,
  PRIMARY KEY (`idSession`),
  FOREIGN KEY (`idClass`) REFERENCES `virtualclass`(`idClass`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;