<?php
class LabTest {
    private $conn;
    private $table = 'lab_tests';

    // Properties
    public $lab_test_id;
    public $op_id;
    public $available_test_id;
    public $status;
    public $result;
    public $notes;

    // For billing
    public $patient_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Order a new lab test
    public function order() {
        $this->conn->beginTransaction();

        try {
            // 1. Create the lab test record
            $query = 'INSERT INTO ' . $this->table . ' SET op_id = :op_id, available_test_id = :available_test_id, status = "Pending"';
            $stmt = $this->conn->prepare($query);

            $this->op_id = htmlspecialchars(strip_tags($this->op_id));
            $this->available_test_id = htmlspecialchars(strip_tags($this->available_test_id));

            $stmt->bindParam(':op_id', $this->op_id);
            $stmt->bindParam(':available_test_id', $this->available_test_id);
            $stmt->execute();
            $this->lab_test_id = $this->conn->lastInsertId();

            // 2. Trigger the billing engine
            $test_cost_query = 'SELECT cost, test_name FROM available_lab_tests WHERE available_test_id = :available_test_id LIMIT 1';
            $test_cost_stmt = $this->conn->prepare($test_cost_query);
            $test_cost_stmt->bindParam(':available_test_id', $this->available_test_id);
            $test_cost_stmt->execute();
            $test_row = $test_cost_stmt->fetch(PDO::FETCH_ASSOC);

            if(!$test_row) {
                throw new Exception("Lab test not found in available tests.");
            }

            // A more robust solution might check if the lab test service is part of a package.
            // For now, we do a direct charge.
            include_once 'Billing.php';
            $billing = new Billing($this->conn);
            $billing->patient_id = $this->patient_id;
            $billing->op_id = $this->op_id;
            $billing->service_id = null; // No service_id for direct charge
            $billing->amount = $test_row['cost'];
            $billing->description = "Lab Test: " . $test_row['test_name'];

            if (!$billing->create()) {
                throw new Exception('Failed to create billing record for lab test.');
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            printf("Error: %s.\n", $e->getMessage());
            return false;
        }
    }

    // Read all pending tests for the lab tech view
    public function read_pending() {
        $query = 'SELECT
                lt.lab_test_id,
                lt.op_id,
                p.first_name,
                p.last_name,
                atl.test_name,
                lt.status,
                lt.test_date
            FROM
                ' . $this->table . ' lt
            JOIN
                opd_visits ov ON lt.op_id = ov.op_id
            JOIN
                patients p ON ov.patient_id = p.patient_id
            JOIN
                available_lab_tests atl ON lt.available_test_id = atl.available_test_id
            WHERE
                lt.status = "Pending"
            ORDER BY
                lt.test_date ASC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update a test with results
    public function update_result() {
        $query = 'UPDATE ' . $this->table . '
            SET
                result = :result,
                notes = :notes,
                status = "Completed"
            WHERE
                lab_test_id = :lab_test_id';

        $stmt = $this->conn->prepare($query);

        $this->result = htmlspecialchars(strip_tags($this->result));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->lab_test_id = htmlspecialchars(strip_tags($this->lab_test_id));

        $stmt->bindParam(':result', $this->result);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':lab_test_id', $this->lab_test_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}
?>
