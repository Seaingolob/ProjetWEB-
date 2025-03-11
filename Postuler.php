<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Postuler</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="Main.html"><h1>lebonplan</h1></a>
            </div>
            <ul class="main-nav">
                <li><a href="Main.php">Accueil</a></li>
                <li><a href="Entreprises.php">Entreprises</a></li>
                <li><a href="Offres.php" >Offres</a></li>
                <li><a href="Postuler.php" class="active">Postuler</a></li>
                <li><a href="Wishlist.php">Wishlist</a></li>
                <li><a href="Contact.php">Contact</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='Connexion.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>

    <main>
        <section class="application-section">
            <h2>Postuler à une offre</h2>
            <form class="application-form">
                <div class="form-group">
                    <label for="full-name">Nom complet :</label>
                    <input type="text" id="full-name" name="full-name" placeholder="Ex: Jean Dupont" required>
                </div>
                <br>
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email" placeholder="Ex: jean.dupont@email.com" required>
                </div>
                <br>
                <div class="form-group">
                    <label for="cv">CV (PDF uniquement) :</label>
                    <input type="file" id="cv" name="cv" accept="application/pdf" required>
                </div>
                <br>
                <div class="form-group">
                    <label for="cover-letter">Lettre de motivation :</label>
                    <textarea id="cover-letter" name="cover-letter" placeholder="Expliquez pourquoi vous êtes motivé pour ce poste..." required></textarea>
                </div>
                <br>
                <button type="submit">Envoyer la candidature</button>
                <br>
                <br>
            </form>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>À propos</h4>
                <ul>
                    <li><a href="QSN.html">Qui sommes-nous</a></li>
                    <li><a href="MentionLegales.html">Mentions légales</a></li>
                    <li><a href="/cgu">CGU</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Ressources</h4>
                <ul>
                    <li><a href="/blog">Blog</a></li>
                    <li><a href="FAQ.html">FAQ</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 - Tous droits réservés - Web4All</p>
        </div>
    </footer>
</body>
</html>