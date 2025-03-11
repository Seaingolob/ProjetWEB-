<?php


// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header("Location: connexion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Qui sommes-nous</title>
    <link rel="stylesheet" href="styles.css">
    <script src="backButton.js"></script>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="Main.php"><h1>lebonplan</h1></a>
            </div>
            <ul class="main-nav">
                <li><a href="Main.php">Accueil</a></li>
                <li><a href="Entreprises.php">Entreprises</a></li>
                <li><a href="Offres.php">Offres</a></li>
                <li><a href="Wishlist.php">Wishlist</a></li>
                <li><a href="Contact.php">Contact</a></li>

                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>

    <main>
        <section class="about-us">
            <h2>Qui sommes-nous ?</h2>
            <p>Bienvenue chez <strong>LeBonPlan</strong> ! Nous sommes une équipe passionnée de 5 personnes, unies par une même vision : faciliter la connexion entre les entreprises et les opportunités d'emploi.</p>
        
            <div class="team-container">
                <div class="team-member">
                    <img src="images/BastienB.jpg" alt="Membre 1">
                    <h3>Blumenfeld Bastien</h3>
                    <p>Développeur Full-stack</p>
                </div>

                <div class="team-member">
                    <img src="images/KillianB.jpg" alt="Membre 2">
                    <h3>Killian Berthier</h3>
                    <p>Product Owner</p>
                </div>

                <div class="team-member">
                    <img src="images/NielsB.jpg" alt="Membre 3">
                    <h3>Nom Prénom</h3>
                    <p>UX / UI Designer</p>
                </div>

                <div class="team-member">
                    <img src="images/SamC.jpg" alt="Membre 4">
                    <h3>Nom Prénom</h3>
                    <p>Administrateur système / DevOps</p>
                </div>

                <div class="team-member">
                    <img src="images/JorisDS.jpg" alt="Membre 5">
                    <h3>Joris Dos-Santos</h3>
                    <p>Développeur Full-stack</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <button id="backButton">Retour</button>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 - Tous droits réservés - Web4All</p>
        </div>
    </footer>
    <script>document.getElementById('backButton').addEventListener('click', function() {
        window.history.back();
    });
    </script>
</body>
</html>
