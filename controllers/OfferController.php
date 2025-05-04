<?php
class OfferController {
    private $view;
    private $offerModel;

    public function __construct($view) {
        $this->view = $view;
        $this->offerModel = new OfferModel();
        $this->checkAuthentication();
    }

    private function checkAuthentication() {
        session_start();
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
        if ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'pilote') {
            header("Location: /main");
            exit();
        }
    }

    public function showForm() {
        $data = $this->offerModel->getFormOptions();
        $data['userType'] = $_SESSION['user_type'];
        $this->view->render('offer/add', $data);
    }

    public function processForm() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $result = $this->offerModel->addOffer($_POST, $_SESSION['user_id']);
            if ($result['success']) {
                $_SESSION['message'] = "Offre ajoutée avec succès !";
                header("Location: /admin");
                exit();
            } else {
                $_SESSION['message'] = "Erreur: " . $result['error'];
                header("Location: /ajouter-offre");
                exit();
            }
        } else {
            header("Location: /ajouter-offre");
            exit();
        }
    }
}