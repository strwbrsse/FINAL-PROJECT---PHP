<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (isset($_SESSION['name_id'])) {
    echo json_encode([
        'loggedIn' => true,
        'name_id' => $_SESSION['name_id']
    ]);
} else {
    echo json_encode([
        'loggedIn' => false,
        'redirect' => '../index.html'
    ]);
} 