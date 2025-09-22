<?php
require_once '../config/database.php';
require_once '../models/SchoolRequest.php';

class SchoolRequestController {
    private $db;
    private $schoolRequest;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->schoolRequest = new SchoolRequest($this->db);
    }

    // Create new school request
    public function create($data, $user_id) {
        // Validate required fields
        $required_fields = ['school_name', 'contact_person', 'email', 'phone', 'location', 'computer_type', 'quantity', 'justification'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return array(
                    "success" => false,
                    "message" => "Field '$field' is required."
                );
            }
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return array(
                "success" => false,
                "message" => "Invalid email format."
            );
        }

        // Set request properties
        $this->schoolRequest->user_id = $user_id;
        $this->schoolRequest->school_name = $data['school_name'];
        $this->schoolRequest->contact_person = $data['contact_person'];
        $this->schoolRequest->email = $data['email'];
        $this->schoolRequest->phone = $data['phone'];
        $this->schoolRequest->location = $data['location'];
        $this->schoolRequest->computer_type = $data['computer_type'];
        $this->schoolRequest->quantity = $data['quantity'];
        $this->schoolRequest->justification = $data['justification'];
        $this->schoolRequest->status = 'pending';

        if ($this->schoolRequest->create()) {
            return array(
                "success" => true,
                "message" => "School request submitted successfully. We will review your application and get back to you.",
                "request_id" => $this->schoolRequest->id
            );
        }

        return array(
            "success" => false,
            "message" => "Unable to submit school request."
        );
    }

    // Get requests by user
    public function getByUser($user_id) {
        $this->schoolRequest->user_id = $user_id;
        $stmt = $this->schoolRequest->readByUser();
        $requests = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $requests[] = $row;
        }

        return array(
            "success" => true,
            "requests" => $requests
        );
    }

    // Get all requests (admin only)
    public function getAll() {
        $stmt = $this->schoolRequest->readAll();
        $requests = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $requests[] = $row;
        }

        return array(
            "success" => true,
            "requests" => $requests
        );
    }

    // Update request status (admin only)
    public function updateStatus($request_id, $status) {
        $valid_statuses = ['pending', 'approved', 'fulfilled', 'rejected'];
        
        if (!in_array($status, $valid_statuses)) {
            return array(
                "success" => false,
                "message" => "Invalid status."
            );
        }

        $this->schoolRequest->id = $request_id;
        $this->schoolRequest->status = $status;

        if ($this->schoolRequest->updateStatus()) {
            return array(
                "success" => true,
                "message" => "Request status updated successfully."
            );
        }

        return array(
            "success" => false,
            "message" => "Unable to update request status."
        );
    }

    // Get request statistics
    public function getStats() {
        $stats = $this->schoolRequest->getStats();
        
        return array(
            "success" => true,
            "stats" => $stats
        );
    }
}
?>