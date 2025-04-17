<?php
// core/View.php
class View {
    private $twig;
    
    public function __construct() {
        // Chemin vers les templates
        $templatesDir = dirname(__DIR__) . '/views';
        
        // Configurer l'environnement Twig
        $loader = new \Twig\Loader\FilesystemLoader($templatesDir);
        $this->twig = new \Twig\Environment($loader, [
            'cache' => dirname(__DIR__) . '/cache/twig',
            'debug' => true,
            'auto_reload' => true // À désactiver en production
        ]);
        
        // Ajouter des extensions et fonctions utiles
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        
        // Ajouter des fonctions/variables globales
        $this->twig->addGlobal('session', $_SESSION);
        $this->twig->addFunction(new \Twig\TwigFunction('url', function($path) {
            return '/' . trim($path, '/');
        }
        ));

        // Fonction pour construire les paramètres de pagination pour les offres
        $this->twig->addFunction(new \Twig\TwigFunction('buildPaginationParams', function($page, $company, $location, $competences) {
            $params = "page=" . $page;
            
            if (!empty($company)) {
                $params .= "&company-name=" . urlencode($company);
            }
            
            if (!empty($location)) {
                $params .= "&location=" . urlencode($location);
            }
            
            if (!empty($competences)) {
                foreach ($competences as $competence) {
                    $params .= "&competences[]=" . urlencode($competence);
                }
            }
            
            return $params;
        }));
    }
    
    public function render($template, $data = []) {
        // Rendre la vue avec Twig
        echo $this->twig->render($template . '.html.twig', $data);
    }
}