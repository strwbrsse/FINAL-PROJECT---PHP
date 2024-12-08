<?php
session_start();
header('Content-Type: application/json');
require_once 'DB_Operations.php';
require_once 'DB_Connect.php';

class AppointmentHandler {
    private $SQL_Operations;
    private $response;

    public function __construct($config) {
        $dbConn = new DbConn($config);
        $this->SQL_Operations = new SQL_Operations($dbConn);
        $this->response = array();
    }

    public function handleAppointment($action, $data = null) {
        switch ($action) {
            case 'create_appointment':
                $this->createAppointment($data);
                break;
            case 'get_upcoming':
                $this->getUpcomingAppointments($data);
                break;
            case 'get_past':
                $this->getPastAppointments($data);
                break;
            case 'get_next':
                $this->getNextAppointment($data);
                break;
            case 'cancel_appointment':
                $this->cancelAppointment($data);
                break;
            default:
                $this->response['success'] = false;
                $this->response['message'] = "Invalid action specified";
        }
        return $this->response;
    }

    private function createAppointment($data) {
        if (!isset($_SESSION['name_id'])) {
            $this->response['success'] = false;
            $this->response['message'] = "User not authenticated";
            return;
        }

        try {
            if (empty($data['vaccine_type']) || empty($data['appointment_date']) || empty($data['appointment_time'])) {
                throw new Exception("Missing required fields");
            }

            error_log('Creating appointment with data: ' . print_r($data, true));
            error_log('Session name_id: ' . $_SESSION['name_id']);

            $result = $this->SQL_Operations->createAppointment(
                $_SESSION['name_id'],
                $data['vaccine_type'],
                $data['appointment_date'],
                $data['appointment_time']
            );

            if ($result) {
                $this->response['success'] = true;
                $this->response['message'] = "Appointment created successfully";
            } else {
                throw new Exception("Failed to create appointment");
            }
        } catch (Exception $e) {
            error_log('Error creating appointment: ' . $e->getMessage());
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    private function getUpcomingAppointments($data) {
        if (!isset($_SESSION['name_id'])) {
            $this->response['success'] = false;
            $this->response['message'] = "User not authenticated";
            return;
        }

        try {
            $appointments = $this->SQL_Operations->getUpcomingAppointments($_SESSION['name_id']);
            $this->response['success'] = true;
            $this->response['data'] = $appointments;
        } catch (Exception $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    private function getPastAppointments($data) {
        if (!isset($_SESSION['name_id'])) {
            $this->response['success'] = false;
            $this->response['message'] = "User not authenticated";
            return;
        }

        try {
            $appointments = $this->SQL_Operations->getPastAppointments($_SESSION['name_id']);
            $this->response['success'] = true;
            $this->response['data'] = $appointments;
        } catch (Exception $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    private function getNextAppointment($data) {
        if (!isset($_SESSION['name_id'])) {
            $this->response['success'] = false;
            $this->response['message'] = "User not authenticated";
            return;
        }

        try {
            $appointment = $this->SQL_Operations->getNextAppointment($_SESSION['name_id']);
            $this->response['success'] = true;
            $this->response['data'] = $appointment;
        } catch (Exception $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    private function cancelAppointment($data) {
        if (!isset($_SESSION['name_id'])) {
            $this->response['success'] = false;
            $this->response['message'] = "User not authenticated";
            return;
        }

        try {
            if (empty($data['appointment_id'])) {
                throw new Exception("Appointment ID is required");
            }

            error_log('Attempting to cancel appointment: ' . $data['appointment_id']);

            $result = $this->SQL_Operations->updateAppointmentStatus(
                $data['appointment_id'],
                $_SESSION['name_id'],
                'cancelled'
            );

            error_log('Cancel result: ' . ($result ? 'success' : 'failed'));

            if ($result) {
                $this->response['success'] = true;
                $this->response['message'] = "Appointment cancelled successfully";
            } else {
                throw new Exception("Failed to cancel appointment");
            }
        } catch (Exception $e) {
            error_log('Cancel error: ' . $e->getMessage());
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
?>
