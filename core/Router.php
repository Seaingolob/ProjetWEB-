<?php
// core/Router.php
class Router {
    private $routes = [];
    private $view;
    
    public function __construct() {
        $this->view = new View();
    }
    
    public function add($path, $controller, $action) {
        $this->routes[$path] = [
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function dispatch($url) {
        // Extraire le chemin de l'URL (ignorer les paramètres GET)
        $path = parse_url($url, PHP_URL_PATH);
        
        // Supprimer les slashes en début et fin de chaîne
        $path = trim($path, '/');
        
        // Si la route est vide, c'est la page d'accueil
        if ($path === '') {
            $path = 'main';
        }
        
        // Vérifier si la route existe
        if (isset($this->routes[$path])) {
            $controllerName = $this->routes[$path]['controller'];
            $action = $this->routes[$path]['action'];
            
            // Inclure le fichier du contrôleur
            require_once '../controllers/' . $controllerName . '.php';
            
            // Instancier le contrôleur et passer la vue
            $controller = new $controllerName($this->view);
            $controller->$action();
        } else {
            // Route non trouvée
            header("HTTP/1.0 404 Not Found");
            $this->view->render('error/404', ['message' => 'Page non trouvée']);
        }
    }
}