<?php
// Démarrer la session
session_start();

// Détruire toutes les données de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header("Location: connexion.php");
exit();
?>