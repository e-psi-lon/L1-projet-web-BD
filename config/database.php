<?php

function getDbConnection() {
    // Define your database credentials here or load from a config file
    $host = "localhost";
    $db_name = "your_database_name";
    $username = "your_username";
    $password = "your_password";

    try {
        $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        echo "Connection error: " . $e->getMessage();
        exit();
    }
}