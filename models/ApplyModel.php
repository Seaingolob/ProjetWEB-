<?php
// models/ApplyModel.php

class ApplyModel {
    private $connexion;
    
    public function __construct() {
        // Inclure config et se connecter à la BD
        require_once __DIR__ . '/../config/config.php';
        $this->connexion = $connexion; // Supposant que $connexion vient de config.php
    }
    
    public function hasApplied($id_compte, $id_offre) {
        try {
            $stmt = $this->connexion->prepare("SELECT COUNT(*) FROM postuler WHERE id_compte = :id_compte AND id_offre = :id_offre");
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de candidature: " . $e->getMessage());
            return false;
        }
    }
    
    public function saveApplication($id_compte, $id_offre, $cv_file, $lm_file) {
        try {
            // Créer le dossier de stockage des fichiers s'il n'existe pas
            $upload_dir = dirname(__DIR__) . '/uploads/candidatures/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Générer des noms de fichiers uniques
            $timestamp = time();
            $cv_filename = $id_compte . '_' . $id_offre . '_cv_' . $timestamp . '.pdf';
            $lm_filename = $id_compte . '_' . $id_offre . '_lm_' . $timestamp . '.pdf';
            
            // Déplacer les fichiers vers le dossier de stockage
            $cv_path = $upload_dir . $cv_filename;
            $lm_path = $upload_dir . $lm_filename;
            
            if (!move_uploaded_file($cv_file['tmp_name'], $cv_path) || 
                !move_uploaded_file($lm_file['tmp_name'], $lm_path)) {
                error_log("Erreur lors du déplacement des fichiers uploadés");
                return false;
            }
            
            // Enregistrer la candidature dans la base de données
            $date = date('Y-m-d H:i:s');
            $stmt = $this->connexion->prepare("INSERT INTO postuler (id_compte, id_offre, date_postulation, cv_path, lm_path) 
                                              VALUES (:id_compte, :id_offre, :date, :cv_path, :lm_path)");
            
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':cv_path', $cv_filename, PDO::PARAM_STR);
            $stmt->bindParam(':lm_path', $lm_filename, PDO::PARAM_STR);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Erreur lors de l'enregistrement de la candidature: " . $e->getMessage());
            return false;
        }
    }
}