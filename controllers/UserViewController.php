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
            echo "ID non fourni dans l'URL!";
            exit();
            // header("Location: /main");
            // exit();
        }
        
        // DÉFINIR L'ID CORRECTEMENT
        $id_compte = $_GET['id'];

        
        $userData = $this->userModel->getUserInfo($id_compte);

        // Récupérer le type de l'utilisateur affiché
        $authModel = new AuthModel();
        $displayedUserType = $authModel->get_user_type($id_compte);
        
        $viewData = [
            'user' => $userData['user'],
            'user_type' => $displayedUserType, // <-- on met le type ici
            'specific_info' => $userData['specific_info'],
            'currentUser' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'type' => $_SESSION['user_type']  // type de l'utilisateur connecté
            ]
        ];
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