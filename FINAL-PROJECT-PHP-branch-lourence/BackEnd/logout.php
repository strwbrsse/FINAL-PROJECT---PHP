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

            // Get the redirect URL from the query parameter, default to index if not set
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../FrontEnd/index.html';
            
            // Validate the redirect URL to prevent open redirect vulnerability
            if (strpos($redirect, '../FrontEnd/') === 0) {
                header("Location: " . $redirect);
            } else {
                header("Location: ../FrontEnd/index.html");
            }
            exit();
        } catch (Exception $e) {
            // Log the error and redirect to index anyway
            error_log("Logout error: " . $e->getMessage());
            header("Location: ../FrontEnd/index.html");
            exit();
        }
    }

    private function clearAuthCookies() {
        // Clear any authentication cookies
        if (isset($_COOKIE['user_token'])) {
            setcookie('user_token', '', time() - 3600, '/');
        }
        // Add any other cookies that need to be cleared
    }
}

// Create instance and execute logout
$logoutManager = new LogoutManager();
$logoutManager->logout();
?>