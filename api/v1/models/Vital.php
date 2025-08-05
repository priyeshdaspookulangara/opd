<?php
class Vital {
    private $conn;
    private $table = 'vitals';

    // Vitals Properties
    public $vital_id;
    public $op_id;
    public $temperature;
    public $blood_pressure;
    public $heart_rate;
    public $respiratory_rate;
    public $recorded_at;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all vitals for a specific visit
    public function read_for_visit() {
        $query = 'SELECT
                vital_id,
                temperature,
                blood_pressure,
                heart_rate,
                respiratory_rate,
                recorded_at
            FROM
                ' . $this->table . '
            WHERE
                op_id = ?
            ORDER BY
                recorded_at DESC
            LIMIT 1'; // Get the most recent vitals for the visit

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->op_id);
        $stmt->execute();
        return $stmt;
    }

    // Create Vitals
    public function create() {
        // Create query
        $query = 'INSERT INTO ' . $this->table . '
            SET
                op_id = :op_id,
                temperature = :temperature,
                blood_pressure = :blood_pressure,
                heart_rate = :heart_rate,
                respiratory_rate = :respiratory_rate';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->op_id = htmlspecialchars(strip_tags($this->op_id));
        $this->temperature = htmlspecialchars(strip_tags($this->temperature));
        $this->blood_pressure = htmlspecialchars(strip_tags($this->blood_pressure));
        $this->heart_rate = htmlspecialchars(strip_tags($this->heart_rate));
        $this->respiratory_rate = htmlspecialchars(strip_tags($this->respiratory_rate));

        // Bind data
        $stmt->bindParam(':op_id', $this->op_id);
        $stmt->bindParam(':temperature', $this->temperature);
        $stmt->bindParam(':blood_pressure', $this->blood_pressure);
        $stmt->bindParam(':heart_rate', $this->heart_rate);
        $stmt->bindParam(':respiratory_rate', $this->respiratory_rate);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }
}
?>
