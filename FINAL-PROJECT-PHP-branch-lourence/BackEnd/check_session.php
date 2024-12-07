<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
    'session_active' => isset($_SESSION['user_id']),
    'user_id' => $_SESSION['user_id'] ?? null,
    'csrf_token' => $_SESSION['csrf_token'] ?? null
]); 