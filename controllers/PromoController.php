<?php
class PromoController {
    private $view;
    private $promoModel;

    public function __construct($view) {
        $this->view = $view;
        $this->promoModel = new PromoModel();
        $this->checkAuthentication();
    }

    private function checkAuthentication() {


        if (!isset($_GET['id_promotion'])) {
            die("ID de promotion manquant.");
        }

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

    public function voir() {
        $id_promotion = intval($_GET['id_promotion']);
        $data = $this->promoModel->getPromoStats($id_promotion);

        if (!$data['promotion']) {
            die("Promotion non trouvée.");
        }
        if (!$data['pilote']) {
            die("Pilote non trouvé.");
        }

        $viewData = [
            'promotion' => $data['promotion'],
            'nb_etudiants' => $data['nb_etudiants'],
            'pilote' => $data['pilote'],
            'moyenne_postulations' => $data['moyenne_postulations'],
            'etudiants_sans_postulation' => $data['etudiants_sans_postulation'],
            'userId' => $_SESSION['user_id'],
            'userName' => $_SESSION['user_name'],
            'userType' => $_SESSION['user_type'],
        ];

        $this->view->render('promo/view', $viewData);
    }
}