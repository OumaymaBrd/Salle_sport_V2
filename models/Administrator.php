<?php
require_once 'User.php';

class Administrator extends User {
    public function getDashboardData() {
        $data = array();

        // Get number of reservations
        $query = "SELECT COUNT(*) as count FROM reservation";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['reservations'] = $row['count'];

        // Get number of members
        $query = "SELECT COUNT(*) as count FROM user_ WHERE post = 'membre'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['members'] = $row['count'];

        // Get number of activities
        $query = "SELECT COUNT(*) as count FROM activite WHERE supprimer = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['activities'] = $row['count'];

        // Number of site visits (you need to implement a mechanism to track this)
        $data['visits'] = 0; // Placeholder

        return $data;
    }

    public function addActivity($nom_activite, $description) {
        $query = "INSERT INTO activite SET nom_activite = :nom_activite, matricule_administration = :matricule_administration, description = :description";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nom_activite", $nom_activite);
        $stmt->bindParam(":matricule_administration", $this->matricule);
        $stmt->bindParam(":description", $description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getReservations() {
        $query = "SELECT r.*, u.nom, u.prenom FROM reservation r JOIN user_ u ON r.id_membre = u.id WHERE r.status = 'non confirme'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateReservationStatus($reservation_id, $status) {
        $query = "UPDATE reservation SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $reservation_id);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>

