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
    <title>LeBonPlan - Recherche de stages</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
</head>
<body>
    <div class="cookie-banner" id="cookie-banner">
        <p>Nous utilisons des cookies pour améliorer votre expérience sur notre site...</p>
        <button id="accept-cookies">Accepter</button>
    </div>
    <header>
        <nav>
            <div class="logo">
                <a href="Main.php"><h1>lebonplan</h1></a>
            </div>
            <div class="burger-menu">&#9776;</div>
            <ul class="main-nav" id="menu">
                <li><a href="Main.php" class="active">Accueil</a></li>
                <li><a href="Offres.php">Offres</a></li>
                <?php if ($_SESSION['user_type'] === 'etudiant'): ?>
                    <li><a href="Wishlist.php">Wishlist</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['user_type'] === 'admin'): ?>
                    <li><a href="Admin.php">Espace-administration</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['user_type'] === 'pilote'): ?>
                    <li><a href="pilote.php">Espace-pilote</a></li>
                <?php endif; ?>
                <li><a href="Contact.php">Contact</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>
    <br><br><br>
    <main>
        <section class="hero">
            <h2>Trouvez le stage idéal</h2>
            <p><big>La plateforme qui simplifie vos recherches de stages</big></p>
            <form class="search-form">
                <input type="text" placeholder="Rechercher par mot-clé, compétence...">
                <input type="text" placeholder="Ville ou région">
                <button type="submit">Rechercher</button>
            </form>
        </section>
        <section class="featured-offers">
            <h2>Offres de stage en vedette</h2>
            <div class="offers-grid">
                <article class="offer-card">
                    <h3>Stage - Développeur Web Full Stack</h3>
                    <p class="company">Web4All</p>
                    <p class="location">Paris</p>
                    <p class="duration">6 mois</p>
                    <a href="/offres/1" class="view-offer">Voir l'offre</a>
                </article>
            </div>
        </section>

        <section class="statistics">
            <h2>Nos chiffres clés</h2>
            <div class="stats-container">
                <div class="stat-item">
                    <p class="stat-number">500+</p>
                    <p class="stat-label">Entreprises partenaires</p>
                </div>
                <div class="stat-item">
                    <p class="stat-number">1000+</p>
                    <p class="stat-label">Offres de stage</p>
                </div>
                <div class="stat-item">
                    <p class="stat-number">5000+</p>
                    <p class="stat-label">Étudiants inscrits</p>
                </div>
            </div>
        </section>

        
    </main>
    <footer>
        <div class="pied">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>À propos</h4>
                    <ul>
                        <li><a href="QSN.php">Qui sommes-nous</a></li>
                        <li><a href="MentionLegales.php">Mentions légales</a></li>
                        <li><a href="CGU.php">CGU</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Ressources</h4>
                    <ul>
                        <li><a href="FAQ.php">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 - Tous droits réservés - Web4All</p>
            </div>
        </div>
    </footer>

</body>
</html>