<?php
class Billing {
    private $conn;
    private $table = 'billings';

    // Properties
    public $bill_id;
    public $op_id;
    public $patient_id;
    public $service_id;
    public $amount;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new billing entry
    public function create() {
        $this->conn->beginTransaction();

        try {
            $patient_package_id = null;
            $charge_amount = 0;

            // If a service_id is provided, check for package coverage
            if (!empty($this->service_id)) {
                $coverage_query = '
                    SELECT pp.patient_package_id
                    FROM patient_packages pp
                    JOIN package_services ps ON pp.package_id = ps.package_id
                    WHERE pp.patient_id = :patient_id
                      AND ps.service_id = :service_id
                      AND pp.end_date >= CURDATE()
                    LIMIT 1
                ';
                $coverage_stmt = $this->conn->prepare($coverage_query);
                $coverage_stmt->bindParam(':patient_id', $this->patient_id);
                $coverage_stmt->bindParam(':service_id', $this->service_id);
                $coverage_stmt->execute();
                $covering_package = $coverage_stmt->fetch(PDO::FETCH_ASSOC);

                if ($covering_package) {
                    // Service is covered by a package
                    $patient_package_id = $covering_package['patient_package_id'];
                    $this->amount = 0;
                    // Get service name for description
                    $service_name_query = 'SELECT service_name FROM services WHERE service_id = :service_id LIMIT 1';
                    $service_name_stmt = $this->conn->prepare($service_name_query);
                    $service_name_stmt->bindParam(':service_id', $this->service_id);
                    $service_name_stmt->execute();
                    $service_row = $service_name_stmt->fetch(PDO::FETCH_ASSOC);
                    $this->description = $service_row['service_name'] . ' (Covered by package)';

                } else {
                    // Service is not covered, get the cost
                    $service_cost_query = 'SELECT cost, service_name FROM services WHERE service_id = :service_id LIMIT 1';
                    $service_cost_stmt = $this->conn->prepare($service_cost_query);
                    $service_cost_stmt->bindParam(':service_id', $this->service_id);
                    $service_cost_stmt->execute();
                    $service_row = $service_cost_stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$service_row) {
                        throw new Exception('Service not found.');
                    }
                    $this->amount = $service_row['cost'];
                    $this->description = $service_row['service_name'];
                    $charge_amount = $this->amount;
                }
            } else {
                // This is a direct charge (e.g., for pharmacy), not a service.
                // The amount and description must be set before calling create().
                if (!isset($this->amount) || !isset($this->description)) {
                    throw new Exception('Amount and description are required for direct billing.');
                }
                $charge_amount = $this->amount;
            }

            // 2. Insert into billings table
            $billing_query = 'INSERT INTO ' . $this->table . '
                SET
                    op_id = :op_id,
                    patient_id = :patient_id,
                    service_id = :service_id,
                    patient_package_id = :patient_package_id,
                    amount = :amount,
                    description = :description';

            $billing_stmt = $this->conn->prepare($billing_query);

            $this->op_id = htmlspecialchars(strip_tags($this->op_id));
            $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
            $this->service_id = htmlspecialchars(strip_tags($this->service_id));
            $this->amount = htmlspecialchars(strip_tags($this->amount));
            $this->description = htmlspecialchars(strip_tags($this->description));

            $billing_stmt->bindParam(':op_id', $this->op_id);
            $billing_stmt->bindParam(':patient_id', $this->patient_id);
            $billing_stmt->bindParam(':service_id', $this->service_id);
            $billing_stmt->bindParam(':patient_package_id', $patient_package_id);
            $billing_stmt->bindParam(':amount', $this->amount);
            $billing_stmt->bindParam(':description', $this->description);

            $billing_stmt->execute();

            // 3. Update patient's account balance if necessary
            if ($charge_amount > 0) {
                $account_update_query = 'UPDATE accounts SET balance = balance + :charge_amount WHERE patient_id = :patient_id';
                $account_update_stmt = $this->conn->prepare($account_update_query);
                $account_update_stmt->bindParam(':charge_amount', $charge_amount);
                $account_update_stmt->bindParam(':patient_id', $this->patient_id);
                $account_update_stmt->execute();
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
