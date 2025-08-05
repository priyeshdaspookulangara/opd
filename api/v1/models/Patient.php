<?php
class Patient {
    private $conn;
    private $table = 'patients';

    // Patient Properties
    public $patient_id;
    public $first_name;
    public $last_name;
    public $date_of_birth;
    public $gender;
    public $phone_number;
    public $email;
    public $address;
    public $age; // New property

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create Patient and associated account
    public function create() {
        // --- Age to Date of Birth Calculation ---
        if (empty($this->date_of_birth) && !empty($this->age)) {
            $birth_year = date('Y') - (int)$this->age;
            $this->date_of_birth = $birth_year . '-01-01';
        }

        // Begin a transaction
        $this->conn->beginTransaction();

        try {
            // Create query for patient
            $query = 'INSERT INTO ' . $this->table . '
                SET
                    first_name = :first_name,
                    last_name = :last_name,
                    date_of_birth = :date_of_birth,
                    gender = :gender,
                    phone_number = :phone_number,
                    email = :email,
                    address = :address';

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Clean data
            $this->first_name = htmlspecialchars(strip_tags($this->first_name));
            $this->last_name = htmlspecialchars(strip_tags($this->last_name));
            $this->date_of_birth = htmlspecialchars(strip_tags($this->date_of_birth));
            $this->gender = htmlspecialchars(strip_tags($this->gender));
            $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->address = htmlspecialchars(strip_tags($this->address));

            // Bind data
            $stmt->bindParam(':first_name', $this->first_name);
            $stmt->bindParam(':last_name', $this->last_name);
            $stmt->bindParam(':date_of_birth', $this->date_of_birth);
            $stmt->bindParam(':gender', $this->gender);
            $stmt->bindParam(':phone_number', $this->phone_number);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':address', $this->address);

            // Execute query
            $stmt->execute();

            // Get the last inserted patient ID
            $this->patient_id = $this->conn->lastInsertId();

            // Create a corresponding account
            $account_query = 'INSERT INTO accounts (patient_id, balance) VALUES (:patient_id, 0.00)';
            $account_stmt = $this->conn->prepare($account_query);
            $account_stmt->bindParam(':patient_id', $this->patient_id);
            $account_stmt->execute();

            // Commit the transaction
            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            // Rollback the transaction if something failed
            $this->conn->rollBack();
            printf("Error: %s.\n", $e->getMessage());
            return false;
        }
    }

    // Get Patients
    public function read() {
        // Create query
        $query = 'SELECT
            p.patient_id,
            p.first_name,
            p.last_name,
            p.date_of_birth,
            p.gender,
            p.phone_number,
            p.email,
            p.address,
            p.created_at
        FROM
            ' . $this->table . ' p
        ORDER BY
            p.created_at DESC';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }
}
?>
