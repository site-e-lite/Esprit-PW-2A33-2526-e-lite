<?php
/**
 * Validateur partagé pour la validation serveur.
 */
class Validator
{
    private static array $errors = [];

    public static function reset(): void
    {
        self::$errors = [];
    }

    public static function addError(string $field, string $message): void
    {
        self::$errors[$field] = $message;
    }

    public static function hasErrors(): bool
    {
        return !empty(self::$errors);
    }

    public static function getErrors(): array
    {
        return self::$errors;
    }

    public static function required(string $field, $value, string $label): bool
    {
        if (trim((string)$value) === '') {
            self::addError($field, $label . ' est obligatoire.');
            return false;
        }
        return true;
    }

    public static function string(string $field, $value, string $label, ?int $min = null, ?int $max = null): bool
    {
        $value = trim((string)$value);
        $len = mb_strlen($value);

        if ($min !== null && $len < $min) {
            self::addError($field, $label . ' doit contenir au moins ' . $min . ' caractères.');
            return false;
        }

        if ($max !== null && $len > $max) {
            self::addError($field, $label . ' ne doit pas dépasser ' . $max . ' caractères.');
            return false;
        }

        return true;
    }

    public static function integer(string $field, $value, string $label, ?int $min = null, ?int $max = null): bool
    {
        if ($value === '' || $value === null || filter_var($value, FILTER_VALIDATE_INT) === false) {
            self::addError($field, $label . ' doit être un entier.');
            return false;
        }

        $intVal = (int)$value;
        if ($min !== null && $intVal < $min) {
            self::addError($field, $label . ' doit être supérieur ou égal à ' . $min . '.');
            return false;
        }
        if ($max !== null && $intVal > $max) {
            self::addError($field, $label . ' doit être inférieur ou égal à ' . $max . '.');
            return false;
        }

        return true;
    }

    public static function number(string $field, $value, string $label, ?float $min = null, ?float $max = null): bool
    {
        if ($value === '' || $value === null || !is_numeric($value)) {
            self::addError($field, $label . ' doit être numérique.');
            return false;
        }

        $numVal = (float)$value;
        if ($min !== null && $numVal < $min) {
            self::addError($field, $label . ' doit être supérieur ou égal à ' . $min . '.');
            return false;
        }
        if ($max !== null && $numVal > $max) {
            self::addError($field, $label . ' doit être inférieur ou égal à ' . $max . '.');
            return false;
        }

        return true;
    }

    public static function email(string $field, $value, string $label): bool
    {
        if (!filter_var((string)$value, FILTER_VALIDATE_EMAIL)) {
            self::addError($field, $label . ' est invalide.');
            return false;
        }
        return true;
    }

    public static function url(string $field, $value, string $label): bool
    {
        $value = trim((string)$value);
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            self::addError($field, $label . ' est invalide.');
            return false;
        }
        return true;
    }

    public static function inArray(string $field, $value, array $allowedValues, string $label): bool
    {
        if (!in_array($value, $allowedValues, true)) {
            self::addError($field, $label . ' est invalide.');
            return false;
        }
        return true;
    }

    public static function date(string $field, $value, string $label): bool
    {
        $value = trim((string)$value);
        if ($value === '') {
            return true;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            self::addError($field, $label . ' est invalide.');
            return false;
        }
        return true;
    }
}
?>