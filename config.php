<?php
// Inclut le fichier vault.php pour récupérer les informations de connexion à la base de données    
$parametres = include 'vault.php';

// Récupère les informations de connexion à la base de données
$serveur = $parametres['serveur'];
$utilisateur = $parametres['utilisateur'];
$mot_de_passe = $parametres['mot_de_passe'];
$base_de_donnees = $parametres['base_de_donnees'];

// Définit le DSN de connexion à la base de données
$dsn = "mysql:host=$serveur;dbname=$base_de_donnees;charset=utf8";


try {
    // Création de la connexion PDO
    // Utilise l'option PDO::ATTR_PERSISTENT pour une connexion persistante
    // Cela peut améliorer les performances en évitant de recréer la connexion à chaque requête
    $connexion = new PDO($dsn, $utilisateur, $mot_de_passe, array(PDO::ATTR_PERSISTENT => true));
    
    // Définit le mode d'erreur de PDO pour afficher les exceptions en cas d'erreur
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // En cas d'erreur, affiche le message d'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>