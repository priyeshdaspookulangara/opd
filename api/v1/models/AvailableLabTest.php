<?php
class AvailableLabTest {
    private $conn;
    private $table = 'available_lab_tests';

    // Properties
    public $available_test_id;
    public $test_name;
    public $description;
    public $cost;
    public $is_available;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all available lab tests
    public function read() {
        $query = 'SELECT available_test_id, test_name, description, cost, is_available FROM ' . $this->table . ' ORDER BY test_name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create available lab test
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' SET test_name = :test_name, description = :description, cost = :cost, is_available = :is_available';
        $stmt = $this->conn->prepare($query);

        $this->test_name = htmlspecialchars(strip_tags($this->test_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->cost = htmlspecialchars(strip_tags($this->cost));
        $this->is_available = htmlspecialchars(strip_tags($this->is_available));

        $stmt->bindParam(':test_name', $this->test_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cost', $this->cost);
        $stmt->bindParam(':is_available', $this->is_available);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Update available lab test
    public function update() {
        $query = 'UPDATE ' . $this->table . '
            SET
                test_name = :test_name,
                description = :description,
                cost = :cost,
                is_available = :is_available
            WHERE
                available_test_id = :available_test_id';
        $stmt = $this->conn->prepare($query);

        $this->test_name = htmlspecialchars(strip_tags($this->test_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->cost = htmlspecialchars(strip_tags($this->cost));
        $this->is_available = htmlspecialchars(strip_tags($this->is_available));
        $this->available_test_id = htmlspecialchars(strip_tags($this->available_test_id));

        $stmt->bindParam(':test_name', $this->test_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cost', $this->cost);
        $stmt->bindParam(':is_available', $this->is_available);
        $stmt->bindParam(':available_test_id', $this->available_test_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Delete available lab test (soft delete by setting is_available to false)
    public function delete() {
        $query = 'UPDATE ' . $this->table . ' SET is_available = FALSE WHERE available_test_id = :available_test_id';
        $stmt = $this->conn->prepare($query);

        $this->available_test_id = htmlspecialchars(strip_tags($this->available_test_id));
        $stmt->bindParam(':available_test_id', $this->available_test_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}
?>
