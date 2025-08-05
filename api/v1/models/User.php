<?php
class User {
    private $conn;
    private $table = 'users';

    // Properties
    public $user_id;
    public $username;
    public $password; // This will be the plain text password for login/create
    public $password_hash; // This is what's stored in the DB
    public $role;
    public $first_name;
    public $last_name;
    public $is_active;


    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all users
    public function read() {
        $query = 'SELECT user_id, username, role, first_name, last_name, is_active FROM ' . $this->table . ' ORDER BY last_name, first_name';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create a new user
    public function create() {
        $query = 'INSERT INTO ' . $this->table . '
            SET
                username = :username,
                password_hash = :password_hash,
                role = :role,
                first_name = :first_name,
                last_name = :last_name';

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));

        // Hash the password
        $this->password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password_hash', $this->password_hash);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // User login
    public function login() {
        $query = 'SELECT user_id, username, password_hash, role FROM ' . $this->table . ' WHERE username = :username LIMIT 0,1';

        $stmt = $this->conn->prepare($query);
        $this->username = htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            if(password_verify($this->password, $row['password_hash'])) {
                $this->user_id = $row['user_id'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    // Read single user by ID
    public function read_one() {
        $query = 'SELECT user_id, username, role, first_name, last_name, is_active FROM ' . $this->table . ' WHERE user_id = ? LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->username = $row['username'];
            $this->role = $row['role'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    // Update user
    public function update() {
        // If password is provided, update it as well
        if(!empty($this->password)) {
            $query = 'UPDATE ' . $this->table . '
                SET
                    username = :username,
                    password_hash = :password_hash,
                    role = :role,
                    first_name = :first_name,
                    last_name = :last_name,
                    is_active = :is_active
                WHERE
                    user_id = :user_id';
        } else {
            // Do not update password
             $query = 'UPDATE ' . $this->table . '
                SET
                    username = :username,
                    role = :role,
                    first_name = :first_name,
                    last_name = :last_name,
                    is_active = :is_active
                WHERE
                    user_id = :user_id';
        }

        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->is_active = isset($this->is_active) ? $this->is_active : true;


        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':is_active', $this->is_active, PDO::PARAM_BOOL);

        // Hash password if it is provided
        if(!empty($this->password)) {
            $this->password_hash = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(':password_hash', $this->password_hash);
        }

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Delete user
    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE user_id = :user_id';
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $stmt->bindParam(':user_id', $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}
?>
