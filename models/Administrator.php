<?php
require_once 'User.php';

class Administrator extends User {
    public function getDashboardData() {
        $data = array();

        // Nombre de réservations
        $query = "SELECT COUNT(*) as count FROM reservation";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['reservations'] = $row['count'];

        // Nombre de membres
        $query = "SELECT COUNT(*) as count FROM user_ WHERE post = 'membre'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['members'] = $row['count'];

        // Nombre d'administrateurs
        $query = "SELECT COUNT(*) as count FROM user_ WHERE post = 'administration'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['administrators'] = $row['count'];

        // Nombre d'activités
        $query = "SELECT COUNT(*) as count FROM activite WHERE supprimer = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['activities'] = $row['count'];

        // Nombre de visites (basé sur le nombre total d'utilisateurs comme approximation)
        $query = "SELECT COUNT(*) as count FROM user_";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['visits'] = $row['count'];

        return $data;
    }

    public function addActivity($nom_activite, $description, $matricule_administration) {
        try {
            $query = "INSERT INTO activite (nom_activite, description, matricule_administration) 
                      VALUES (:nom_activite, :description, :matricule_administration)";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':nom_activite', $nom_activite);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':matricule_administration', $matricule_administration);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            } else {
                error_log("Erreur SQL: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Erreur PDO: " . $e->getMessage());
            return false;
        }
    }

    public function getAllReservations() {
        $query = "SELECT r.*, u.nom, u.prenom, a.nom_activite 
                 FROM reservation r 
                 JOIN user_ u ON r.id = u.id 
                 JOIN activite a ON r.id= a.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateReservationStatus($reservation_id, $status) {
        $query = "UPDATE reservation SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $reservation_id);
        return $stmt->execute();
    }

    public function getConfirmedActivities() {
        $query = "SELECT a.*, u.nom, u.prenom 
                 FROM activite a 
                 JOIN user_ u ON a.matricule_administration = u.matricule 
                 WHERE a.supprimer = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateActivity($id, $nom_activite, $description) {
        $query = "UPDATE activite SET nom_activite = :nom_activite, description = :description WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nom_activite", $nom_activite);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function deleteActivity($id) {
        $query = "UPDATE activite SET supprimer = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>

