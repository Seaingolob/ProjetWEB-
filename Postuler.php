<?php
// Démarrer la session
session_start();

// Vérifier si la session existe et si elle a expiré
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    // La session a expiré, déconnecter l'utilisateur
    session_unset();
    session_destroy();
    header("Location: Connexion.php?expired=1");
    exit();
}
// Mettre à jour le timestamp de dernière activité
$_SESSION['last_activity'] = time();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header("Location: Connexion.php");
    exit();
}

// Vérifier si l'utilisateur est un étudiant
if ($_SESSION['user_type'] !== 'etudiant') {
    // Rediriger vers la page principale
    header("Location: Main.php");
    exit();
}

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Vérifier si l'ID de l'offre est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Rediriger vers la page des offres
    header("Location: Offres.php");
    exit();
}

// Récupérer l'ID de l'offre
$id_offre = intval($_GET['id']);
$id_compte = $_SESSION['user_id'];

try {
    // Vérifier si l'offre existe
    $stmt = $connexion->prepare("SELECT COUNT(*) FROM offre WHERE id_offre = :id");
    $stmt->bindParam(':id', $id_offre);
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        // L'offre n'existe pas
        header("Location: Offres.php");
        exit();
    }
    
    // Vérifier si l'étudiant a déjà postulé à cette offre
    $stmt = $connexion->prepare("SELECT COUNT(*) FROM postuler WHERE id_compte = :id_compte AND id_offre = :id_offre");
    $stmt->bindParam(':id_compte', $id_compte);
    $stmt->bindParam(':id_offre', $id_offre);
    $stmt->execute();
    
    if ($stmt->fetchColumn() > 0) {
        // L'étudiant a déjà postulé
        header("Location: VoirOffre.php?id=" . $id_offre . "&error=already_applied");
        exit();
    }
    
    // Insérer la candidature
    $stmt = $connexion->prepare("INSERT INTO postuler (id_compte, id_offre) VALUES (:id_compte, :id_offre)");
    $stmt->bindParam(':id_compte', $id_compte);
    $stmt->bindParam(':id_offre', $id_offre);
    $stmt->execute();
    
    // Rediriger vers la page de détail avec un message de succès
    header("Location: VoirOffre.php?id=" . $id_offre . "&success=applied");
    exit();
    
} catch(PDOException $e) {
    // En cas d'erreur, afficher un message et rediriger
    echo "Erreur : " . $e->getMessage();
    exit();
}
?>
