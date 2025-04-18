<?php

class ContactController {
    private $view;

    public function __construct($view) {
        $this->view = $view;
        $this->checkAuthentication();
    }

    private function checkAuthentication() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header("Location: /connexion");
            exit();
        }
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
            session_unset();
            session_destroy();
            header("Location: /connexion?expired=1");
            exit();
        }
        $_SESSION['last_activity'] = time();
    }

    public function form() {
        $params = [
            'userId'    => $_SESSION['user_id'],
            'userName'  => $_SESSION['user_name'],
            'userType'  => $_SESSION['user_type'],
            'success'   => isset($_GET['success']),
            'error'     => isset($_GET['error']) ? $_GET['error'] : null
        ];
        $this->view->render('contact/form', $params);
    }

    public function process() {
        // Vérifie la méthode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /contact?error=method_not_allowed');
            exit();
        }

        // Récupère les données du formulaire
        $subject = $_POST['subject'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $message = $_POST['message'] ?? '';

        // Validation rapide
        if (empty($subject) || empty($name) || empty($email) || empty($message)) {
            header('Location: /contact?error=empty_fields');
            exit();
        }

        // Préparer le contenu du fichier
        $subjectLabels = [
            'info'        => "Demande d'information",
            'problem'     => "Signaler un problème",
            'partnership' => "Proposition de partenariat",
            'other'       => "Autre"
        ];
        $subjectText = $subjectLabels[$subject] ?? $subject;

        $content  = "Date: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Sujet: " . $subjectText . "\n";
        $content .= "Nom: " . $name . "\n";
        $content .= "Email: " . $email . "\n";
        $content .= "Message:\n" . $message . "\n";
        $content .= "------------------------------------------------\n";

        // Répertoire d'upload
        $uploadDir = __DIR__ . "/../uploads/contacts/";

        // Créer le dossier si besoin
        if (!is_dir($uploadDir)) {
            $success = mkdir($uploadDir, 0777, true);
            if (!$success) {
                header('Location: /contact?error=directory_creation_failed');
                exit();
            }
        }

        // Fonction pour nettoyer le nom de fichier
        function sanitizeFileName($string) {
            $accents = [
                'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','æ'=>'ae',
                'ç'=>'c','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ì'=>'i','í'=>'i',
                'î'=>'i','ï'=>'i','ð'=>'o','ñ'=>'n','ò'=>'o','ó'=>'o','ô'=>'o',
                'õ'=>'o','ö'=>'o','ø'=>'o','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ý'=>'y','ÿ'=>'y'
            ];
            $string = strtolower(strtr($string, $accents));
            $string = str_replace(' ', '_', $string);
            return preg_replace('/[^a-z0-9_]/', '', $string);
        }

        $cleanName = sanitizeFileName(strtolower($name));
        $cleanSubject = sanitizeFileName($subject);
        $date = date('Y-m-d');
        $uniqueId = substr(uniqid(), -6);
        $filename = "{$cleanName}_{$date}_{$cleanSubject}_{$uniqueId}.txt";
        $filepath = $uploadDir . $filename;

        try {
            $success = file_put_contents($filepath, $content);
            if ($success === false) throw new Exception("Échec de l'écriture du fichier");

            header('Location: /contact?success=1');
            exit();
        } catch (Exception $e) {
            header('Location: /contact?error=save_error');
            exit();
        }
    }
}