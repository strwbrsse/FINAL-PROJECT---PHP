<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once 'UserAuth.php';
require_once 'Register.php';
require_once 'SignUp.php';
require_once 'appointments.php';
require_once 'ProfileHandler.php';

// Process requests for both POST and GET
if ($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        header('Content-Type: application/json');
        session_start();
        
        // Database configuration settings for local development
        $dbConfig = [
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'root',
            'dbname' => 'vaccination'
        ];

        // Initialize service classes
        $UserAuth = new UserAuth($dbConfig);
        $Register = new UserReg($dbConfig);
        $SignUp = new UserSignUp($dbConfig);
        $Appointment = new AppointmentHandler($dbConfig);
        
        // Get action from either POST, GET, or JSON body
        $action = null;
        $data = [];
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['action'])) {
                $action = htmlspecialchars($_POST['action'], ENT_QUOTES, 'UTF-8');
                $data = $_POST;
            } else {
                // For JSON POST requests
                $jsonData = json_decode(file_get_contents('php://input'), true);
                $action = $jsonData['action'] ?? null;
                $data = $jsonData;
            }
        } else {
            // GET requests
            $action = isset($_GET['action']) ? htmlspecialchars($_GET['action'], ENT_QUOTES, 'UTF-8') : null;
            $data = $_GET;
        }
        
        // Add this after action is set
        error_log("Received action: " . $action);
        error_log("Request method: " . $_SERVER["REQUEST_METHOD"]);
        
        // Add this right before the switch statement
        error_log('POST data: ' . print_r(file_get_contents('php://input'), true));
        error_log('Action received: ' . $action);
        error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);
        
        switch($action) {
            // Handle user sign-in with email/password
            case 'signin':
                $email = isset($_POST['Email']) ? filter_var($_POST['Email'], FILTER_VALIDATE_EMAIL) : null;
                $password = isset($_POST['Pass']) ? htmlspecialchars($_POST['Pass'], ENT_QUOTES, 'UTF-8') : null;
                $result = $UserAuth->authenticate($email, $password);
                break;

            // Handle account creation with username/password
            case 'signup':
                $username = isset($_POST['Name']) ? htmlspecialchars($_POST['Name'], ENT_QUOTES, 'UTF-8') : null;
                $password = isset($_POST['Pass']) ? htmlspecialchars($_POST['Pass'], ENT_QUOTES, 'UTF-8') : null;
                $confirmPass = isset($_POST['ConPass']) ? htmlspecialchars($_POST['ConPass'], ENT_QUOTES, 'UTF-8') : null;
                $result = $SignUp->signUp($username, $password, $confirmPass);
                break;

            // Handle initial user registration with personal info
            case 'register':
                // Sanitize all POST data using htmlspecialchars
                $sanitizedData = array_map(function($value) {
                    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }, $_POST);

                // Process registration with sanitized inputs
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

            // Handle appointment operations
            case 'create_appointment':
                if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                    throw new Exception("Invalid method for appointment creation");
                }
                $result = $Appointment->handleAppointment($action, $data);
                break;
                
            case 'get_upcoming':
            case 'get_next':
            case 'get_doses':
            case 'get_past':
                if ($_SERVER["REQUEST_METHOD"] !== "GET") {
                    throw new Exception("Invalid method for appointment retrieval");
                }
                $result = $Appointment->handleAppointment($action, $data);
                break;

            case 'cancel_appointment':
                if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                    throw new Exception("Invalid method for appointment cancellation");
                }
                $result = $Appointment->handleAppointment($action, $data);
                break;

            case 'get_profile':
            case 'update_profile':
            case 'delete_profile':
                if (!isset($_SESSION['name_id'])) {
                    $result = ["success" => false, "message" => "User not logged in"];
                    break;
                }
                $Profile = new ProfileHandler($dbConfig);
                $result = $Profile->handleProfile($action, $_POST);
                $Profile->close();
                break;

            case 'get_dashboard_data':
                if (!isset($data['name_id'])) {
                    throw new Exception('Name ID is required');
                }
                
                $nameId = htmlspecialchars($data['name_id'], ENT_QUOTES, 'UTF-8');
                
                $result = [
                    'success' => true,
                    'user' => $UserAuth->getProfile($nameId)['userData'],
                    'appointments' => $Appointment->handleAppointment('get_upcoming', ['name_id' => $nameId])['data'],
                    'vaccineHistory' => $Appointment->handleAppointment('get_past', ['name_id' => $nameId])['data']
                ];
                break;

            // Handle invalid action parameter
            default:
                error_log("Invalid action received: " . $action);
                $result = [
                    "success" => false, 
                    "message" => "Invalid action: " . $action,
                    "method" => $_SERVER["REQUEST_METHOD"]
                ];
        }

        if (!is_array($result)) {
            throw new Exception('Invalid response format');
        }
        echo json_encode($result);
        exit;

    } catch (Throwable $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Server error: " . $e->getMessage()
        ]);
        exit;
    } finally {
        // Clean up database connections
        $UserAuth->close();
        $Register->close();
        $SignUp->close();
        $Appointment->close();
    }
}
