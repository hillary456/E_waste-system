<?php
require_once '../config/database.php';
require_once '../models/ContactMessage.php';

class ContactController {
    private $db;
    private $contactMessage;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->contactMessage = new ContactMessage($this->db);
    }

    // Create new contact message
    public function create($data) {
        // Validate required fields
        $required_fields = ['name', 'email', 'subject', 'message'];
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

        // Set message properties
        $this->contactMessage->name = $data['name'];
        $this->contactMessage->email = $data['email'];
        $this->contactMessage->subject = $data['subject'];
        $this->contactMessage->message = $data['message'];
        $this->contactMessage->status = 'unread';

        if ($this->contactMessage->create()) {
            return array(
                "success" => true,
                "message" => "Thank you for your message! We will get back to you within 24 hours.",
                "message_id" => $this->contactMessage->id
            );
        }

        return array(
            "success" => false,
            "message" => "Unable to send message."
        );
    }

    // Get all messages (admin only)
    public function getAll() {
        $stmt = $this->contactMessage->readAll();
        $messages = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $messages[] = $row;
        }

        return array(
            "success" => true,
            "messages" => $messages
        );
    }

    // Update message status (admin only)
    public function updateStatus($message_id, $status) {
        $valid_statuses = ['unread', 'read', 'replied'];
        
        if (!in_array($status, $valid_statuses)) {
            return array(
                "success" => false,
                "message" => "Invalid status."
            );
        }

        $this->contactMessage->id = $message_id;
        $this->contactMessage->status = $status;

        if ($this->contactMessage->updateStatus()) {
            return array(
                "success" => true,
                "message" => "Message status updated successfully."
            );
        }

        return array(
            "success" => false,
            "message" => "Unable to update message status."
        );
    }
}
?>