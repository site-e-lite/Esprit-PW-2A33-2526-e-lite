CREATE DATABASE IF NOT EXISTS elite_forum;
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

-- Teacher-Course Relationship Table
CREATE TABLE IF NOT EXISTS teacher_course (
    idTeacherCourse INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL,
    idCourse INT NOT NULL,
    dateAssigned DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idUser) REFERENCES user(idUser) ON DELETE CASCADE,
    FOREIGN KEY (idCourse) REFERENCES course(idCourse) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_course (idUser, idCourse),
    INDEX idx_teacher (idUser),
    INDEX idx_course (idCourse)
);

-- Forum Rating Table
CREATE TABLE IF NOT EXISTS forum_rating (
    idRating INT AUTO_INCREMENT PRIMARY KEY,
    IdForum INT NOT NULL,
    IdUser INT NOT NULL,
    note INT NOT NULL CHECK (note >= 1 AND note <= 5),
    dateRating DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (IdForum) REFERENCES forum(IdForum) ON DELETE CASCADE,
    FOREIGN KEY (IdUser) REFERENCES user(idUser) ON DELETE CASCADE,
    UNIQUE KEY unique_forum_rating (IdForum, IdUser),
    INDEX idx_forum_rating (IdForum)
);

-- Enrollment Table (if not already created separately)
CREATE TABLE IF NOT EXISTS enrollment (
    idEnrollment INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL,
    idCourse INT NOT NULL,
    niveauInitial VARCHAR(50) NOT NULL,
    objectifPersonnel TEXT NULL,
    engagement INT DEFAULT 5,
    modeAcces VARCHAR(50) DEFAULT 'standard',
    dateInscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    progression INT DEFAULT 0,
    derniereActivite DATETIME NULL,
    tempsTotalPasse INT DEFAULT 0,
    statut VARCHAR(20) DEFAULT 'actif',
    noteFinale DECIMAL(5,2) NULL,
    certificatObtenu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (idUser) REFERENCES user(idUser) ON DELETE CASCADE,
    FOREIGN KEY (idCourse) REFERENCES course(idCourse) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (idUser, idCourse),
    INDEX idx_student (idUser),
    INDEX idx_course_enrollment (idCourse),
    INDEX idx_statut (statut)
);
