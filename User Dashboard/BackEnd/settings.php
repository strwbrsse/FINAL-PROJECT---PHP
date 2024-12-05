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


if (!$db) {
    error_log('Database connection failed');
    die('Could not connect to database');
}

// Debug session
error_log('Session user_id: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));

class SettingsManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getUserSettings() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    notification_email,
                    notification_sms,
                    language_preference,
                    theme_preference,
                    reminder_frequency,
                    time_zone,
                    privacy_profile,
                    privacy_records,
                    two_factor_auth
                FROM User_Settings
                WHERE user_id = :user_id
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'notification_email' => 1,
                    'notification_sms' => 0,
                    'language_preference' => 'en',
                    'theme_preference' => 'light',
                    'reminder_frequency' => 'weekly',
                    'time_zone' => 'UTC',
                    'privacy_profile' => 'private',
                    'privacy_records' => 'private',
                    'two_factor_auth' => 0
                ];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('Settings Error: ' . $e->getMessage());
            return ['error' => 'Failed to load settings: ' . $e->getMessage()];
        }
    }

    public function updateNotificationSettings($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO User_Settings (
                    user_id,
                    notification_email,
                    notification_sms,
                    reminder_frequency
                ) VALUES (
                    :user_id,
                    :notification_email,
                    :notification_sms,
                    :reminder_frequency
                ) ON DUPLICATE KEY UPDATE
                    notification_email = VALUES(notification_email),
                    notification_sms = VALUES(notification_sms),
                    reminder_frequency = VALUES(reminder_frequency)
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'notification_email' => $data['notification_email'] ? 1 : 0,
                'notification_sms' => $data['notification_sms'] ? 1 : 0,
                'reminder_frequency' => $data['reminder_frequency']
            ]);

            return ['success' => true, 'message' => 'Notification settings updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
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

            return ['success' => true, 'message' => 'Appearance settings updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updatePrivacySettings($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO User_Settings (
                    user_id,
                    privacy_profile,
                    privacy_records
                ) VALUES (
                    :user_id,
                    :privacy_profile,
                    :privacy_records
                ) ON DUPLICATE KEY UPDATE
                    privacy_profile = VALUES(privacy_profile),
                    privacy_records = VALUES(privacy_records)
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'privacy_profile' => $data['privacy_profile'],
                'privacy_records' => $data['privacy_records']
            ]);

            return ['success' => true, 'message' => 'Privacy settings updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateSecuritySettings($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO User_Settings (
                    user_id,
                    two_factor_auth
                ) VALUES (
                    :user_id,
                    :two_factor_auth
                ) ON DUPLICATE KEY UPDATE
                    two_factor_auth = VALUES(two_factor_auth)
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'two_factor_auth' => $data['two_factor_auth'] ? 1 : 0
            ]);

            return ['success' => true, 'message' => 'Security settings updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAvailableLanguages() {
        try {
            error_log('Getting available languages');
            
            $languages = [
                'en' => 'English',
                'es' => 'Español',
                'fr' => 'Français',
                'tl' => 'Tagalog'
            ];
            
            error_log('Available languages: ' . print_r($languages, true));
            return $languages;
        } catch (Exception $e) {
            error_log('Error getting languages: ' . $e->getMessage());
            return ['en' => 'English']; // Fallback to just English if there's an error
        }
    }

    public function getAvailableThemes() {
        error_log('Getting available themes');
        // Return available themes
        $themes = [
            'light' => 'Light Mode',
            'dark' => 'Dark Mode',
            'system' => 'System Default'
        ];
        error_log('Available themes: ' . print_r($themes, true));
        return $themes;
    }

    public function getReminderFrequencies() {
        // Return reminder frequency options
        return [
            'never' => 'Never',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly'
        ];
    }

    public function getPrivacyOptions() {
        // Return privacy options
        return [
            'public' => 'Public',
            'private' => 'Private',
            'friends' => 'Friends Only'
        ];
    }

    public function getTimeZones() {
        error_log('Getting timezones');
        // Return list of time zones
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        error_log('Number of timezones: ' . count($timezones));
        return $timezones;
    }

    public function exportUserData() {
        try {
            // Get user's personal information
            $stmt = $this->db->prepare("
                SELECT pi.*, h.*, v.*, us.*
                FROM Personal_Info pi
                LEFT JOIN Health h ON pi.personal_id = h.user_id
                LEFT JOIN Vaccine v ON pi.personal_id = v.user_id
                LEFT JOIN User_Settings us ON pi.personal_id = us.user_id
                WHERE pi.personal_id = :user_id
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Create JSON file
            $filename = 'user_data_' . $_SESSION['user_id'] . '_' . date('Ymd_His') . '.json';
            file_put_contents('exports/' . $filename, json_encode($userData, JSON_PRETTY_PRINT));

            return [
                'success' => true,
                'message' => 'Data exported successfully',
                'filename' => $filename
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
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

        case 'update_notifications':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $settingsManager->updateNotificationSettings($data);
            }
            break;

        case 'update_appearance':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $settingsManager->updateAppearanceSettings($data);
            }
            break;

        case 'update_privacy':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $settingsManager->updatePrivacySettings($data);
            }
            break;

        case 'update_security':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $settingsManager->updateSecuritySettings($data);
            }
            break;

        case 'get_languages':
            $response = $settingsManager->getAvailableLanguages();
            break;

        case 'get_themes':
            $response = $settingsManager->getAvailableThemes();
            break;

        case 'get_reminder_frequencies':
            $response = $settingsManager->getReminderFrequencies();
            break;

        case 'get_privacy_options':
            $response = $settingsManager->getPrivacyOptions();
            break;

        case 'get_timezones':
            $response = $settingsManager->getTimeZones();
            break;

        case 'export_data':
            $response = $settingsManager->exportUserData();
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle file downloads
if (isset($_GET['download'])) {
    $filename = $_GET['download'];
    $filepath = 'exports/' . $filename;
    
    if (file_exists($filepath)) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filepath);
        exit;
    }
}
?> 