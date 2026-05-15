<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/logger.php';

if (isset($_SESSION['visitor_logged_in']) && $_SESSION['visitor_logged_in'] === true) {
    logActivity($pdo, 'visitor', $_SESSION['visitor_name'] . " (" . $_SESSION['visitor_kelas'] . ")", 'Logged out');
    unset($_SESSION['visitor_logged_in']);
    unset($_SESSION['visitor_name']);
    unset($_SESSION['visitor_kelas']);
}

if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
    // We will assume student_name is stored in session later
    $s_name = $_SESSION['student_name'] ?? 'Unknown Student';
    logActivity($pdo, 'student', $s_name, 'Logged out');
    unset($_SESSION['student_logged_in']);
    unset($_SESSION['student_name']);
    // other student sessions...
}

header('Location: index.php');
exit;
?>
