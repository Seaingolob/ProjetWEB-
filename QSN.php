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
<header class="header">
    <nav>
            <div class="logo">
                <a href="Main.php"><h1>lebonplan</h1></a>
            </div>
            <div class="user-info-left"> 
                <a href="VoirEleve.php?id=<?php echo $_SESSION['user_id']; ?>" class="profile-link">
                    👤 <?php echo $_SESSION['user_name']; ?>
                </a>
            </div>
            <div class="burger-menu">&#9776;</div>
            <ul class="main-nav" id="menu">
                <li><a href="Main.php" >Accueil</a></li>
                <li><a href="Offres.php">Offres</a></li>
                <?php if ($_SESSION['user_type'] === 'etudiant'): ?>
                    <li><a href="Wishlist.php">Wishlist</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['user_type'] === 'admin'): ?>
                    <li><a href="Admin.php">Espace-administration</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['user_type'] === 'pilote'): ?>
                    <li><a href="Admin.php">Espace-pilote</a></li>
                <?php endif; ?>
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
                    <p>Dev JavaScript</p>
                </div>

                <div class="team-member">
                    <img src="images/NielsB.jpg" alt="Membre 3">
                    <h3>Niels Bourg</h3>
                    <p>Interface Utilisateur</p>
                </div>

                <div class="team-member">
                    <img src="images/SamC.jpg" alt="Membre 4">
                    <h3>Samuel Ceccarelli</h3>
                    <p>Dev Backend PHP</p>
                </div>

                <div class="team-member">
                    <img src="images/JorisDS.jpg" alt="Membre 5">
                    <h3>Joris Dos-Santos</h3>
                    <p>Expert CSS</p>
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
