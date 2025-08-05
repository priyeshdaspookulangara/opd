<?php
class Consultation {
    private $conn;
    private $table = 'doctor_consultations';

    // Properties
    public $consultation_id;
    public $op_id;
    public $doctor_id;
    public $diagnosis;
    public $prescription;
    public $notes;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new consultation record
    public function create() {
        $query = 'INSERT INTO ' . $this->table . '
            SET
                op_id = :op_id,
                doctor_id = :doctor_id,
                diagnosis = :diagnosis,
                prescription = :prescription,
                notes = :notes';

        $stmt = $this->conn->prepare($query);

        $this->op_id = htmlspecialchars(strip_tags($this->op_id));
        $this->doctor_id = htmlspecialchars(strip_tags($this->doctor_id));
        $this->diagnosis = htmlspecialchars(strip_tags($this->diagnosis));
        $this->prescription = htmlspecialchars(strip_tags($this->prescription));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        $stmt->bindParam(':op_id', $this->op_id);
        $stmt->bindParam(':doctor_id', $this->doctor_id);
        $stmt->bindParam(':diagnosis', $this->diagnosis);
        $stmt->bindParam(':prescription', $this->prescription);
        $stmt->bindParam(':notes', $this->notes);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}
?>
