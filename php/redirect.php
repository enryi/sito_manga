<?php
// Check if the request method is 'POST'
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Redirect to /enryi/
    header('Location: /enryi/');
    exit;
} else {
    // Redirect to one level up
    header('Location: ../');
    exit;
}
?>