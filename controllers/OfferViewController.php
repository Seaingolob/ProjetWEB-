<?php
class OfferViewController {
    private $view;
    private $offerModel;

    public function __construct($view) {
        $this->view = $view;
        $this->offerModel = new OfferModel();
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
    }

    public function voir() {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: /offres");
            exit();
        }
        $id_offre = intval($_GET['id']);

        $offre = $this->offerModel->getOfferDetails($id_offre, $_SESSION['user_id']);

        if (!$offre) {
            header("Location: /offres");
            exit();
        }

        // Récupérer les messages de succès ou erreur éventuels
        $success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
        $error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
        unset($_SESSION['success'], $_SESSION['error']);

        $this->view->render('offer/view', [
            'offre' => $offre['offre'],
            'competences' => $offre['competences'],
            'secteurs' => $offre['secteurs'],
            'postule' => $offre['postule'],
            'wishlist' => $offre['wishlist'],
            'evaluations' => $offre['evaluations'],
            'a_evalue' => $offre['a_evalue'],
            'userType' => $_SESSION['user_type'],
            'userId' => $_SESSION['user_id'],
            'userName' => $_SESSION['user_name'],
            'success' => $success,
            'error' => $error
        ]);
    }

    public function postuler() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /offres");
            exit();
        }
        if ($_SESSION['user_type'] !== 'etudiant') {
            header("Location: /main");
            exit();
        }

        $id_offre = intval($_POST['id'] ?? 0);
        $id_compte = $_SESSION['user_id'];

        $result = $this->offerModel->processApplication($id_offre, $id_compte, $_FILES);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        header("Location: /voir-offre?id=" . $id_offre);
        exit();
    }

    public function ajouterEvaluation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /offres");
            exit();
        }
        if ($_SESSION['user_type'] !== 'etudiant') {
            header("Location: /main");
            exit();
        }
        $id_offre = intval($_POST['id_offre'] ?? 0);
        $id_compte = $_SESSION['user_id'];
        $note = $_POST['note'] ?? '';
        $avis = $_POST['avis'] ?? '';

        $result = $this->offerModel->addEvaluation($id_offre, $id_compte, $note, $avis);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        header("Location: /voir-offre?id=" . $id_offre);
        exit();
    }
}