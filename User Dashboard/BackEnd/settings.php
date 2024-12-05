<?php
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$db = new PDO('mysql:host=localhost;dbname=shotsafe_data', 'username', 'password');

class SettingsManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getUserSettings() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    language_preference,
                    theme_preference,
                    time_zone
                FROM User_Settings
                WHERE user_id = :user_id
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'language_preference' => 'en',
                    'theme_preference' => 'light',
                    'time_zone' => 'UTC'
                ];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('Settings Error: ' . $e->getMessage());
            return ['error' => 'Failed to load settings'];
        }
    }

    public function updateAppearanceSettings($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO User_Settings (
                    user_id,
                    language_preference,
                    theme_preference,
                    time_zone
                ) VALUES (
                    :user_id,
                    :language_preference,
                    :theme_preference,
                    :time_zone
                ) ON DUPLICATE KEY UPDATE
                    language_preference = VALUES(language_preference),
                    theme_preference = VALUES(theme_preference),
                    time_zone = VALUES(time_zone)
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'language_preference' => $data['language_preference'],
                'theme_preference' => $data['theme_preference'],
                'time_zone' => $data['time_zone']
            ]);

            return ['success' => true, 'message' => 'Settings updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAvailableLanguages() {
        return [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'tl' => 'Tagalog'
        ];
    }

    public function getAvailableThemes() {
        return [
            'light' => 'Light Mode',
            'dark' => 'Dark Mode',
            'system' => 'System Default'
        ];
    }

    public function getTimeZones() {
        return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $settingsManager = new SettingsManager($db);
    $response = [];

    switch ($_GET['action']) {
        case 'get_settings':
            $response = $settingsManager->getUserSettings();
            break;

        case 'get_languages':
            $response = $settingsManager->getAvailableLanguages();
            break;

        case 'get_themes':
            $response = $settingsManager->getAvailableThemes();
            break;

        case 'get_timezones':
            $response = $settingsManager->getTimeZones();
            break;

        case 'update_appearance':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $settingsManager->updateAppearanceSettings($data);
            }
            break;
    }

    echo json_encode($response);
    exit;
}
?> 