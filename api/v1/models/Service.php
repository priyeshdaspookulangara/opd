<?php
class Service {
    private $conn;
    private $table = 'services';

    // Properties
    public $service_id;
    public $service_name;
    public $description;
    public $cost;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all services
    public function read() {
        $query = 'SELECT service_id, service_name, description, cost FROM ' . $this->table . ' ORDER BY service_name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single service
    public function read_one() {
        $query = 'SELECT service_id, service_name, description, cost FROM ' . $this->table . ' WHERE service_id = ? LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->service_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->service_name = $row['service_name'];
            $this->description = $row['description'];
            $this->cost = $row['cost'];
            return true;
        }
        return false;
    }

    // Create service
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' SET service_name = :service_name, description = :description, cost = :cost';
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->service_name = htmlspecialchars(strip_tags($this->service_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->cost = htmlspecialchars(strip_tags($this->cost));

        // Bind data
        $stmt->bindParam(':service_name', $this->service_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cost', $this->cost);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Update service
    public function update() {
        $query = 'UPDATE ' . $this->table . '
            SET
                service_name = :service_name,
                description = :description,
                cost = :cost
            WHERE
                service_id = :service_id';
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->service_name = htmlspecialchars(strip_tags($this->service_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->cost = htmlspecialchars(strip_tags($this->cost));
        $this->service_id = htmlspecialchars(strip_tags($this->service_id));

        // Bind data
        $stmt->bindParam(':service_name', $this->service_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cost', $this->cost);
        $stmt->bindParam(':service_id', $this->service_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Delete service
    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE service_id = :service_id';
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->service_id = htmlspecialchars(strip_tags($this->service_id));

        // Bind data
        $stmt->bindParam(':service_id', $this->service_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}
?>
