<?php
class PatientPackage {
    private $conn;
    private $table = 'patient_packages';

    // Properties
    public $patient_package_id;
    public $patient_id;
    public $package_id;
    public $start_date;
    public $end_date;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Assign a package to a patient
    public function create() {
        // First, get the package duration
        $package_query = 'SELECT duration_days FROM packages WHERE package_id = :package_id LIMIT 0,1';
        $package_stmt = $this->conn->prepare($package_query);
        $package_stmt->bindParam(':package_id', $this->package_id);
        $package_stmt->execute();
        $package_row = $package_stmt->fetch(PDO::FETCH_ASSOC);

        if(!$package_row) {
            // Package not found
            return false;
        }
        $duration = $package_row['duration_days'];

        // Set dates
        $this->start_date = date('Y-m-d');
        $this->end_date = date('Y-m-d', strtotime('+' . $duration . ' days'));

        // Create query
        $query = 'INSERT INTO ' . $this->table . ' SET patient_id = :patient_id, package_id = :package_id, start_date = :start_date, end_date = :end_date';
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->package_id = htmlspecialchars(strip_tags($this->package_id));

        // Bind data
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':package_id', $this->package_id);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Read all active packages for a patient
    public function read_for_patient() {
        $query = 'SELECT
                pp.patient_package_id,
                p.package_name,
                p.description,
                p.price,
                pp.start_date,
                pp.end_date
            FROM
                ' . $this->table . ' pp
            JOIN
                packages p ON pp.package_id = p.package_id
            WHERE
                pp.patient_id = ? AND pp.end_date >= CURDATE()
            ORDER BY
                pp.start_date DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->patient_id);
        $stmt->execute();
        return $stmt;
    }
}
?>
