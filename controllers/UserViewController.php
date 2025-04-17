<?php
// controllers/UserViewController.php

class UserViewController {
    private $view;
    private $userModel;
    
    public function __construct($view) {
        $this->view = $view;
        $this->userModel = new UserModel();
        
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
    
    public function viewUser() {
        // Vérifier si l'ID de l'utilisateur est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: /main");
            exit();
        }
        
        // DÉFINIR L'ID AVANT DE L'UTILISER !
        $id_compte = $_GET['id'];
        
        // Récupérer les informations de l'utilisateur (UNE SEULE FOIS)
        $userData = $this->userModel->getUserInfo($id_compte);
        
        // Pour le débogage
        echo "<pre>"; 
        print_r($userData); 
        echo "</pre>";
        
        // Vérifier si l'utilisateur existe
        if (!$userData['user']) {
            echo "Utilisateur non trouvé!";
            exit();
            // header("Location: /main");
            // exit();
        }
        
        // Préparer les données pour la vue
        $viewData = [
            'user' => $userData['user'],
            'user_type' => $userData['user_type'],
            'specific_info' => $userData['specific_info'],
            'currentUser' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'type' => $_SESSION['user_type']
            ]
        ];
        
        // Rendre la vue
        $this->view->render('user/view', $viewData);
    }
    
    public function deleteUser() {
        // Vérifier que l'utilisateur est admin
        if ($_SESSION['user_type'] !== 'admin') {
            header("Location: /main");
            exit();
        }
        
        // Vérifier si l'ID est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: /main");
            exit();
        }
        
        $id = $_GET['id'];
        
        // Supprimer l'utilisateur
        $this->userModel->deleteUser($id);
        
        // Rediriger vers la page d'admin
        header("Location: /admin?tab=utilisateur");
        exit();
    }
}