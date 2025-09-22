<?php
require_once '../config/database.php';
require_once '../models/Donation.php';

class DonationController {
    private $db;
    private $donation;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->donation = new Donation($this->db);
    }

    // Create new donation
    public function create($data, $user_id = null) {
        // Validate required fields
        $required_fields = ['donor_name', 'email', 'phone', 'address', 'computer_type', 'quantity', 'condition'];
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

        // Set donation properties
        $this->donation->user_id = $user_id;
        $this->donation->donor_name = $data['donor_name'];
        $this->donation->organization = $data['organization'] ?? '';
        $this->donation->email = $data['email'];
        $this->donation->phone = $data['phone'];
        $this->donation->address = $data['address'];
        $this->donation->computer_type = $data['computer_type'];
        $this->donation->quantity = $data['quantity'];
        $this->donation->condition = $data['condition'];
        $this->donation->pickup_date = $data['pickup_date'] ?? null;
        $this->donation->message = $data['message'] ?? '';
        $this->donation->status = 'pending';

        if ($this->donation->create()) {
            return array(
                "success" => true,
                "message" => "Donation submitted successfully. We will contact you within 24 hours.",
                "donation_id" => $this->donation->id
            );
        }

        return array(
            "success" => false,
            "message" => "Unable to submit donation."
        );
    }

    // Get donations by user
    public function getByUser($user_id) {
        $this->donation->user_id = $user_id;
        $stmt = $this->donation->readByUser();
        $donations = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $donations[] = $row;
        }

        return array(
            "success" => true,
            "donations" => $donations
        );
    }

    // Get all donations (admin only)
    public function getAll() {
        $stmt = $this->donation->readAll();
        $donations = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $donations[] = $row;
        }

        return array(
            "success" => true,
            "donations" => $donations
        );
    }

    // Update donation status (admin only)
    public function updateStatus($donation_id, $status) {
        $valid_statuses = ['pending', 'approved', 'collected', 'processing', 'delivered', 'rejected'];
        
        if (!in_array($status, $valid_statuses)) {
            return array(
                "success" => false,
                "message" => "Invalid status."
            );
        }

        $this->donation->id = $donation_id;
        $this->donation->status = $status;

        if ($this->donation->updateStatus()) {
            return array(
                "success" => true,
                "message" => "Donation status updated successfully."
            );
        }

        return array(
            "success" => false,
            "message" => "Unable to update donation status."
        );
    }

    // Get donation statistics
    public function getStats() {
        $stats = $this->donation->getStats();
        
        return array(
            "success" => true,
            "stats" => $stats
        );
    }
}
?>