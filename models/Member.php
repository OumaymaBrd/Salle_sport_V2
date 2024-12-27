<?php
require_once 'User.php';

class Member extends User {
    public function getActivities() {
        $query = "SELECT * FROM activite WHERE supprimer = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function makeReservation($activite_id) {
        $query = "INSERT INTO reservation SET id_membre = :id_membre, matricule = :matricule, date_reservation = :date_reservation, status = 'non confirme', nom_activite = (SELECT nom_activite FROM activite WHERE id = :activite_id)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id_membre", $this->id);
        $stmt->bindParam(":matricule", $this->matricule);
        $stmt->bindParam(":date_reservation", date('Y-m-d H:i:s'));
        $stmt->bindParam(":activite_id", $activite_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getReservations() {
        $query = "SELECT * FROM reservation WHERE id_membre = :id_membre";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_membre", $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function cancelReservation($reservation_id) {
        $query = "DELETE FROM reservation WHERE id = :id AND id_membre = :id_membre";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $reservation_id);
        $stmt->bindParam(":id_membre", $this->id);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>

