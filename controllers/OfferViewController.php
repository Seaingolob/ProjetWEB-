<?php
// controllers/OfferViewController.php

class OfferViewController {
    private $view;
    private $offerModel;
    
    public function __construct($view) {
        $this->view = $view;
        $this->offerModel = new OfferModel();
        
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
    }
    
    public function viewOffer() {
        // Vérifier si l'ID de l'offre est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: /offres");
            exit();
        }
        
        $id_offre = intval($_GET['id']);
        
        // Récupérer les détails de l'offre
        $offerData = $this->offerModel->getOfferDetails($id_offre, $_SESSION['user_id']);
        
        // Vérifier si l'offre existe
        if (!$offerData['offre']) {
            header("Location: /offres");
            exit();
        }
        
        // Préparer les données pour la vue
        $viewData = [
            'offre' => $offerData['offre'],
            'competences' => $offerData['competences'],
            'secteurs' => $offerData['secteurs'],
            'postule' => $offerData['postule'],
            'wishlist' => $offerData['wishlist'],
            'evaluations' => $offerData['evaluations'],
            'a_evalue' => $offerData['a_evalue'],
            'userType' => $_SESSION['user_type'],
            'userId' => $_SESSION['user_id'],
            'userName' => $_SESSION['user_name']
        ];
        
        // Rendre la vue
        $this->view->render('offer/view', $viewData);
    }
    
    public function deleteOffer() {
        // Vérifier que l'utilisateur est admin
        if ($_SESSION['user_type'] !== 'admin') {
            header("Location: /main");
            exit();
        }
        
        // Vérifier si l'ID est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: /offres");
            exit();
        }
        
        $id = intval($_GET['id']);
        
        // Supprimer l'offre
        $this->offerModel->deleteOffer($id);
        
        // Rediriger vers la liste des offres
        header("Location: /offres");
        exit();
    }

    public function apply() {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header("Location: /connexion");
            exit();
        }
        
        // Vérifier que l'utilisateur est un étudiant
        if ($_SESSION['user_type'] !== 'etudiant') {
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
        if ($this->offerModel->hasApplied($id_compte, $id_offre)) {
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
        $result = $this->offerModel->saveApplication($id_compte, $id_offre, $cv_file, $lm_file);
        
        if ($result) {
            $_SESSION['success_message'] = "Votre candidature a été enregistrée avec succès.";
        } else {
            $_SESSION['error_message'] = "Une erreur est survenue lors de l'enregistrement de votre candidature.";
        }
        
        // Rediriger vers la page de détail de l'offre
        header("Location: /voir-offre?id=" . $id_offre);
        exit();
    }



}