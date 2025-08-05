<?php
class Admission
{
    private $conn;
    private $table = 'admissions';

    // Admission Properties
    public $admission_id;
    public $patient_id;
    public $bed_id;
    public $admission_date;
    public $discharge_date;
    public $diagnosis;
    public $created_at;

    // Constructor with DB
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create Admission
    public function create()
    {
        // Create query
        $query = 'INSERT INTO ' . $this->table . ' SET patient_id = :patient_id, bed_id = :bed_id, admission_date = :admission_date, discharge_date = :discharge_date, diagnosis = :diagnosis';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->bed_id = htmlspecialchars(strip_tags($this->bed_id));
        $this->admission_date = htmlspecialchars(strip_tags($this->admission_date));
        $this->discharge_date = htmlspecialchars(strip_tags($this->discharge_date));
        $this->diagnosis = htmlspecialchars(strip_tags($this->diagnosis));

        // Bind data
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':bed_id', $this->bed_id);
        $stmt->bindParam(':admission_date', $this->admission_date);
        $stmt->bindParam(':discharge_date', $this->discharge_date);
        $stmt->bindParam(':diagnosis', $this->diagnosis);

        // Execute query
        if ($stmt->execute()) {
            $this->admission_id = $this->conn->lastInsertId();
            return true;
        }

        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    // Read Admissions
    public function read()
    {
        // Create query
        $query = 'SELECT
            a.admission_id,
            a.patient_id,
            p.name as patient_name,
            a.bed_id,
            b.bed_number,
            w.ward_name,
            a.admission_date,
            a.discharge_date,
            a.diagnosis
        FROM
            ' . $this->table . ' a
        LEFT JOIN
            patients p ON a.patient_id = p.patient_id
        LEFT JOIN
            beds b ON a.bed_id = b.bed_id
        LEFT JOIN
            wards w ON b.ward_id = w.ward_id
        ORDER BY
            a.admission_date DESC';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Discharge Patient
    public function discharge()
    {
        // Create query
        $query = 'UPDATE ' . $this->table . ' SET discharge_date = :discharge_date WHERE admission_id = :admission_id';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->discharge_date = htmlspecialchars(strip_tags($this->discharge_date));
        $this->admission_id = htmlspecialchars(strip_tags($this->admission_id));

        // Bind data
        $stmt->bindParam(':discharge_date', $this->discharge_date);
        $stmt->bindParam(':admission_id', $this->admission_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Get Bed ID from Admission ID
    public function getBedId()
    {
        $query = 'SELECT bed_id FROM ' . $this->table . ' WHERE admission_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->admission_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['bed_id'];
        }
        return null;
    }
}
