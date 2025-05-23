<?php
    session_start();
    unset($_SESSION['logged_in']);
    session_destroy();
    header("Location: https://enryi.23hosts.com");
    exit();
?>