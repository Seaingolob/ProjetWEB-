<?php
// controllers/WishlistController.php

class WishlistController {
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
        
        // Vérifier que l'utilisateur est un étudiant
        if ($_SESSION['user_type'] !== 'etudiant') {
            // Rediriger vers la page principale si ce n'est pas un étudiant
            header("Location: /main");
            exit();
        }
    }
    
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // Récupérer les offres de la wishlist
        $offres = $this->offerModel->getWishlistOffers($userId);
        
        // Préparer les données pour la vue
        $viewData = [
            'offres' => $offres,
            'userId' => $userId,
            'userName' => $_SESSION['user_name'],
            'count' => count($offres)
        ];
        
        // Rendre la vue
        $this->view->render('wishlist/index', $viewData);
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