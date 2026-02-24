<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function check_logged_in() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

function check_dealer() {
    check_logged_in();
    if ($_SESSION['user_role'] !== 'dealer') {
        header("Location: ../index.php"); // Redirect to home if not dealer
        exit;
    }
}

function check_admin() {
    check_logged_in();
    if ($_SESSION['user_role'] !== 'admin') {
        header("Location: ../index.php"); // Redirect to home if not admin
        exit;
    }
}
?>
