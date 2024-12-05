<?php
session_start();

class LogoutManager {
    public function logout() {
        try {
            // Clear all session variables
            $_SESSION = array();

            // Destroy the session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }

            // Destroy the session
            session_destroy();

            // Clear any other cookies set by the application
            $this->clearAuthCookies();

            return [
                'success' => true,
                'message' => 'Logged out successfully',
                'redirect' => 'register.html'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error during logout: ' . $e->getMessage()
            ];
        }
    }

    private function clearAuthCookies() {
        // Clear remember-me cookie if it exists
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }

        // Clear any other auth-related cookies
        if (isset($_COOKIE['user_token'])) {
            setcookie('user_token', '', time() - 3600, '/');
        }
    }
}

// Handle the logout request
if (isset($_GET['action']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $logoutManager = new LogoutManager();
    $response = $logoutManager->logout();
    
    // For AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('Location: register.html');
    }
    exit;
}

// If no action, redirect to register
header('Location: register.html');
exit;
?> 