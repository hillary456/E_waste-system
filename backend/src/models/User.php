<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $user_type;
    public $organization;
    public $phone;
    public $location;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user
    public function create() {
    $query = "INSERT INTO " . $this->table_name . " 
            SET name=:name, email=:email, password=:password, user_type=:user_type, 
                organization=:organization, phone=:phone, location=:location";

    $stmt = $this->conn->prepare($query);

    // Sanitize
    $this->name = htmlspecialchars(strip_tags($this->name));
    $this->email = htmlspecialchars(strip_tags($this->email)); 
    $this->user_type = htmlspecialchars(strip_tags($this->user_type));
    $this->organization = htmlspecialchars(strip_tags($this->organization));
    $this->phone = htmlspecialchars(strip_tags($this->phone));
    $this->location = htmlspecialchars(strip_tags($this->location));

    
    $stmt->bindParam(":name", $this->name);
    $stmt->bindParam(":email", $this->email);
    $stmt->bindParam(":password", $this->password);  
    $stmt->bindParam(":user_type", $this->user_type);
    $stmt->bindParam(":organization", $this->organization);
    $stmt->bindParam(":phone", $this->phone);
    $stmt->bindParam(":location", $this->location);

    if ($stmt->execute()) {
        $this->id = $this->conn->lastInsertId();
        return true;
    }

    return false;
}


    // Read user by email
    public function readByEmail() {
        $query = "SELECT id, name, email, password, user_type, organization, phone, location, created_at 
                FROM " . $this->table_name . " 
                WHERE email = :email 
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->user_type = $row['user_type'];
            $this->organization = $row['organization'];
            $this->phone = $row['phone'];
            $this->location = $row['location'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    // Read user by ID
    public function readById() {
        $query = "SELECT id, name, email, user_type, organization, phone, location, created_at 
                FROM " . $this->table_name . " 
                WHERE id = :id 
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->user_type = $row['user_type'];
            $this->organization = $row['organization'];
            $this->phone = $row['phone'];
            $this->location = $row['location'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    // Update user
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET name=:name, organization=:organization, phone=:phone, location=:location, updated_at=NOW()
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->organization = htmlspecialchars(strip_tags($this->organization));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":organization", $this->organization);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>