<?php
// controllers/AuthController.php

class AuthController {
    private $view;
    private $authModel;
    
    public function __construct($view) {
        $this->view = $view;
        $this->authModel = new AuthModel();
    }
    
    public function login() {
        // Rediriger si l'utilisateur est déjà connecté
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header("Location: /main");
            exit();
        }
        
        // Variables pour la vue
        $viewData = [
            'error_message' => isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null
        ];
        
        // Supprimer le message d'erreur de la session après l'avoir récupéré
        if (isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }
        
        // Afficher la page de connexion
        $this->view->render('auth/login', $viewData);
    }
    
    public function loginProcess() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifiant = isset($_POST['identifiant']) ? $_POST['identifiant'] : '';
            $motdepasse = isset($_POST['motdepasse']) ? $_POST['motdepasse'] : '';
            
            // Validation des entrées
            if (empty($identifiant) || empty($motdepasse)) {
                $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
                header("Location: /connexion");
                exit();
            }
            
            // Tentative de connexion
            $user = $this->authModel->verifyUser($identifiant, $motdepasse);
            
            if ($user) {
                // Connexion réussie
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id_compte'];
                $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_type'] = $user['type_compte'];
                $_SESSION['last_activity'] = time();
                
                header("Location: /main");
                exit();
            } else {
                // Échec de connexion
                $_SESSION['error_message'] = "Identifiant ou mot de passe incorrect.";
                header("Location: /connexion");
                exit();
            }
        } else {
            // Si quelqu'un essaie d'accéder directement à login_process.php
            header("Location: /connexion");
            exit();
        }
    }
    
    public function logout() {
        // Détruire la session
        session_unset();
        session_destroy();
        
        // Rediriger vers la page de connexion
        header("Location: /connexion");
        exit();
    }
}