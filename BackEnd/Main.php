<?php

require_once 'UserAuth.php';
require_once 'Register.php';
require_once 'SignUp.php';

// DEBUG: Process all POST requests for user authentication and registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // DEBUG: Database configuration settings for local development
    $dbConfig = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'vaccination'
    ];

    // DEBUG: Initialize service classes for auth, registration, and signup
    $UserAuth = new UserAuth($dbConfig);
    $Register = new UserReg($dbConfig);
    $SignUp = new UserSignUp($dbConfig);

    try {
        // DEBUG: Sanitize action parameter to prevent XSS
        $action = isset($_POST['action']) ? htmlspecialchars($_POST['action'], ENT_QUOTES, 'UTF-8') : null;
        
        switch($action) {
            // DEBUG: Handle user sign-in with email/password
            case 'signin':
                $email = isset($_POST['Email']) ? filter_var($_POST['Email'], FILTER_VALIDATE_EMAIL) : null;
                $password = isset($_POST['Pass']) ? htmlspecialchars($_POST['Pass'], ENT_QUOTES, 'UTF-8') : null;
                
                $result = $UserAuth->authenticate($email, $password);
                break;

            // DEBUG: Handle account creation with username/password
            case 'signup':
                $username = isset($_POST['Name']) ? htmlspecialchars($_POST['Name'], ENT_QUOTES, 'UTF-8') : null;
                $password = isset($_POST['Pass']) ? htmlspecialchars($_POST['Pass'], ENT_QUOTES, 'UTF-8') : null;
                $confirmPass = isset($_POST['ConPass']) ? htmlspecialchars($_POST['ConPass'], ENT_QUOTES, 'UTF-8') : null;
                
                $result = $SignUp->signUp($username, $password, $confirmPass);
                break;

            // DEBUG: Handle initial user registration with personal info
            case 'register':
                // DEBUG: Sanitize all POST data using htmlspecialchars
                $sanitizedData = array_map(function($value) {
                    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }, $_POST);

                // DEBUG: Process registration with sanitized inputs
                $result = $Register->register_PersonalInfo(
                    $sanitizedData['fname'] ?? null,
                    $sanitizedData['mname'] ?? null,
                    $sanitizedData['lname'] ?? null,
                    $sanitizedData['birthday'] ?? null,
                    filter_var($_POST['mail'] ?? '', FILTER_VALIDATE_EMAIL),
                    $sanitizedData['contact'] ?? null,
                    $sanitizedData['sex'] ?? null,
                    $sanitizedData['civilstat'] ?? null,
                    $sanitizedData['nationality'] ?? null,
                    $sanitizedData['employmentstat'] ?? null,
                    $sanitizedData['employer'] ?? null,
                    $sanitizedData['profession'] ?? null,
                    $sanitizedData['address'] ?? null,
                    $sanitizedData['barangay'] ?? null,
                    $sanitizedData['allergy_description'] ?? null,
                    $sanitizedData['disease_description'] ?? null,
                    $sanitizedData['allergy_check'] ?? null,
                    $sanitizedData['disease_check'] ?? null
                );
                break;

            // DEBUG: Handle invalid action parameter
            default:
                $result = ["success" => false, "message" => "Invalid action"];
        }

        // DEBUG: Return JSON response to client
        header('Content-Type: application/json');
        echo json_encode($result);

    } catch (Exception $e) {
        // DEBUG: Handle and return any server errors
        header('Content-Type: application/json');
        echo json_encode([
            "success" => false,
            "message" => "Server error: " . $e->getMessage()
        ]);
    } finally {
        // DEBUG: Clean up database connections
        $UserAuth->close();
        $Register->close();
        $SignUp->close();
    }
}
