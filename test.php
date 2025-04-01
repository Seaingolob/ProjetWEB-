<?php
// Paramètres de connexion à la base de données locale
$serveur = "52.143.152.159"; // Ou 127.0.0.1
$utilisateur = "CESIdistant";  // Utilisateur par défaut (à modifier selon votre configuration)
$motdepasse = "Password1234!";       // Par défaut vide sous XAMPP/WAMP (à modifier selon votre configuration)
$base_de_donnees = "LeBonPlan"; // Remplacez par le nom de votre base de données

try {
    // Création de la connexion avec PDO
    $connexion = new PDO("mysql:host=$serveur;dbname=$base_de_donnees;charset=utf8mb4", 
                         $utilisateur, 
                         $motdepasse);
                         
    // Configuration pour afficher les erreurs
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connexion réussie à la base de données locale!";
    
    // À partir d'ici, vous pouvez exécuter vos requêtes SQL
    // Exemple:


    
} 

catch(PDOException $e) {
    echo "La connexion à la base de données a échoué : " . $e->getMessage();
}                   

// La connexion se ferme automatiquement à la fin du script
?>
