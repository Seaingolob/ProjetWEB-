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
    <title>LeBonPlan - FAQ</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
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

    <main class="faq-page">
        <section class="faq-hero">
            <h1>Questions Fréquentes</h1>
            <p>Trouvez rapidement des réponses à vos questions sur LeBonPlan</p>
        </section>

        <section class="faq-categories">
            <div class="faq-category">
                <h2>Compte et Inscription</h2>
                <div class="faq-item">
                    <h3>Comment créer un compte ?</h3>
                    <p>Pour créer un compte, rendez-vous sur la page de connexion et cliquez sur "Créer un compte". Suivez les instructions pour compléter votre profil.</p>
                </div>
                <div class="faq-item">
                    <h3>Qui peut s'inscrire sur LeBonPlan ?</h3>
                    <p>LeBonPlan est ouvert aux étudiants et aux entreprises proposant des stages. Vous devez être étudiant ou représentant d'entreprise pour vous inscrire.</p>
                </div>
            </div>

            <div class="faq-category">
                <h2>Offres de Stage</h2>
                <div class="faq-item">
                    <h3>Comment postuler à un stage ?</h3>
                    <p>Une fois connecté, naviguez dans la section "Offres", sélectionnez l'offre qui vous intéresse et cliquez sur "Postuler". Préparez votre CV et lettre de motivation.</p>
                </div>
                <div class="faq-item">
                    <h3>Les offres sont-elles vérifiées ?</h3>
                    <p>Nous vérifions chaque offre de stage publiée pour garantir sa conformité et sa pertinence. Cependant, nous recommandons toujours une vérification personnelle.</p>
                </div>
            </div>

            <div class="faq-category">
                <h2>Entreprises</h2>
                <div class="faq-item">
                    <h3>Comment publier une offre de stage ?</h3>
                    <p>Les entreprises doivent créer un compte professionnel et accéder à la section "Publier une offre" dans leur espace entreprise.</p>
                </div>
                <div class="faq-item">
                    <h3>Quels sont les avantages pour les entreprises ?</h3>
                    <p>LeBonPlan permet de toucher un large réseau d'étudiants motivés, de publier des offres gratuitement et de simplifier le processus de recrutement de stagiaires.</p>
                </div>
            </div>
        </section>

        <section class="contact-support">
            <h2>Besoin d'aide ?</h2>
            <p>Si vous ne trouvez pas la réponse à votre question, contactez notre support.</p>
            <br>
            <a href="Contact.php" class="button">Contactez-nous</a>
        </section>
    </main>

    <footer>
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
                    <li><a href="Blog.php">Blog</a></li>
                    <li><a href="FAQ.php">FAQ</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 - Tous droits réservés - Web4All</p>
        </div>
    </footer>
</body>
</html>