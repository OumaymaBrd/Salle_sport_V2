<?php
require_once 'User.php';

class Member extends User {
    public function getMemberInfo($matricule) {
        $query = "SELECT nom, prenom FROM " . $this->table_name . " WHERE matricule = :matricule";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActivities() {
        $query = "SELECT id, nom_activite, description FROM activite WHERE supprimer = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function addReservation($matricule, $activite_id) {
        // Vérifier si l'activité existe et s'il reste des places
        $queryActivite = "SELECT nom_activite, disponibilite, reste_place FROM activite WHERE id = :activite_id";
        $stmtActivite = $this->conn->prepare($queryActivite);
        $stmtActivite->bindParam(':activite_id', $activite_id);
        $stmtActivite->execute();
        $activite = $stmtActivite->fetch(PDO::FETCH_ASSOC);
    
        if (!$activite) {
            return ['status' => 'error', 'message' => 'Activité non trouvée'];
        }
    
        if ($activite['reste_place'] <= 0) {
            return ['status' => 'error', 'message' => 'Il n\'y a plus de places disponibles pour l\'activité ' . $activite['nom_activite'] . '. Veuillez choisir une autre activité.'];
        }
    
        // Vérifier si le membre a déjà réservé cette activité
        $queryExistingReservation = "SELECT id FROM reservation WHERE matricule = :matricule AND nom_activite = :nom_activite";
        $stmtExistingReservation = $this->conn->prepare($queryExistingReservation);
        $stmtExistingReservation->bindParam(':matricule', $matricule);
        $stmtExistingReservation->bindParam(':nom_activite', $activite['nom_activite']);
        $stmtExistingReservation->execute();
    
        if ($stmtExistingReservation->fetch()) {
            return ['status' => 'error', 'message' => 'Vous avez déjà réservé l\'activité ' . $activite['nom_activite'] . '. Veuillez choisir une autre activité.'];
        }
    
        // Commencer une transaction
        $this->conn->beginTransaction();
    
        try {
            // Insérer la réservation
            $queryInsert = "INSERT INTO reservation (matricule, date_reservation, status, nom_activite) 
                            VALUES (:matricule, :date, 'non confirme', :nom_activite)";
            
            $stmtInsert = $this->conn->prepare($queryInsert);
            $stmtInsert->bindParam(':matricule', $matricule);
            $stmtInsert->bindParam(':nom_activite', $activite['nom_activite']);
            
            $date = date('Y-m-d H:i:s');
            $stmtInsert->bindParam(':date', $date);
            
            $stmtInsert->execute();
    
            // Mettre à jour le nombre de places restantes
            $queryUpdate = "UPDATE activite SET reste_place = reste_place - 1 WHERE id = :activite_id";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->bindParam(':activite_id', $activite_id);
            $stmtUpdate->execute();
    
            // Valider la transaction
            $this->conn->commit();
    
            return [
                'status' => 'success',
                'message' => 'Réservation ajoutée avec succès pour l\'activité ' . $activite['nom_activite'],
                'data' => [
                    'id' => $this->conn->lastInsertId(),
                    'nom_activite' => $activite['nom_activite'],
                    'date_reservation' => $date,
                    'status' => 'non confirme'
                ]
            ];
        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $this->conn->rollBack();
            return ['status' => 'error', 'message' => 'Erreur lors de l\'ajout de la réservation: ' . $e->getMessage()];
        }
    }

    public function getReservations($matricule) {
        $query = "SELECT r.id, r.nom_activite, r.date_reservation, r.status 
                 FROM reservation r 
                 WHERE r.matricule = :matricule";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->execute();
        return $stmt;
    }

    public function cancelReservation($reservation_id, $matricule) {
        $query = "DELETE FROM reservation WHERE id = :id AND matricule = :matricule";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $reservation_id);
        $stmt->bindParam(':matricule', $matricule);
        
        if($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Réservation annulée avec succès'];
        }
        return ['status' => 'error', 'message' => 'Erreur lors de l\'annulation de la réservation'];
    }

    public function getActivitiesWithAvailability() {
        $query = "SELECT id, nom_activite, description, disponibilite, reste_place FROM activite WHERE supprimer = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    
}
?>

