<?php
class Dispensation {
    private $conn;
    private $table = 'pharmacy_dispensations';

    // Properties
    public $dispensation_id;
    public $op_id;
    public $drug_id;
    public $quantity;
    public $total_cost;

    // For billing
    public $patient_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new dispensation record
    public function create() {
        $this->conn->beginTransaction();

        try {
            // 1. Get drug cost and current stock
            $drug_query = 'SELECT drug_name, cost_per_unit, stock_quantity FROM pharmacy_drugs WHERE drug_id = :drug_id LIMIT 1';
            $drug_stmt = $this->conn->prepare($drug_query);
            $drug_stmt->bindParam(':drug_id', $this->drug_id);
            $drug_stmt->execute();
            $drug_row = $drug_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$drug_row) {
                throw new Exception('Drug not found.');
            }
            if ($drug_row['stock_quantity'] < $this->quantity) {
                throw new Exception('Not enough stock available.');
            }

            $this->total_cost = $this->quantity * $drug_row['cost_per_unit'];
            $new_stock = $drug_row['stock_quantity'] - $this->quantity;
            $drug_name = $drug_row['drug_name'];

            // 2. Create the dispensation record
            $dispensation_query = 'INSERT INTO ' . $this->table . ' SET op_id = :op_id, drug_id = :drug_id, quantity = :quantity, total_cost = :total_cost';
            $dispensation_stmt = $this->conn->prepare($dispensation_query);

            $this->op_id = htmlspecialchars(strip_tags($this->op_id));
            $this->drug_id = htmlspecialchars(strip_tags($this->drug_id));
            $this->quantity = htmlspecialchars(strip_tags($this->quantity));

            $dispensation_stmt->bindParam(':op_id', $this->op_id);
            $dispensation_stmt->bindParam(':drug_id', $this->drug_id);
            $dispensation_stmt->bindParam(':quantity', $this->quantity);
            $dispensation_stmt->bindParam(':total_cost', $this->total_cost);
            $dispensation_stmt->execute();

            // 3. Update stock quantity
            $stock_update_query = 'UPDATE pharmacy_drugs SET stock_quantity = :new_stock WHERE drug_id = :drug_id';
            $stock_update_stmt = $this->conn->prepare($stock_update_query);
            $stock_update_stmt->bindParam(':new_stock', $new_stock);
            $stock_update_stmt->bindParam(':drug_id', $this->drug_id);
            $stock_update_stmt->execute();

            // 4. Trigger the billing engine for a direct charge
            include_once 'Billing.php';
            $billing = new Billing($this->conn);
            $billing->patient_id = $this->patient_id;
            $billing->op_id = $this->op_id;
            $billing->service_id = null; // No service_id for direct charge
            $billing->amount = $this->total_cost;
            $billing->description = "Pharmacy: " . $this->quantity . "x " . $drug_name;

            if (!$billing->create()) {
                throw new Exception('Failed to create billing record.');
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            printf("Error: %s.\n", $e->getMessage());
            return false;
        }
    }
}
?>
