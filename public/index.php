<?php
// public/index.php

// Inclure l'autoloader de Composer
require_once '../vendor/autoload.php';

// Charger nos propres classes
spl_autoload_register(function($class) {
    // Convertir les noms de classe en chemins de fichiers
    if (strpos($class, 'Controller') !== false) {
        include '../controllers/' . $class . '.php';
    } elseif (strpos($class, 'Model') !== false) {
        include '../models/' . $class . '.php';
    } else {
        include '../core/' . $class . '.php';
    }
});

// Démarrer la session
session_start();

// Initialiser le routeur
$router = new Router();

// Définir les routes
$router->add('admin', 'AdminController', 'index');
$router->add('main', 'MainController', 'index');
$router->add('connexion', 'AuthController', 'login');
$router->add('logout', 'AuthController', 'logout');
$router->add('connexion', 'AuthController', 'login');
$router->add('login-process', 'AuthController', 'loginProcess');
$router->add('logout', 'AuthController', 'logout');
$router->add('main', 'MainController', 'index');
$router->add('toggle-like', 'MainController', 'toggleLike');
$router->add('', 'MainController', 'index'); // Route par défaut
$router->add('wishlist', 'WishlistController', 'index');
$router->add('offres', 'OfferSearchController', 'index');
$router->add('voir-eleve', 'UserViewController', 'viewUser');
$router->add('delete-user', 'UserViewController', 'deleteUser');
$router->add('voir-offre', 'OfferViewController', 'viewOffer');
$router->add('delete-offer', 'OfferViewController', 'deleteOffer');
$router->add('postuler', 'ApplyController', 'apply');
$router->add('ajouter-evaluation', 'EvaluationController', 'addEvaluation');
// ... autres routes

// Dispatcher la requête
$router->dispatch($_SERVER['REQUEST_URI']);