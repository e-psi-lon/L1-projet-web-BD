<?php

function getDbConnection() {
    // MySQL configuration
    $host = 'localhost:3306';
    $dbname = 'web-project';
    $username = 'root';
    $password = 'root';

    try {
        // Connection to MySQL
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

        // Config of PDO attributes
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $conn;
    } catch(PDOException $e) {
        echo "Erreur de connexion : " . $e->getMessage();
        exit();
    }
}

// Bob the Builder
function toUrlName(string $name): string {
    // Transliterate accented characters to their ASCII equivalents
    if (function_exists('transliterator_transliterate')) {
        // Convert accented characters to their ASCII equivalents
        $name = transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
    } else {
        // Fallback in case the function is not available
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

function getAuthorUrl(string $urlName): string {
    return '/authors/' . $urlName;
}

function getBookUrl(string $authorUrlName, string $bookUrlTitle): string  {
    return getAuthorUrl($authorUrlName) . '/books/' . $bookUrlTitle;
}

function getChapterUrl(string $authorUrlName, string $bookUrlTitle, string $chapterNumber): string  {
    return getBookUrl($authorUrlName, $bookUrlTitle) . '/chapters/' . $chapterNumber;
}

// Text formatting
function h($text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function truncateText($text, $length = 150, $searchTerm = '') {
    $text = strip_tags($text);

    if (!empty($searchTerm)) {
        $pos = stripos($text, $searchTerm);
        if ($pos !== false) {
            $start = max(0, $pos - $length / 2);
            if ($start > 0) {
                $text = '...' . substr($text, $start);
            }
        }
    }

    if (strlen($text) > $length) {
        $text = substr($text, 0, $length) . '...';
    }

    if (!empty($searchTerm)) {
        $text = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<mark>$1</mark>', $text);
    }

    return $text;
}

// Security
function ensureLoggedIn() {
    if (!isset($_SESSION['user'])) {
        header('Location: /auth/login');
        exit();
    }
}

function ensureLoggedInError($redirectUrl = null) {
    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Vous devez être connecté pour effectuer cette action.']);
        if ($redirectUrl) {
            header('Location: ' . $redirectUrl);
        } else {
            exit();
        }
    }
}

function ensureAdmin() {
    if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
        header('Location: /index');
        exit();
    }
}

function ensureAdminError($redirectUrl = null) {
    if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        if ($redirectUrl) {
            header('Location: ' . $redirectUrl);
        } else {
            exit();
        }
    }
}

// Misc
function dbFieldToParamName(string $dbField): string {
    // Replace . with underscore
    $paramName = str_replace('.', '_', $dbField);
    $paramName = str_replace(' ', '_', $paramName);
    // Remove any leading or trailing underscores
    $paramName = trim($paramName, '_');
    // Convert to lowercase
    return strtolower($paramName);
}