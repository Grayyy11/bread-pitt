<?php
// Logout: ends the session
include_once "includes/db.php";
include_once "includes/auth.php";

// save the logout in the audit log
if (isLoggedIn()) {
    logAction($conn, $_SESSION['user_id'], "logout", "User logged out");
}

session_unset();
session_destroy();

header("Location: login.php");
exit;