<?php
class Visit {
    private $conn;
    private $table = 'opd_visits';

    // Visit Properties
    public $op_id;
    public $op_number;
    public $patient_id;
    public $visit_date;
    public $doctor_id; // Optional for now

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read Active Queue
    public function read_active_queue() {
        $query = 'SELECT
                v.op_id,
                v.op_number,
                v.patient_id,
                p.first_name,
                p.last_name,
                v.visit_date
            FROM
                ' . $this->table . ' v
            JOIN
                patients p ON v.patient_id = p.patient_id
            LEFT JOIN
                doctor_consultations dc ON v.op_id = dc.op_id
            WHERE
                dc.consultation_id IS NULL
            ORDER BY
                v.visit_date ASC, v.op_id ASC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create Visit
    public function create() {
        // Generate a unique OP Number
        $this->op_number = 'OPD-' . date('YmdHis') . '-' . rand(100, 999);
        $this->visit_date = date('Y-m-d');

        // Create query
        $query = 'INSERT INTO ' . $this->table . '
            SET
                op_number = :op_number,
                patient_id = :patient_id,
                visit_date = :visit_date';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));

        // Bind data
        $stmt->bindParam(':op_number', $this->op_number);
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':visit_date', $this->visit_date);

        // Execute query
        if($stmt->execute()) {
            $this->op_id = $this->conn->lastInsertId();
            return true;
        }

        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }
}
?>
