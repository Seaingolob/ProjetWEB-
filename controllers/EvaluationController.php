<?php
// controllers/EvaluationController.php

class EvaluationController {
    private $view;
    private $evaluationModel;
    
    public function __construct($view) {
        $this->view = $view;
        $this->evaluationModel = new EvaluationModel();
        
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
    
    public function addEvaluation() {
        // Vérifier que la requête est une méthode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /main");
            exit();
        }
        
        // Vérifier que l'ID de l'offre est présent
        if (!isset($_POST['id_offre']) || empty($_POST['id_offre'])) {
            $_SESSION['error_message'] = "Aucune offre spécifiée.";
            header("Location: /offres");
            exit();
        }
        
        // Récupérer les données du formulaire
        $id_offre = intval($_POST['id_offre']);
        $id_compte = $_SESSION['user_id'];
        $note = isset($_POST['note']) ? $_POST['note'] : null;
        $avis = isset($_POST['avis']) ? trim($_POST['avis']) : '';
        
        // Valider la note
        if (!$this->validateNote($note)) {
            $_SESSION['error_message'] = "La note sélectionnée n'est pas valide.";
            header("Location: /voir-offre?id=" . $id_offre);
            exit();
        }
        
        // Vérifier si l'étudiant a déjà évalué cette offre
        if ($this->evaluationModel->hasEvaluated($id_compte, $id_offre)) {
            $_SESSION['error_message'] = "Vous avez déjà évalué cette offre.";
            header("Location: /voir-offre?id=" . $id_offre);
            exit();
        }
        
        // Enregistrer l'évaluation
        $result = $this->evaluationModel->saveEvaluation($id_compte, $id_offre, $note, $avis);
        
        if ($result) {
            $_SESSION['success_message'] = "Votre évaluation a été enregistrée avec succès.";
        } else {
            $_SESSION['error_message'] = "Une erreur est survenue lors de l'enregistrement de votre évaluation.";
        }
        
        // Rediriger vers la page de détail de l'offre
        header("Location: /voir-offre?id=" . $id_offre);
        exit();
    }
    
    private function validateNote($note) {
        $valid_notes = ['Excellent', 'Très bien', 'Bien', 'Moyen', 'À éviter'];
        return in_array($note, $valid_notes);
    }
}