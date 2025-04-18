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

}