<?php
class Ward {
    private $conn;
    private $table = 'wards';

    // Properties
    public $ward_id;
    public $ward_name;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all wards
    public function read() {
        $query = 'SELECT ward_id, ward_name, description FROM ' . $this->table . ' ORDER BY ward_name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create ward
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' SET ward_name = :ward_name, description = :description';
        $stmt = $this->conn->prepare($query);

        $this->ward_name = htmlspecialchars(strip_tags($this->ward_name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':ward_name', $this->ward_name);
        $stmt->bindParam(':description', $this->description);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Update ward
    public function update() {
        $query = 'UPDATE ' . $this->table . '
            SET
                ward_name = :ward_name,
                description = :description
            WHERE
                ward_id = :ward_id';
        $stmt = $this->conn->prepare($query);

        $this->ward_name = htmlspecialchars(strip_tags($this->ward_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->ward_id = htmlspecialchars(strip_tags($this->ward_id));

        $stmt->bindParam(':ward_name', $this->ward_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':ward_id', $this->ward_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Delete ward
    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE ward_id = :ward_id';
        $stmt = $this->conn->prepare($query);

        $this->ward_id = htmlspecialchars(strip_tags($this->ward_id));
        $stmt->bindParam(':ward_id', $this->ward_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}
?>
