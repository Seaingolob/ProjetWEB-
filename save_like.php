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

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    exit();
}

// Récupérer les données
$offerId = isset($_POST['offer_id']) ? intval($_POST['offer_id']) : 0;
$liked = isset($_POST['liked']) ? intval($_POST['liked']) : 0;
$userId = $_SESSION['user_id'];

// Vérifier que l'ID de l'offre est valide
if ($offerId <= 0) {
    exit();
}

try {
    if ($liked == 1) {
        // Ajouter à la wishlist
        $sql = "INSERT IGNORE INTO souhaiter (id_compte, id_offre) VALUES (:user_id, :offer_id)";
    } else {
        // Supprimer de la wishlist
        $sql = "DELETE FROM souhaiter WHERE id_compte = :user_id AND id_offre = :offer_id";
    }
    
    $stmt = $connexion->prepare($sql);
    $stmt->execute([':user_id' => $userId, ':offer_id' => $offerId]);
} catch (PDOException $e) {
    // Silencieusement échouer
}
?>