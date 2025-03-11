<?php
// Démarrer la session
session_start();


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header("Location: connexion.php");
    exit();
}


// Inclure le fichier de configuration pour la connexion à la base de données
require_once(__DIR__ . '/config.php');

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les valeurs du formulaire
    $identifiant = $_POST['identifiant'];
    $motdepasse = $_POST['motdepasse'];
    
    // Préparer la requête pour vérifier l'existence de l'utilisateur
    $sql = "SELECT * FROM Utilisateur WHERE id_compte = :identifiant";
    
    try {
        $stmt = $connexion->prepare($sql);
        $stmt->bindParam(':identifiant', $identifiant, PDO::PARAM_INT);
        $stmt->execute();
        
        // Vérifier si un utilisateur a été trouvé
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérification du mot de passe
            if ($motdepasse === $user['mot_de_passe']) {
                // Authentification réussie
                $_SESSION['user_id'] = $user['id_compte'];
                $_SESSION['logged_in'] = true;
                
                // Déterminer le type d'utilisateur de façon sécurisée
                try {
                    $type_utilisateur = determinerTypeUtilisateur($connexion, $user['id_compte']);
                    $_SESSION['user_type'] = $type_utilisateur;
                } catch (Exception $e) {
                    // Si la détermination échoue, on continue quand même avec un type par défaut
                    $_SESSION['user_type'] = "utilisateur";
                    // Optionnel : journaliser l'erreur
                    error_log("Erreur lors de la détermination du type: " . $e->getMessage());
                }
                
                // Rediriger vers la page principale
                header("Location: Main.php");
                exit();
            } else {
                // Mot de passe incorrect
                $_SESSION['error_message'] = "Identifiant ou mot de passe incorrect.";
                header("Location: connexion.php");
                exit();
            }
        } else {
            // Utilisateur non trouvé
            $_SESSION['error_message'] = "Identifiant ou mot de passe incorrect.";
            header("Location: connexion.php");
            exit();
        }
    } catch (PDOException $e) {
        // Erreur de préparation ou d'exécution
        die("Erreur de connexion ou de requête : " . $e->getMessage());
    }
} else {
    // Si quelqu'un accède directement à cette page sans soumettre le formulaire
    header("Location: connexion.php");
    exit();
}

// Fonction pour déterminer le type d'utilisateur
function determinerTypeUtilisateur($connexion, $id_compte) {
    $types = ["pilote", "etudiant", "admin"]; // Liste des types d'utilisateurs
    
    foreach ($types as $type) {
        $sql = "SELECT 1 FROM $type WHERE id_compte = :id_compte";
        
        try {
            $stmt = $connexion->prepare($sql);
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 1) { // Si l'utilisateur est trouvé
                return $type;
            }
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la préparation de la requête $type: " . $e->getMessage());
        }
    }
    
    return false; // Aucun type trouvé, l'utilisateur n'existe pas
}
?>