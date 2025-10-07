<?php
    require_once 'session.php';
    $registration_error = null;
    $registration_success = null;
    $password_changed = null;
    $registration_username = "";
    if (isset($_SESSION['registration_error'])) {
        $registration_error = $_SESSION['registration_error'];
        unset($_SESSION['registration_error']);
    }
    if (isset($_SESSION['registration_success'])) {
        $registration_success = $_SESSION['registration_success'];
        unset($_SESSION['registration_success']);
    }
    if (isset($_SESSION['password_changed'])) {
        $password_changed = $_SESSION['password_changed'];
        unset($_SESSION['password_changed']);
    }
    if (isset($_SESSION['registration_username'])) {
        $registration_username = $_SESSION['registration_username'];
    }
?>