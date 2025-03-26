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
    <title>LeBonPlan - Contact et Mentions Légales</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="Main.php"><h1>lebonplan</h1></a>
            </div>
            <div class="burger-menu">&#9776;</div>
            <ul class="main-nav" id="menu">
                <li><a href="Main.php">Accueil</a></li>
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
                <li><a href="Contact.php" class="active">Contact</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>

    <main>
        <br><br><br>
        <section class="contact-form">
            <h2>Contactez-nous</h2>
            <p>Notre équipe est à votre disposition pour répondre à toutes vos questions</p>
        </section>
        <br>
        <section class="contact-info">
            <div class="contact-form">
                <h3>Siège social</h3>
                <address>
                    <p>Web4All</p>
                    <p>93 Boulevard de la Seine</p>
                    <p>92000 Nanterre</p>
                    <p>France</p>
                </address>
            </div>
            <br>
            <div class="contact-form">
                <h3>Coordonnées</h3>
                <ul>
                    <li>
                        <span>Email:</span>
                        <a href="mailto:contact@web4all.fr">contact@web4all.fr</a>
                    </li>
                    <li>
                        <span>Téléphone:</span>
                        <a href="tel:+33123456789">01 23 45 67 89</a>
                    </li>
                    <li>
                        <span>Horaires: Du lundi au vendredi, de 9h à 18h</span>
                    </li>
                </ul>
            </div>
        </section>
        <br>
        <section class="contact-form">
            <h3>Formulaire de contact</h3>

            <form id="contactForm" action="submit.php" method="POST">
                <div class="form-group">
                    <label for="subject">Sujet *</label>
                    <select id="subject" name="subject" required>
                        <option value="">Choisissez un sujet</option>
                        <option value="info">Demande d'information</option>
                        <option value="problem">Signaler un problème</option>
                        <option value="partnership">Proposition de partenariat</option>
                        <option value="demande_de_changement">Demande de changement / Ajout</option>
                        <option value="other">Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Nom complet *</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <br>
                        En envoyant le formulaire, vous consentez à ce que vous données soient traitées conformément à la politique de confidentialité</label>
                </div>

                <button type="submit">Envoyer</button>
            </form>
        </section>
        <br><br>
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
    </main>
</body>
</html>