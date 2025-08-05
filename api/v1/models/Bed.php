<?php
class Bed {
    private $conn;
    private $table = 'beds';

    // Properties
    public $bed_id;
    public $bed_number;
    public $ward_id;
    public $is_occupied;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all beds, optionally by ward
    public function read() {
        $query = 'SELECT b.bed_id, b.bed_number, b.is_occupied, w.ward_name
                  FROM ' . $this->table . ' b
                  JOIN wards w ON b.ward_id = w.ward_id';

        if (!empty($this->ward_id)) {
            $query .= ' WHERE b.ward_id = :ward_id';
        }

        $query .= ' ORDER BY w.ward_name, b.bed_number ASC';

        $stmt = $this->conn->prepare($query);

        if (!empty($this->ward_id)) {
            $stmt->bindParam(':ward_id', $this->ward_id);
        }

        $stmt->execute();
        return $stmt;
    }

    // Create bed
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' SET bed_number = :bed_number, ward_id = :ward_id, is_occupied = :is_occupied';
        $stmt = $this->conn->prepare($query);

        $this->bed_number = htmlspecialchars(strip_tags($this->bed_number));
        $this->ward_id = htmlspecialchars(strip_tags($this->ward_id));
        $this->is_occupied = !empty($this->is_occupied) ? 1 : 0;

        $stmt->bindParam(':bed_number', $this->bed_number);
        $stmt->bindParam(':ward_id', $this->ward_id);
        $stmt->bindParam(':is_occupied', $this->is_occupied);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Update bed
    public function update() {
        $query = 'UPDATE ' . $this->table . '
            SET
                bed_number = :bed_number,
                ward_id = :ward_id,
                is_occupied = :is_occupied
            WHERE
                bed_id = :bed_id';
        $stmt = $this->conn->prepare($query);

        $this->bed_number = htmlspecialchars(strip_tags($this->bed_number));
        $this->ward_id = htmlspecialchars(strip_tags($this->ward_id));
        $this->is_occupied = !empty($this->is_occupied) ? 1 : 0;
        $this->bed_id = htmlspecialchars(strip_tags($this->bed_id));

        $stmt->bindParam(':bed_number', $this->bed_number);
        $stmt->bindParam(':ward_id', $this->ward_id);
        $stmt->bindParam(':is_occupied', $this->is_occupied);
        $stmt->bindParam(':bed_id', $this->bed_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Delete bed
    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE bed_id = :bed_id';
        $stmt = $this->conn->prepare($query);

        $this->bed_id = htmlspecialchars(strip_tags($this->bed_id));
        $stmt->bindParam(':bed_id', $this->bed_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}
?>
