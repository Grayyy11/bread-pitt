<?php
// Connects PHP to the MySQL database

// load the private settings (host, port, user, password, database)
include __DIR__ . "/config.php";

// PDO connection 
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Database connection failed.");
}

// old MySQLi connection: pages not converted yet still use it
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli("127.0.0.1", $user, $pass, $db, $port);
if ($conn->connect_error) {
    exit("Database connection failed.");
}
$conn->set_charset("utf8mb4");