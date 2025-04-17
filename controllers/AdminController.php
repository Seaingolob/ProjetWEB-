<?php
// controllers/AdminController.php
class AdminController {
    private $userModel;
    private $offerModel;
    private $view;
    
    public function __construct($view) {
        $this->view = $view;
        $this->userModel = new UserModel();
        $this->offerModel = new OfferModel();
        
        // Vérifier l'authentification
        $this->checkAuthentication();
    }
    
    private function checkAuthentication() {
        // Vérifier si la session a expiré
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
            session_unset();
            session_destroy();
            header("Location: /connexion?expired=1");
            exit();
        }
        
        // Mettre à jour le timestamp
        $_SESSION['last_activity'] = time();
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header("Location: /connexion");
            exit();
        }
        
        // Vérifier le rôle
        if ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'pilote') {
            header("Location: /main");
            exit();
        }
    }
    
    public function index() {
        // Obtenir l'onglet actif, par défaut 'utilisateur'
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'utilisateur';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Gérer les actions
        if (isset($_GET['action']) && isset($_GET['id'])) {
            $this->handleActions($_GET['action'], $_GET['id'], $tab);
        }
        
        // Données communes pour la vue
        $viewData = [
            'tab' => $tab,
            'search' => $search,
        ];
        
        // Ajouter les données spécifiques selon l'onglet
        if ($tab === 'utilisateur') {
            $userData = $this->getUserData($search);
            $viewData = array_merge($viewData, $userData);
        } else {
            $offerData = $this->getOfferData($search);
            $viewData = array_merge($viewData, $offerData);
        }
        
        // Rendre la vue dashboard
        $this->view->render('admin/dashboard', $viewData);
    }
    
    private function handleActions($action, $id, $tab) {
        if ($action === 'delete_user' && $_SESSION['user_type'] === 'admin') {
            $this->userModel->deleteUser($id);
        } elseif ($action === 'delete_offer' && $_SESSION['user_type'] === 'admin') {
            $this->offerModel->deleteOffer($id);
        }
        
        // Rediriger pour éviter la répétition
        header("Location: /admin?tab=" . $tab);
        exit();
    }
    
    private function getUserData($search) {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $itemsPerPage = 4;
        
        // Récupérer les utilisateurs
        $data = $this->userModel->getUsers($search, $page, $itemsPerPage);
        
        return [
            'utilisateurs' => $data['users'],
            'page' => $page,
            'totalPages' => $data['totalPages']
        ];
    }
    
    private function getOfferData($search) {
        $page = isset($_GET['pageOffres']) ? (int)$_GET['pageOffres'] : 1;
        $itemsPerPage = 4;
        
        // Récupérer les offres
        $data = $this->offerModel->getOffers($search, $page, $itemsPerPage);
        
        return [
            'offres' => $data['offers'],
            'page' => $page,
            'totalPages' => $data['totalPages']
        ];
    }
}