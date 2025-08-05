<?php
class Package {
    private $conn;
    private $table = 'packages';

    // Properties
    public $package_id;
    public $package_name;
    public $description;
    public $price;
    public $duration_days;
    public $is_active;

    // For handling services
    public $service_ids = [];

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all packages
    public function read() {
        $query = 'SELECT package_id, package_name, description, price, duration_days, is_active FROM ' . $this->table . ' ORDER BY package_name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single package with its services
    public function read_one() {
        // Get package details
        $query = 'SELECT package_id, package_name, description, price, duration_days, is_active FROM ' . $this->table . ' WHERE package_id = ? LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->package_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row) {
            return false;
        }

        $this->package_name = $row['package_name'];
        $this->description = $row['description'];
        $this->price = $row['price'];
        $this->duration_days = $row['duration_days'];
        $this->is_active = $row['is_active'];

        // Get associated services
        $service_query = 'SELECT service_id FROM package_services WHERE package_id = ?';
        $service_stmt = $this->conn->prepare($service_query);
        $service_stmt->bindParam(1, $this->package_id);
        $service_stmt->execute();
        $this->service_ids = $service_stmt->fetchAll(PDO::FETCH_COLUMN);

        return true;
    }

    // Create package
    public function create() {
        $this->conn->beginTransaction();
        try {
            $query = 'INSERT INTO ' . $this->table . ' SET package_name = :package_name, description = :description, price = :price, duration_days = :duration_days, is_active = :is_active';
            $stmt = $this->conn->prepare($query);

            $this->package_name=htmlspecialchars(strip_tags($this->package_name));
            $this->description=htmlspecialchars(strip_tags($this->description));
            $this->price=htmlspecialchars(strip_tags($this->price));
            $this->duration_days=htmlspecialchars(strip_tags($this->duration_days));
            $this->is_active=htmlspecialchars(strip_tags($this->is_active));

            $stmt->bindParam(':package_name', $this->package_name);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':price', $this->price);
            $stmt->bindParam(':duration_days', $this->duration_days);
            $stmt->bindParam(':is_active', $this->is_active);

            $stmt->execute();
            $this->package_id = $this->conn->lastInsertId();

            // Handle package services
            $this->manage_package_services();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            printf("Error: %s.\n", $e->getMessage());
            return false;
        }
    }

    // Update package
    public function update() {
        $this->conn->beginTransaction();
        try {
            $query = 'UPDATE ' . $this->table . '
                SET
                    package_name = :package_name,
                    description = :description,
                    price = :price,
                    duration_days = :duration_days,
                    is_active = :is_active
                WHERE
                    package_id = :package_id';
            $stmt = $this->conn->prepare($query);

            $this->package_name=htmlspecialchars(strip_tags($this->package_name));
            $this->description=htmlspecialchars(strip_tags($this->description));
            $this->price=htmlspecialchars(strip_tags($this->price));
            $this->duration_days=htmlspecialchars(strip_tags($this->duration_days));
            $this->is_active=htmlspecialchars(strip_tags($this->is_active));
            $this->package_id=htmlspecialchars(strip_tags($this->package_id));

            $stmt->bindParam(':package_name', $this->package_name);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':price', $this->price);
            $stmt->bindParam(':duration_days', $this->duration_days);
            $stmt->bindParam(':is_active', $this->is_active);
            $stmt->bindParam(':package_id', $this->package_id);

            $stmt->execute();

            // Handle package services
            $this->manage_package_services();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            printf("Error: %s.\n", $e->getMessage());
            return false;
        }
    }

    // Delete package
    public function delete() {
        // The ON DELETE CASCADE in the DB schema handles the package_services table
        $query = 'DELETE FROM ' . $this->table . ' WHERE package_id = :package_id';
        $stmt = $this->conn->prepare($query);
        $this->package_id=htmlspecialchars(strip_tags($this->package_id));
        $stmt->bindParam(':package_id', $this->package_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Helper function to manage the linker table
    private function manage_package_services() {
        // First, remove all existing services for this package
        $delete_query = 'DELETE FROM package_services WHERE package_id = :package_id';
        $delete_stmt = $this->conn->prepare($delete_query);
        $delete_stmt->bindParam(':package_id', $this->package_id);
        $delete_stmt->execute();

        // Now, insert the new services
        if (!empty($this->service_ids)) {
            $insert_query = 'INSERT INTO package_services (package_id, service_id) VALUES (:package_id, :service_id)';
            $insert_stmt = $this->conn->prepare($insert_query);

            foreach($this->service_ids as $service_id) {
                $insert_stmt->bindParam(':package_id', $this->package_id);
                $insert_stmt->bindParam(':service_id', $service_id);
                $insert_stmt->execute();
            }
        }
    }
}
?>
