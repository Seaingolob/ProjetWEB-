<?php
// models/AuthModel.php

class AuthModel {
    private $connexion;
    
    public function __construct() {
        // Inclure le fichier de configuration
        require_once 'config/config.php';
        $this->connexion = $connexion; // Supposant que $connexion vient de config.php
    }
    
    public function verifyUser($identifiant, $motdepasse) {
        // Rechercher l'utilisateur par identifiant
        $sql = "SELECT id_compte, nom, prenom, mail, motdepasse, type_compte 
                FROM utilisateur 
                WHERE mail = :identifiant OR id_compte = :identifiant";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':identifiant', $identifiant, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier si l'utilisateur existe et si le mot de passe correspond
        if ($user && password_verify($motdepasse, $user['motdepasse'])) {
            return $user;
        } elseif ($user && $motdepasse === $user['motdepasse']) {
            // Si le mot de passe n'est pas hashé dans la base de données
            // (à remplacer par password_verify une fois les mots de passe hashés)
            return $user;
        }
        
        return false;
    }
}