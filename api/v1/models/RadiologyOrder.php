<?php
class RadiologyOrder {
    private $conn;
    private $table = 'radiology_orders';

    // Properties
    public $radiology_order_id;
    public $op_id;
    public $available_test_id;
    public $status;
    public $report;
    public $notes;

    // For billing
    public $patient_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Order a new radiology test
    public function order() {
        $this->conn->beginTransaction();

        try {
            // 1. Create the radiology order record
            $query = 'INSERT INTO ' . $this->table . ' SET op_id = :op_id, available_test_id = :available_test_id, status = "Pending"';
            $stmt = $this->conn->prepare($query);

            $this->op_id = htmlspecialchars(strip_tags($this->op_id));
            $this->available_test_id = htmlspecialchars(strip_tags($this->available_test_id));

            $stmt->bindParam(':op_id', $this->op_id);
            $stmt->bindParam(':available_test_id', $this->available_test_id);
            $stmt->execute();
            $this->radiology_order_id = $this->conn->lastInsertId();

            // 2. Trigger the billing engine
            $test_cost_query = 'SELECT cost, test_name FROM available_radiology_tests WHERE available_test_id = :available_test_id LIMIT 1';
            $test_cost_stmt = $this->conn->prepare($test_cost_query);
            $test_cost_stmt->bindParam(':available_test_id', $this->available_test_id);
            $test_cost_stmt->execute();
            $test_row = $test_cost_stmt->fetch(PDO::FETCH_ASSOC);

            if(!$test_row) {
                throw new Exception("Radiology test not found in available tests.");
            }

            include_once 'Billing.php';
            $billing = new Billing($this->conn);
            $billing->patient_id = $this->patient_id;
            $billing->op_id = $this->op_id;
            $billing->service_id = null; // No service_id for direct charge
            $billing->amount = $test_row['cost'];
            $billing->description = "Radiology: " . $test_row['test_name'];

            if (!$billing->create()) {
                throw new Exception('Failed to create billing record for radiology test.');
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            printf("Error: %s.\n", $e->getMessage());
            return false;
        }
    }

    // Read all pending tests for the radiologist view
    public function read_pending() {
        $query = 'SELECT
                ro.radiology_order_id,
                ro.op_id,
                p.first_name,
                p.last_name,
                art.test_name,
                ro.status,
                ro.order_date
            FROM
                ' . $this->table . ' ro
            JOIN
                opd_visits ov ON ro.op_id = ov.op_id
            JOIN
                patients p ON ov.patient_id = p.patient_id
            JOIN
                available_radiology_tests art ON ro.available_test_id = art.available_test_id
            WHERE
                ro.status = "Pending"
            ORDER BY
                ro.order_date ASC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update a test with a report
    public function update_report() {
        $query = 'UPDATE ' . $this->table . '
            SET
                report = :report,
                notes = :notes,
                status = "Completed"
            WHERE
                radiology_order_id = :radiology_order_id';

        $stmt = $this->conn->prepare($query);

        $this->report = htmlspecialchars(strip_tags($this->report));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->radiology_order_id = htmlspecialchars(strip_tags($this->radiology_order_id));

        $stmt->bindParam(':report', $this->report);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':radiology_order_id', $this->radiology_order_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}
?>
