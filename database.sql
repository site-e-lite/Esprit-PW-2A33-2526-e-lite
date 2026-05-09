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
