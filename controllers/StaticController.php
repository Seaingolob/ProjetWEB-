<?php
class StaticController {
    private $view;
    public function __construct($view) { $this->view = $view; }

    public function faq() {
        $this->view->render('static/faq');
    }

    public function mentionsLegales() {
        $this->view->render('static/mentions_legales');
    }

    public function cgu() {
        $this->view->render('static/cgu');
    }

    public function qsn() {
        $this->view->render('static/qsn');
    }
}