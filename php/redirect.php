<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: /enryi/');
    exit;
} else {
    header('Location: ../');
    exit;
}
?>