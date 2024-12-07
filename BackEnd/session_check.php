<?php
function checkSession() {
    session_start();
    if (!isset($_SESSION['name_id'])) {
        header('Location: ../FrontEnd/index.html');
        exit();
    }
}
?> 