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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = $_POST['subject'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Créez le dossier 'demandes' s'il n'existe pas
    if (!is_dir('demandes')) {
        if (!mkdir('demandes', 0777, true)) {
            die('Failed to create directories...');
        }
    }

    // Génère le nom de fichier unique
    $date = date('Y-m-d_H-i-s');
    $filename = "demandes/{$subject}_{$name}_{$date}.txt";

    // Prépare les données à enregistrer
    $data = "Sujet: $subject\nNom: $name\nEmail: $email\nMessage: $message\n\n";

    // Enregistre les données dans le fichier
    if (file_put_contents($filename, $data) === false) {
        die('Failed to write to file...');
    }

    // Affiche un message de confirmation
    echo '<script>alert("Formulaire soumis avec succès !"); window.location.href = "Main.php";</script>';
    exit();
} else {
    echo 'Invalid request method';
}
?>