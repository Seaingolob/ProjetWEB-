<?php
class UserController {
    private $view;
    private $userModel;

    public function __construct($view) {
        $this->view = $view;
        $this->userModel = new UserModel(); // à créer aussi (voir plus bas)
        $this->checkAuthentication();
    }

    private function checkAuthentication() {

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
            session_unset();
            session_destroy();
            header("Location: /connexion?expired=1");
            exit();
        }
        $_SESSION['last_activity'] = time();
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header("Location: /connexion");
            exit();
        }
        if ($_SESSION['user_type'] == 'etudiant') {
            header("Location: /main");
            exit();
        }
    }

    public function showForm() {
        $data = $this->userModel->getFormOptions();
        $data['userType'] = $_SESSION['user_type'];
        $this->view->render('user/add', $data);
    }

    public function processForm() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $result = $this->userModel->addUser($_POST);
            if ($result['success']) {
                $_SESSION['message'] = "Utilisateur ajouté avec succès !";
                header("Location: /admin");
                exit();
            } else {
                $_SESSION['message'] = "Erreur: " . $result['error'];
                header("Location: /formulaire-utilisateur");
                exit();
            }
        } else {
            header("Location: /formulaire-utilisateur");
            exit();
        }
    }
}