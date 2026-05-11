<?php
/**
 * La base e_lite n'a pas de table role séparée.
 * Le rôle est un ENUM directement sur la table user : 'etudiant', 'formateur', 'admin'.
 */
class Role {
    public static function getAll(): array {
        return [
            ['idRole' => 'etudiant',  'nom' => 'etudiant'],
            ['idRole' => 'formateur', 'nom' => 'formateur'],
        ];
    }
}
