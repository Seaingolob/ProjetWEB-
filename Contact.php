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
    <title>LeBonPlan - Contact et Mentions Légales</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
    <script>
        window.onload = function() {
            compteurmessage();
        };
    </script>
</head>
<body>
    <header>
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
                <li><a href="Contact.php" class ="active">Contact</a></li>
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
                        <span>Horaires:</span>
                        <p>Du lundi au vendredi, de 9h à 18h</p>
                    </li>
                </ul>
            </div>
        </section>
        <br>            
        <section class="contact-form">
            <h3>Formulaire de contact</h3>
            <form id="contact-form">
                    <div id="sujet_message" class="message">
                    Veuillez choisir un sujet    
                    </div>
                    <label for="subject">Sujet *</label>
                    <div id="subject_message" class="message">
                    Veuillez sélectionner un sujet
                    </div>
                    <select id="subject" name="subject">
                        <option value="">Choisissez un sujet</option>
                        <option value="info">Demande d'information</option>
                        <option value="problem">Signaler un problème</option>
                        <option value="partnership">Proposition de partenariat</option>
                        <option value="other">Autre</option>
                    </select>            
                    <label for="name">Nom complet *</label>
                    <div id="nom_message" class="message">
                    Veuillez entrer votre nom    
                    </div>    
                    <input type="text" id="name" name="name">
                    <label for="email">Email *</label>
                    <div id="email_message" class="message">
                    Veuillez entrer votre email    
                    </div>
                    <input type="email" id="email" name="email">
                    <label for="message">Message *</d>
                    <div id="commentaire_message" class ="message">
                    Veuillez entrer un message
                    </div>
                    <textarea id="message" name="message" rows="5" maxlength="300"></textarea>
                    <div id="compteur">0/300</div>
                    <label class="checkbox-label">
                        En envoyant le formulaire, vous consentez à ce que vous données soient traitées conformément à la politique de confidentialité
                    </label>
                    <br>    
                <button type="submit" onclick="contact()">Envoyer</button>
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
</html>