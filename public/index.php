<?php
// public/index.php

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si le fichier autoload.php existe
$autoloadPath = '../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // Si Composer n'est pas configuré, utilise uniquement l'autoloader manuel
    echo "Note: Composer autoload non trouvé. Utilisation de l'autoloader manuel uniquement.<br>";
}

// Charger nos propres classes
spl_autoload_register(function($class) {
    // Convertir les noms de classe en chemins de fichiers
    if (strpos($class, 'Controller') !== false) {
        $file = '../controllers/' . $class . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    } elseif (strpos($class, 'Model') !== false) {
        $file = '../models/' . $class . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    } else {
        $file = '../core/' . $class . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    }
    
    echo "Impossible de charger la classe: $class<br>";
});

// Démarrer la session
session_start();

// Vérifier si la classe Router existe
if (!class_exists('Router')) {
    die("Erreur: La classe Router n'existe pas ou n'est pas correctement chargée.");
}

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
$router->add('contact', 'ContactController', 'form');
$router->add('contact-process', 'ContactController', 'process');
$router->add('voir-promo', 'PromoController', 'voir');
$router->add('faq', 'StaticController', 'faq');
$router->add('cgu', 'StaticController', 'cgu');
$router->add('mentions-legales', 'StaticController', 'mentionsLegales');
$router->add('qsn', 'StaticController', 'qsn');
$router->add('404', 'ErrorController', 'notFound');
// ... autres routes

// Dispatcher la requête
$router->dispatch($_SERVER['REQUEST_URI']);