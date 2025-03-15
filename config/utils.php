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
    // Replace spaces with dashes
    return str_replace(' ', '-', $name);
}

function fromUrlName(string $url_name): string {
    // Replace dashes with spaces
    return str_replace('-', ' ', $url_name);
}