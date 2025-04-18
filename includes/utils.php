<?php

function getDbConnection() {
    // Define database path relative to this file
    $db_path = dirname(__DIR__) . '/database/database.db';

    try {
        // Use SQLite with the database file
        $conn = new PDO('sqlite:' . $db_path);

        // Set error mode and some SQLite specific attributes
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Enable foreign keys in SQLite
        $conn->exec('PRAGMA foreign_keys = ON');

        return $conn;
    } catch(PDOException $e) {
        echo "Connection error: " . $e->getMessage();
        exit();
    }
}

function toUrlName(string $name): string {
    // Transliterate accented characters to their ASCII equivalents
    if (function_exists('transliterator_transliterate')) {
        // Convert accented characters to their ASCII equivalents
        $name = transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
    } else {
        // Fallback for when intl extension is not available
        $chars = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
            'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i',
            'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o',
            'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y', 'Þ' => 'Th', 'À' => 'A', 'Á' => 'A', 'Â' => 'A',
            'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D',
            'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Ÿ' => 'Y'
        ];
        $name = strtr($name, $chars);
    }

    // Remove special characters
    $url_name = preg_replace('/[^\w\s]/u', '', $name);
    // Replace spaces with dashes
    $url_name = preg_replace('/\s+/', '-', $url_name);
    // Convert to lowercase
    $url_name = strtolower($url_name);
    // Replace multiple dashes with a single dash
    $url_name = preg_replace('/-+/', '-', $url_name);

    return trim($url_name, '-');
}