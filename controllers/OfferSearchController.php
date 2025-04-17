<?php
// controllers/OfferSearchController.php

class OfferSearchController {
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
    
    public function index() {
        // Paramètres de pagination
        $itemsPerPage = 5;
        $page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
        
        // Récupérer les paramètres de recherche
        $searchCompany = isset($_GET['company-name']) ? $_GET['company-name'] : '';
        $searchLocation = isset($_GET['location']) ? $_GET['location'] : '';
        $searchCompetences = isset($_GET['competences']) ? array_filter($_GET['competences']) : [];
        
        // Récupérer les listes pour les filtres
        $companies = $this->offerModel->getAllCompanies();
        $cities = $this->offerModel->getAllCities();
        $competences = $this->offerModel->getAllCompetences();
        
        // Récupérer les offres filtrées
        $searchResults = $this->offerModel->searchOffers(
            $searchCompany, 
            $searchLocation, 
            $searchCompetences, 
            $page, 
            $itemsPerPage
        );
        
        // Récupérer les offres likées par l'utilisateur (pour les cœurs)
        $likedOffers = [];
        if ($_SESSION['user_type'] === 'etudiant') {
            $likedOffers = $this->offerModel->getUserLikedOffers($_SESSION['user_id']);
        }
        
        // Construire les données pour la vue
        $viewData = [
            'companies' => $companies,
            'cities' => $cities,
            'allCompetences' => $competences,
            'searchCompany' => $searchCompany,
            'searchLocation' => $searchLocation,
            'searchCompetences' => $searchCompetences,
            'offres' => $searchResults['offres'],
            'totalOffres' => $searchResults['total'],
            'totalPages' => $searchResults['totalPages'],
            'page' => $page,
            'likedOffers' => $likedOffers,
            'userType' => $_SESSION['user_type'],
            'userId' => $_SESSION['user_id'],
            'userName' => $_SESSION['user_name']
        ];
        
        // Rendre la vue
        $this->view->render('offer/search', $viewData);
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