<?php
class Drug {
    private $conn;
    private $table = 'pharmacy_drugs';

    // Properties
    public $drug_id;
    public $drug_name;
    public $description;
    public $cost_per_unit;
    public $stock_quantity;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all drugs
    public function read() {
        $query = 'SELECT drug_id, drug_name, description, cost_per_unit, stock_quantity FROM ' . $this->table . ' ORDER BY drug_name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create drug
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' SET drug_name = :drug_name, description = :description, cost_per_unit = :cost_per_unit, stock_quantity = :stock_quantity';
        $stmt = $this->conn->prepare($query);

        $this->drug_name = htmlspecialchars(strip_tags($this->drug_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->cost_per_unit = htmlspecialchars(strip_tags($this->cost_per_unit));
        $this->stock_quantity = htmlspecialchars(strip_tags($this->stock_quantity));

        $stmt->bindParam(':drug_name', $this->drug_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cost_per_unit', $this->cost_per_unit);
        $stmt->bindParam(':stock_quantity', $this->stock_quantity);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Update drug
    public function update() {
        $query = 'UPDATE ' . $this->table . '
            SET
                drug_name = :drug_name,
                description = :description,
                cost_per_unit = :cost_per_unit,
                stock_quantity = :stock_quantity
            WHERE
                drug_id = :drug_id';
        $stmt = $this->conn->prepare($query);

        $this->drug_name = htmlspecialchars(strip_tags($this->drug_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->cost_per_unit = htmlspecialchars(strip_tags($this->cost_per_unit));
        $this->stock_quantity = htmlspecialchars(strip_tags($this->stock_quantity));
        $this->drug_id = htmlspecialchars(strip_tags($this->drug_id));

        $stmt->bindParam(':drug_name', $this->drug_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cost_per_unit', $this->cost_per_unit);
        $stmt->bindParam(':stock_quantity', $this->stock_quantity);
        $stmt->bindParam(':drug_id', $this->drug_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Delete drug
    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE drug_id = :drug_id';
        $stmt = $this->conn->prepare($query);

        $this->drug_id = htmlspecialchars(strip_tags($this->drug_id));
        $stmt->bindParam(':drug_id', $this->drug_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}
?>
