    <?php
class Donation {
    private $conn;
    private $table_name = "donations";

    public $id;
    public $user_id;
    public $donor_name;
    public $organization;
    public $email;
    public $phone;
    public $address;
    public $computer_type;
    public $quantity;
    public $condition;
    public $pickup_date;
    public $message;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create donation
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET user_id=:user_id, donor_name=:donor_name, organization=:organization, 
                    email=:email, phone=:phone, address=:address, computer_type=:computer_type,
                    quantity=:quantity, condition_status=:condition_status, pickup_date=:pickup_date, 
                    message=:message, status=:status";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->donor_name = htmlspecialchars(strip_tags($this->donor_name));
        $this->organization = htmlspecialchars(strip_tags($this->organization));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->computer_type = htmlspecialchars(strip_tags($this->computer_type));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->condition = htmlspecialchars(strip_tags($this->condition));
        $this->pickup_date = htmlspecialchars(strip_tags($this->pickup_date));
        $this->message = htmlspecialchars(strip_tags($this->message));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":donor_name", $this->donor_name);
        $stmt->bindParam(":organization", $this->organization);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":computer_type", $this->computer_type);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":condition_status", $this->condition);
        $stmt->bindParam(":pickup_date", $this->pickup_date);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read donations by user
    public function readByUser() {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt;
    }

    // Read all donations (admin)
    public function readAll() {
        $query = "SELECT d.*, u.name as user_name 
                FROM " . $this->table_name . " d
                LEFT JOIN users u ON d.user_id = u.id
                ORDER BY d.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Update donation status
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

    // Get donation statistics
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_donations,
                    SUM(quantity) as total_computers,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_donations,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_donations
                FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>