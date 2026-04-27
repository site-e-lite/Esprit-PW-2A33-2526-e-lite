-- ============================================================
--  Migration: Add Rating System to Forum Module
--  Run this script ONCE against the e_lite database
-- ============================================================

USE e_lite;

-- Add a rating column to the post table (1-5 stars, nullable = not yet rated)
ALTER TABLE post
    ADD COLUMN IF NOT EXISTS rating TINYINT UNSIGNED DEFAULT NULL CHECK (rating BETWEEN 1 AND 5);

-- Table for rating a forum (one rating per user per forum)
CREATE TABLE IF NOT EXISTS forum_rating (
    idRating   INT AUTO_INCREMENT PRIMARY KEY,
    idForum    INT NOT NULL,
    idUser     INT NOT NULL DEFAULT 1,          -- simplified: no FK to keep it independent
    note       TINYINT UNSIGNED NOT NULL CHECK (note BETWEEN 1 AND 5),
    dateRating DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_forum_user (idForum, idUser),
    CONSTRAINT fk_fr_forum FOREIGN KEY (idForum) REFERENCES forum(idForum) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
