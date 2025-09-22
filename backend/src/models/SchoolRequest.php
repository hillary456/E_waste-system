<?php
class SchoolRequest {
    private $conn;
    private $table_name = "school_requests";

    public $id;
    public $user_id;
    public $school_name;
    public $contact_person;
    public $email;
    public $phone;
    public $location;
    public $computer_type;
    public $quantity;
    public $justification;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create school request
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET user_id=:user_id, school_name=:school_name, contact_person=:contact_person,
                    email=:email, phone=:phone, location=:location, computer_type=:computer_type,
                    quantity=:quantity, justification=:justification, status=:status";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->school_name = htmlspecialchars(strip_tags($this->school_name));
        $this->contact_person = htmlspecialchars(strip_tags($this->contact_person));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->computer_type = htmlspecialchars(strip_tags($this->computer_type));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->justification = htmlspecialchars(strip_tags($this->justification));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":school_name", $this->school_name);
        $stmt->bindParam(":contact_person", $this->contact_person);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":computer_type", $this->computer_type);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":justification", $this->justification);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read requests by user
    public function readByUser() {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt;
    }

    // Read all requests (admin)
    public function readAll() {
        $query = "SELECT sr.*, u.name as user_name 
                FROM " . $this->table_name . " sr
                LEFT JOIN users u ON sr.user_id = u.id
                ORDER BY sr.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Update request status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " 
                SET status=:status, updated_at=NOW()
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Get request statistics
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_requests,
                    SUM(quantity) as total_computers_requested,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_requests,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_requests
                FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>