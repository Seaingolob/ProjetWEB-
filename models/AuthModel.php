<?php
// models/AuthModel.php

class AuthModel {
    private $connexion;
    
    public function __construct() {
        // Inclure le fichier de configuration
        require_once __DIR__ . '/../config/config.php';
        $this->connexion = $connexion; // Supposant que $connexion vient de config.php
    }
    
    public function verifyUser($identifiant, $motdepasse) {  // Le paramètre s'appelle motdepasse ici
        // Rechercher l'utilisateur par identifiant
        $sql = "SELECT id_compte, nom, prenom, mail, mot_de_passe  
                FROM utilisateur 
                WHERE id_compte = :identifiant";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':identifiant', $identifiant, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier si l'utilisateur existe et si le mot de passe correspond
        // Note: dans la BDD, c'est 'mot_de_passe', mais la variable PHP s'appelle 'motdepasse'
        if ($user && password_verify($motdepasse, $user['mot_de_passe'])) {
            // Ajouter le type d'utilisateur aux informations
            $user['type_compte'] = $this->get_user_type($user['id_compte']);
            return $user;
        } elseif ($user && $motdepasse === $user['mot_de_passe']) {
            // Si le mot de passe n'est pas hashé dans la base de données
            // Ajouter le type d'utilisateur aux informations
            $user['type_compte'] = $this->get_user_type($user['id_compte']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Détermine le type de compte de l'utilisateur en vérifiant sa présence dans les tables spécifiques
     * 
     * @param int $userId ID de l'utilisateur
     * @return string Type de compte ('etudiant', 'pilote', 'admin' ou 'inconnu')
     */
    public function get_user_type($userId) {
        // Vérifier si l'utilisateur est un étudiant
        $sql = "SELECT id_compte FROM etudiant WHERE id_compte = :userId LIMIT 1";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return 'etudiant';
        }
        
        // Vérifier si l'utilisateur est un pilote
        $sql = "SELECT id_compte FROM pilote WHERE id_compte = :userId LIMIT 1";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return 'pilote';
        }
        
        // Vérifier si l'utilisateur est un admin
        $sql = "SELECT id_compte FROM admin WHERE id_compte = :userId LIMIT 1";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return 'admin';
        }
        
        // Si aucun type n'est trouvé
        return 'inconnu';
    }
}