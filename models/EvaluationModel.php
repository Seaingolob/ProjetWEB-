<?php
// models/EvaluationModel.php

class EvaluationModel {
    private $connexion;
    
    public function __construct() {
        // Inclure config et se connecter à la BD
        require_once 'config/config.php';
        $this->connexion = $connexion; // Supposant que $connexion vient de config.php
    }
    
    public function hasEvaluated($id_compte, $id_offre) {
        try {
            $stmt = $this->connexion->prepare("SELECT COUNT(*) FROM evaluation WHERE id_compte = :id_compte AND id_offre = :id_offre");
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification d'évaluation: " . $e->getMessage());
            return false;
        }
    }
    
    public function saveEvaluation($id_compte, $id_offre, $note, $avis) {
        try {
            $stmt = $this->connexion->prepare("INSERT INTO evaluation (id_compte, id_offre, note, avis, date_evaluation) 
                                              VALUES (:id_compte, :id_offre, :note, :avis, NOW())");
            
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre, PDO::PARAM_INT);
            $stmt->bindParam(':note', $note, PDO::PARAM_STR);
            $stmt->bindParam(':avis', $avis, PDO::PARAM_STR);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Erreur lors de l'enregistrement de l'évaluation: " . $e->getMessage());
            return false;
        }
    }
}