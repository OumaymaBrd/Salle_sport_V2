<?php
require_once 'User.php';

class Member extends User {
    private $reservation_table = "reservation";
    private $activity_table = "activite";

    public function addReservation($activity_id, $date_reservation, $status) {
        $query = "INSERT INTO " . $this->reservation_table . " 
                  SET user_id = :user_id, activite_id = :activity_id, 
                  date_reservation = :date_reservation, status = :status";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":activity_id", $activity_id);
        $stmt->bindParam(":date_reservation", $date_reservation);
        $stmt->bindParam(":status", $status);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function updateReservation($reservation_id, $new_date) {
        $query = "UPDATE " . $this->reservation_table . "
                  SET date_reservation = :new_date
                  WHERE id = :reservation_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":new_date", $new_date);
        $stmt->bindParam(":reservation_id", $reservation_id);
        $stmt->bindParam(":user_id", $this->id);

        return $stmt->execute();
    }

    public function deleteReservation($reservation_id) {
        $query = "DELETE FROM " . $this->reservation_table . "
                  WHERE id = :reservation_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":reservation_id", $reservation_id);
        $stmt->bindParam(":user_id", $this->id);

        return $stmt->execute();
    }

    public function getReservations() {
        $query = "SELECT r.*, a.nom_activite 
                  FROM " . $this->reservation_table . " r
                  JOIN " . $this->activity_table . " a ON r.activite_id = a.id
                  WHERE r.user= :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user", $this->id);
        $stmt->execute();

        return $stmt;
    }

    public function getActivities() {
        $query = "SELECT * FROM " . $this->activity_table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>

