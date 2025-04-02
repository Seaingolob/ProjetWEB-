
<?php
// Démarrer la session
session_start();

// Vérifier si la session existe et si elle a expiré
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    // La session a expiré, déconnecter l'utilisateur
    session_unset();
    session_destroy();
    header("Location: connexion.php?expired=1");
    exit();
}
// Mettre à jour le timestamp de dernière activité
$_SESSION['last_activity'] = time();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header("Location: connexion.php");
    exit();
}

// Vérifier que l'utilisateur est un étudiant
if ($_SESSION['user_type'] !== 'etudiant') {
    // Rediriger vers la page principale si ce n'est pas un étudiant
    header("Location: Main.php");
    exit();
}

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier que tous les champs nécessaires sont remplis
    if (isset($_POST['id_offre']) && isset($_POST['note'])) {
        $id_offre = intval($_POST['id_offre']);
        $id_compte = $_SESSION['user_id'];
        $note = htmlspecialchars($_POST['note']);
        $avis = isset($_POST['avis']) ? htmlspecialchars($_POST['avis']) : null;
        
        try {
            // Vérifier que l'offre existe
            $stmt = $connexion->prepare("SELECT 1 FROM offre WHERE id_offre = :id_offre");
            $stmt->bindParam(':id_offre', $id_offre);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                // L'offre n'existe pas
                $_SESSION['error'] = "L'offre spécifiée n'existe pas.";
                header("Location: Offres.php");
                exit();
            }
            
            // Vérifier si l'utilisateur a déjà évalué cette offre
            $stmt = $connexion->prepare("SELECT 1 FROM evaluation WHERE id_compte = :id_compte AND id_offre = :id_offre");
            $stmt->bindParam(':id_compte', $id_compte,PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                // L'utilisateur a déjà évalué cette offre
                $_SESSION['error'] = "Vous avez déjà évalué cette offre.";
                header("Location: VoirOffre.php?id=" . $id_offre);
                exit();
            }
            
            // Vérifier si la note est valide
            $notes_valides = ['Excellent', 'Très bien', 'Bien', 'Moyen', 'À éviter'];
            if (!in_array($note, $notes_valides)) {
                $_SESSION['error'] = "La note spécifiée n'est pas valide.";
                header("Location: VoirOffre.php?id=" . $id_offre);
                exit();
            }
            
            // Ajouter l'évaluation
            $stmt = $connexion->prepare("INSERT INTO evaluation (id_compte, id_offre, note, avis) VALUES (:id_compte, :id_offre, :note, :avis)");
            $stmt->bindParam(':id_compte', $id_compte,PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre);
            $stmt->bindParam(':note', $note);
            $stmt->bindParam(':avis', $avis);
            $stmt->execute();
            
            // Rediriger vers la page de l'offre avec un message de succès
            $_SESSION['success'] = "Votre évaluation a été ajoutée avec succès !";
            header("Location: VoirOffre.php?id=" . $id_offre);
            exit();
            
        } catch(PDOException $e) {
            // Gérer les erreurs de base de données
            $_SESSION['error'] = "Erreur lors de l'ajout de l'évaluation : " . $e->getMessage();
            header("Location: VoirOffre.php?id=" . $id_offre);
            exit();
        }
    } else {
        // Les données requises ne sont pas présentes
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
        header("Location: Offres.php");
        exit();
    }
} else {
    // Si le formulaire n'a pas été soumis via POST, rediriger vers la page des offres
    header("Location: Offres.php");
    exit();
}
?>

