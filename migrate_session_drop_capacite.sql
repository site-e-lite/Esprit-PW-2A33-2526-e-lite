-- À exécuter une fois sur une base déjà créée (avant la colonne existait sur `session`).
-- Si la colonne n'existe pas, ignorer l'erreur ou commenter la ligne.
ALTER TABLE session DROP COLUMN capacite;
