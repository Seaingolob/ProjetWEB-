<?php

// Démarrer la session
session_start();

// Rediriger si l'utilisateur est déjà connecté
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: Main.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
    <script>
        window.onload = function() {
            connexion();
        };
    </script>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="/"><h1>lebonplan</h1></a>
            </div>
        </nav>
    </header>
    <div class="login-container">
        <div class="login-box">
            <h2>Connexion</h2>
            <?php
            if (isset($_SESSION['error_message'])) {
                echo '<div class="err_message" style="color: red; margin-top: 10px;">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            ?>
            <form id="login-form" action="login_process.php" method="post">
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant">
                <div id="utilisateur-message" class="message">Veuillez entrer votre identifiant</div>
                <label for="motdepasse">Mot de passe</label>
                <input type="password" id="motdepasse" name="motdepasse">
                <div id="mdp-message" class="message">Veuillez entrer votre mot de passe</div>
                <div class="btn-container">
                    <button type="submit">Se connecter</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>