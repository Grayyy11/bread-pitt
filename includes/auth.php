<?php
// Starts the session 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// true if someone is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// only lets the listed roles into a page
function requireRole($roles) {
    // not logged in: go to the login page
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
    // wrong role = show an error
    if (!in_array($_SESSION['role'], $roles)) {
        http_response_code(403);
        die("Access denied. You do not have permission to open this page.");
    }
}

// saves an action in the audit_logs table
function logAction($conn, $user_id, $action, $details) {
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $details);
    $stmt->execute();
}