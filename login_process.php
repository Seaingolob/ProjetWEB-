<?php
// Démarrer la session
session_start();

// Définir la durée de la session à 1 heure (en secondes)
ini_set('session.gc_maxlifetime', 3600); // 60 minutes * 60 secondes = 3600 secondes
session_set_cookie_params(3600);

// Vérifier si la session existe déjà et si elle a expiré
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    // La session a expiré, déconnecter l'utilisateur
    session_unset();
    session_destroy();
    header("Location: connexion.php?expired=1");
    exit();
}

// Mettre à jour le timestamp de dernière activité
$_SESSION['last_activity'] = time();

// Inclure le fichier de configuration
require_once 'config.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les valeurs du formulaire
    $identifiant = $_POST['identifiant'];
    $motdepasse = $_POST['motdepasse'];
    
    // Préparer la requête pour vérifier l'existence de l'utilisateur
    $sql = "SELECT id_compte, mot_de_passe, nom, prenom FROM utilisateur WHERE id_compte = :identifiant";
    
    try {
        $stmt = $connexion->prepare($sql);
        $stmt->bindParam(':identifiant', $identifiant, PDO::PARAM_STR);
        $stmt->execute();
        
        // Vérifier si un utilisateur a été trouvé
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérification du mot de passe avec password_verify
            if (password_verify($motdepasse, $user['mot_de_passe'])) {
                // Authentification réussie
                $_SESSION['user_id'] = $user['id_compte'];
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time(); // Enregistrer le moment de la connexion
                $_SESSION['user_name'] = $user['nom'] . " " . $user['prenom'];
                
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
        $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
        header("Location: connexion.php");
        exit();
    }
} else {
    // Si quelqu'un accède directement à cette page sans soumettre le formulaire
    header("Location: connexion.php");
    exit();
}

// Fonction pour déterminer le type d'utilisateur
function determinerTypeUtilisateur($connexion, $id_compte) {
    $types = ["etudiant", "admin", "pilote"]; // Liste des types d'utilisateurs, en incluant les pilotes

    foreach ($types as $type) {
        $sql = "SELECT 1 FROM $type WHERE id_compte = :id_compte";

        try {
            $stmt = $connexion->prepare($sql);
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 1) { // Si l'utilisateur est trouvé
                return $type; // Réponse le type
            }
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la préparation de la requête $type: " . $e->getMessage());
        }
    }

    return "utilisateur"; // Type par défaut si aucun type spécifique n'est trouvé
}
?>