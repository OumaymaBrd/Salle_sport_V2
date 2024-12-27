<?php
class User {
    protected $conn;
    protected $table_name = "user_";

    public $id;
    public $matricule;
    public $post;
    public $email;
    public $tel;
    public $nom;
    public $prenom;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function generateMatricule() {
        return 'AV' . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . " SET matricule=:matricule, post=:post, email=:email, tel=:tel, nom=:nom, prenom=:prenom, password=:password";
        $stmt = $this->conn->prepare($query);

        $this->matricule = $this->generateMatricule();
        $this->post = htmlspecialchars(strip_tags($this->post));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->tel = htmlspecialchars(strip_tags($this->tel));
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":matricule", $this->matricule);
        $stmt->bindParam(":post", $this->post);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":tel", $this->tel);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":password", $this->password);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && password_verify($this->password, $row['password'])) {
            $this->id = $row['id'];
            $this->matricule = $row['matricule'];
            $this->post = $row['post'];
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            return true;
        }
        return false;
    }


    
}



?>