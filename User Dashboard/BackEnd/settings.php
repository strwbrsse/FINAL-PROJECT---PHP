<?php
session_start();

// Database connection
$db = new PDO('mysql:host=localhost;dbname=shotsafe_data', 'username', 'password');

class SettingsManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getUserSettings() {
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        // Return supported languages
        return [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'tl' => 'Tagalog'
        ];
    }

    public function getAvailableThemes() {
        // Return available themes
        return [
            'light' => 'Light Mode',
            'dark' => 'Dark Mode',
            'system' => 'System Default'
        ];
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
        // Return list of time zones
        return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
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