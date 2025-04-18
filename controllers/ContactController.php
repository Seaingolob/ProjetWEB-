<?php
// controllers/ContactController.php

class ContactController {
    private $view;
    
    public function __construct($view) {
        $this->view = $view;
        
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
        // Afficher la page de contact
        $this->view->render('contact/index', [
            'userId' => $_SESSION['user_id'],
            'userName' => $_SESSION['user_name'],
            'userType' => $_SESSION['user_type']
        ]);
    }
    
    public function processForm() {
        // Vérifier si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /contact');
            exit();
        }
        
        // Récupérer les données du formulaire
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $message = isset($_POST['message']) ? $_POST['message'] : '';
        
        // Valider les données
        $errors = [];
        
        if (empty($subject)) {
            $errors['subject'] = "Veuillez sélectionner un sujet";
        }
        
        if (empty($name)) {
            $errors['name'] = "Veuillez entrer votre nom";
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Veuillez entrer un email valide";
        }
        
        if (empty($message)) {
            $errors['message'] = "Veuillez entrer un message";
        } elseif (strlen($message) > 300) {
            $errors['message'] = "Votre message ne doit pas dépasser 300 caractères";
        }
        
        // S'il y a des erreurs, retourner à la page de contact avec les erreurs
        if (!empty($errors)) {
            $this->view->render('contact/index', [
                'errors' => $errors,
                'formData' => $_POST,
                'userId' => $_SESSION['user_id'],
                'userName' => $_SESSION['user_name'],
                'userType' => $_SESSION['user_type']
            ]);
            return;
        }
        
        // TODO: Traiter le formulaire (envoyer un email, sauvegarder dans la base de données, etc.)
        
        // Rediriger vers une page de confirmation
        $_SESSION['contact_success'] = true;
        header('Location: /contact-success');
        exit();
    }
    
    public function showSuccess() {
        // Vérifier si l'utilisateur vient bien d'envoyer un formulaire
        if (!isset($_SESSION['contact_success']) || $_SESSION['contact_success'] !== true) {
            header('Location: /contact');
            exit();
        }
        
        // Supprimer le flag de succès
        unset($_SESSION['contact_success']);
        
        // Afficher la page de succès
        $this->view->render('contact/success', [
            'userId' => $_SESSION['user_id'],
            'userName' => $_SESSION['user_name'],
            'userType' => $_SESSION['user_type']
        ]);
    }
}