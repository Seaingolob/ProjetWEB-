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
        // Code d'authentification (déjà implémenté)
    }
    
    public function index() {
        // Code d'affichage de la page de contact (déjà implémenté)
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
        
        // ----- Code récupéré de processContact.php -----
        
        // Préparer le contenu du fichier
        $subjectLabels = [
            'info' => 'Demande d\'information',
            'problem' => 'Signaler un problème',
            'partnership' => 'Proposition de partenariat',
            'other' => 'Autre'
        ];

        $subjectText = isset($subjectLabels[$subject]) ? $subjectLabels[$subject] : $subject;

        $content = "Date: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Sujet: " . $subjectText . "\n";
        $content .= "Nom: " . $name . "\n";
        $content .= "Email: " . $email . "\n";
        $content .= "Message:\n" . $message . "\n";
        $content .= "------------------------------------------------\n";

        // Définir le répertoire d'upload (en utilisant le chemin du projet)
        $uploadDir = dirname(dirname(__FILE__)) . "/uploads/contacts/";

        // Journaliser l'action pour débogage
        error_log("Tentative de création du répertoire: " . $uploadDir);

        // Vérifier si le répertoire existe, sinon le créer
        if (!is_dir($uploadDir)) {
            $success = mkdir($uploadDir, 0777, true);
            error_log("Création du répertoire: " . ($success ? "Réussie" : "Échec"));
            
            if (!$success) {
                $errors['system'] = "Impossible de créer le répertoire pour sauvegarder les messages";
                $this->view->render('contact/index', [
                    'errors' => $errors,
                    'formData' => $_POST,
                    'userId' => $_SESSION['user_id'],
                    'userName' => $_SESSION['user_name'],
                    'userType' => $_SESSION['user_type']
                ]);
                return;
            }
        }

        // Fonction pour nettoyer les caractères spéciaux dans un nom de fichier
        function sanitizeFileName($string) {
            // Version simplifiée sans dépendance à l'extension intl
            $accents = array(
                'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'ae',
                'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
                'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o',
                'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u',
                'ý'=>'y', 'ÿ'=>'y'
            );
            $string = strtolower(strtr($string, $accents));
            $string = str_replace(' ', '_', $string);
            return preg_replace('/[^a-z0-9_]/', '', $string);
        }

        // Créer un nom de fichier unique
        $cleanName = sanitizeFileName(strtolower($name));
        $cleanSubject = sanitizeFileName($subject);
        $date = date('Y-m-d');
        $uniqueId = substr(uniqid(), -6);

        $filename = "{$cleanName}_{$date}_{$cleanSubject}_{$uniqueId}.txt";
        $filepath = $uploadDir . $filename;

        // Journaliser le chemin du fichier pour débogage
        error_log("Tentative d'écriture dans le fichier: " . $filepath);

        try {
            // Enregistrer le fichier
            $success = file_put_contents($filepath, $content);
            
            // Vérifier si l'enregistrement a réussi
            if ($success === false) {
                throw new Exception("Échec de l'écriture du fichier");
            }
            
            // Rediriger vers la page de confirmation
            $_SESSION['contact_success'] = true;
            header('Location: /contact-success');
            exit();
            
        } catch (Exception $e) {
            error_log("Erreur lors du traitement du formulaire de contact: " . $e->getMessage());
            
            $errors['system'] = "Une erreur est survenue lors de l'enregistrement du message. Veuillez réessayer.";
            $this->view->render('contact/index', [
                'errors' => $errors,
                'formData' => $_POST,
                'userId' => $_SESSION['user_id'],
                'userName' => $_SESSION['user_name'],
                'userType' => $_SESSION['user_type']
            ]);
            return;
        }
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
    