<?php
// controllers/ApplyController.php

class ApplyController {
    private $view;
    private $applyModel;
    
    public function __construct($view) {
        $this->view = $view;
        $this->applyModel = new ApplyModel();
        
        // Vérifier l'authentification
        $this->checkAuthentication();
    }
    
    private function checkAuthentication() {
        // Vérifier si la session a expiré
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
            // La session a expiré, déconnecter l'utilisateur
            session_unset();
            session_destroy();
            header("Location: /connexion?expired=1");
            exit();
        }
        
        // Mettre à jour le timestamp de dernière activité
        $_SESSION['last_activity'] = time();
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header("Location: /connexion");
            exit();
        }
        
        // Vérifier que l'utilisateur est un étudiant
        if ($_SESSION['user_type'] !== 'etudiant') {
            header("Location: /main");
            exit();
        }
    }
    
    public function apply() {
        // Vérifier que la requête est une méthode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /main");
            exit();
        }
        
        // Vérifier que l'ID de l'offre est présent
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            $_SESSION['error_message'] = "Aucune offre spécifiée.";
            header("Location: /offres");
            exit();
        }
        
        $id_offre = intval($_POST['id']);
        $id_compte = $_SESSION['user_id'];
        
        // Vérifier si l'étudiant a déjà postulé à cette offre
        if ($this->applyModel->hasApplied($id_compte, $id_offre)) {
            $_SESSION['error_message'] = "Vous avez déjà postulé à cette offre.";
            header("Location: /voir-offre?id=" . $id_offre);
            exit();
        }
        
        // Vérifier les fichiers uploadés
        $cv_file = isset($_FILES['cv']) ? $_FILES['cv'] : null;
        $lm_file = isset($_FILES['lettre_motivation']) ? $_FILES['lettre_motivation'] : null;
        
        // Valider les fichiers
        $validation = $this->validateFiles($cv_file, $lm_file);
        if (!$validation['valid']) {
            $_SESSION['error_message'] = $validation['message'];
            header("Location: /voir-offre?id=" . $id_offre);
            exit();
        }
        
        // Traiter les fichiers et enregistrer la candidature
        $result = $this->applyModel->saveApplication($id_compte, $id_offre, $cv_file, $lm_file);
        
        if ($result) {
            $_SESSION['success_message'] = "Votre candidature a été enregistrée avec succès.";
        } else {
            $_SESSION['error_message'] = "Une erreur est survenue lors de l'enregistrement de votre candidature.";
        }
        
        // Rediriger vers la page de détail de l'offre
        header("Location: /voir-offre?id=" . $id_offre);
        exit();
    }
    
    private function validateFiles($cv_file, $lm_file) {
        // Vérifier que les fichiers sont présents
        if (!$cv_file || !$lm_file || $cv_file['error'] !== UPLOAD_ERR_OK || $lm_file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'message' => "Veuillez fournir à la fois un CV et une lettre de motivation."
            ];
        }
        
        // Vérifier les types de fichiers (PDF uniquement)
        $cv_type = mime_content_type($cv_file['tmp_name']);
        $lm_type = mime_content_type($lm_file['tmp_name']);
        
        if ($cv_type !== 'application/pdf' || $lm_type !== 'application/pdf') {
            return [
                'valid' => false,
                'message' => "Les fichiers doivent être au format PDF."
            ];
        }
        
        // Vérifier la taille des fichiers (max 5 Mo chacun)
        $max_size = 5 * 1024 * 1024; // 5 Mo
        if ($cv_file['size'] > $max_size || $lm_file['size'] > $max_size) {
            return [
                'valid' => false,
                'message' => "Les fichiers ne doivent pas dépasser 5 Mo."
            ];
        }
        
        return ['valid' => true];
    }
}