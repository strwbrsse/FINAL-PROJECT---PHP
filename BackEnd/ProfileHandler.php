<?php
require_once 'DB_Operations.php';
require_once 'DB_Connect.php';

class ProfileHandler {
    private $SQL_Operations;
    private $response;

    public function __construct($config) {
        $dbConn = new DbConn($config);
        $this->SQL_Operations = new SQL_Operations($dbConn);
        $this->response = array();
    }

    public function handleProfile($action, $data = null) {
        switch ($action) {
            case 'get_profile':
                $this->getProfile($data);
                break;
            case 'update_profile':
                $this->updateProfile($data);
                break;
            case 'delete_profile':
                $this->deleteProfile($data);
                break;
            default:
                $this->response['success'] = false;
                $this->response['message'] = "Invalid action specified";
        }
        return $this->response;
    }

    private function getProfile($data) {
        if (!isset($_SESSION['name_id'])) {
            $this->response['success'] = false;
            $this->response['message'] = "User not authenticated";
            return;
        }

        try {
            $result = $this->SQL_Operations->getProfile($_SESSION['name_id']);
            if ($result['success']) {
                $this->response['success'] = true;
                $this->response['userData'] = $result['userData'];
            } else {
                $this->response['success'] = false;
                $this->response['message'] = $result['message'];
            }
        } catch (Exception $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    private function updateProfile($data) {
        if (!isset($_SESSION['name_id'])) {
            $this->response['success'] = false;
            $this->response['message'] = "User not authenticated";
            return;
        }

        try {
            $profileData = [
                'fname' => $data['fname'] ?? null,
                'mname' => $data['mname'] ?? null,
                'lname' => $data['lname'] ?? null,
                'email' => $data['email'] ?? null,
                'contact' => $data['contact'] ?? null,
                'birthday' => $data['birthday'] ?? null,
                'sex' => $data['sex'] ?? null,
                'civilstat' => $data['civilstat'] ?? null,
                'address' => $data['address'] ?? null
            ];

            $result = $this->SQL_Operations->updateProfile($_SESSION['name_id'], $profileData);
            $this->response = $result;
        } catch (Exception $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    private function deleteProfile($data) {
        if (!isset($_SESSION['name_id'])) {
            $this->response['success'] = false;
            $this->response['message'] = "User not authenticated";
            return;
        }

        try {
            $result = $this->SQL_Operations->deleteProfile($_SESSION['name_id']);
            $this->response = $result;
            if ($result['success']) {
                session_destroy();
            }
        } catch (Exception $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    public function close() {
        if ($this->SQL_Operations) {
            $this->SQL_Operations->close();
        }
    }
} 