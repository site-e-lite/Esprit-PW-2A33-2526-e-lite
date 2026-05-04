<?php
class Validator {
    private static $errors = [];

    public static function reset() {
        // On repart toujours d'une liste d'erreurs vide avant une nouvelle validation.
        self::$errors = [];
    }

    public static function getErrors() {
        return self::$errors;
    }

    public static function hasErrors() {
        return !empty(self::$errors);
    }

    public static function addError($field, $message) {
        self::$errors[$field] = $message;
    }

    public static function required($field, $value, $label) {
        $value = trim($value ?? '');
        // Un champ vide ne peut pas passer la validation.
        if (empty($value)) {
            self::$errors[$field] = "$label est obligatoire.";
            return false;
        }
        return true;
    }

    public static function string($field, $value, $label, $minLength = null, $maxLength = null) {
        $value = trim($value ?? '');
        
        if ($minLength !== null && strlen($value) < $minLength) {
            self::$errors[$field] = "$label doit contenir au moins $minLength caractères.";
            return false;
        }
        
        if ($maxLength !== null && strlen($value) > $maxLength) {
            self::$errors[$field] = "$label ne doit pas dépasser $maxLength caractères.";
            return false;
        }
 //bhvjvkvihkv       
        return true;
    }

    public static function integer($field, $value, $label, $minValue = null, $maxValue = null) {
        // On vérifie ici que la valeur est bien un entier avant d'aller plus loin.
        if (!is_numeric($value) || intval($value) != $value) {
            self::$errors[$field] = "$label doit être un nombre entier.";
            return false;
        }
        
        $value = intval($value);
        
        if ($minValue !== null && $value < $minValue) {
            self::$errors[$field] = "$label doit être supérieur ou égal à $minValue.";
            return false;
        }
        
        if ($maxValue !== null && $value > $maxValue) {
            self::$errors[$field] = "$label doit être inférieur ou égal à $maxValue.";
            return false;
        }
        
        return true;
    }

    public static function number($field, $value, $label, $minValue = null, $maxValue = null) {
        // Cette méthode sert pour les champs numériques plus souples, comme un score.
        $value = floatval($value ?? 0);
        
        if ($minValue !== null && $value < $minValue) {
            self::$errors[$field] = "$label doit être supérieur ou égal à $minValue.";
            return false;
        }
        
        if ($maxValue !== null && $value > $maxValue) {
            self::$errors[$field] = "$label doit être inférieur ou égal à $maxValue.";
            return false;
        }
        
        return true;
    }

    public static function email($field, $value) {
        $value = trim($value ?? '');
        // On garde une validation simple côté serveur, sans faire confiance au navigateur.
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::$errors[$field] = "L'email n'est pas valide.";
            return false;
        }
        return true;
    }

    public static function url($field, $value) {
        $value = trim($value ?? '');
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            self::$errors[$field] = "L'URL n'est pas valide.";
            return false;
        }
        return true;
    }

    public static function inArray($field, $value, $array, $label) {
        if (!in_array($value, $array)) {
            self::$errors[$field] = "$label sélectionné est invalide.";
            return false;
        }
        return true;
    }

    public static function date($field, $value) {
        $value = trim($value ?? '');
        if (empty($value)) {
            return true;
        }
        if (!strtotime($value)) {
            self::$errors[$field] = "La date n'est pas valide.";
            return false;
        }
        return true;
    }
}
?>
