<?php
$serveur = "localhost"; // Adresse du serveur
$utilisateur = "root"; // Nom d'utilisateur MySQL (par défaut sur XAMPP)
$mot_de_passe = ""; // Ajustez selon votre configuration XAMPP
$base_de_donnees = "projet_web"; // Nom de votre base de données

// DSN (Data Source Name) pour la connexion PDO
$dsn = "mysql:host=$serveur;dbname=$base_de_donnees;charset=utf8";

try {
    // Création de la connexion PDO
    $connexion = new PDO($dsn, $utilisateur, $mot_de_passe);
    
    // Définit le mode d'erreur de PDO pour afficher les exceptions en cas d'erreur
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Suppression du message de débogage
    // echo "Connexion réussie à la base de données!";
} catch (PDOException $e) {
    // En cas d'erreur, affiche le message d'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>