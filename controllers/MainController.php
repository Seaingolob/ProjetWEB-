<?php
// controllers/MainController.php

class MainController {
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
            // Rediriger vers la page de connexion
            header("Location: /connexion");
            exit();
        }
    }
    
    public function index() {
        // Récupérer les offres en vedette
        $offres = $this->offerModel->getFeaturedOffers();
        
        // Vérifier les offres likées par l'utilisateur si c'est un étudiant
        $likedOffers = [];
        if ($_SESSION['user_type'] === 'etudiant' && !empty($offres)) {
            $likedOffers = $this->offerModel->getUserLikedOffers($_SESSION['user_id']);
        }
        
        // Préparer les données pour la vue
        $viewData = [
            'offres' => $offres,
            'likedOffers' => $likedOffers,
            'userType' => $_SESSION['user_type'],
            'userId' => $_SESSION['user_id'],
            'userName' => $_SESSION['user_name']
        ];
        
        // Rendre la vue
        $this->view->render('main/index', $viewData);
    }
    
    public function toggleLike() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offerId'])) {
            $offerId = (int)$_POST['offerId'];
            $userId = $_SESSION['user_id'];
            
            if ($_SESSION['user_type'] === 'etudiant') {
                $result = $this->offerModel->toggleLike($userId, $offerId);
                echo json_encode(['success' => true, 'liked' => $result]);
                exit();
            }
        }
        
        echo json_encode(['success' => false]);
        exit();
    }
}