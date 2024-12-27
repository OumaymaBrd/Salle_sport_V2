<?php
class User {
    private $conn;
    private $table = "user_";

    public $id;
    public $matricule;
    public $post;
    public $email;
    public $password;
    public $nom;
    public $prenom;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        // Prepare query to fetch user by email
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email AND supprimer = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "User found in database.";

            // Verify password
            if (password_verify($this->password, $row['password'])) {
                // Set user properties for session storage
                $this->id = $row['id'];
                $this->matricule = $row['matricule'];
                $this->post = $row['post'];
                $this->nom = $row['nom'];
                $this->prenom = $row['prenom'];
                return true;
            }
        }

        return false; // Invalid credentials
    }
}
?>
