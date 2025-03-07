<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Contact et Mentions Légales</title>
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
                <li><a href="Contact.php"class="active">Contact</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='Connection.html';">Déconnexion</button>
                </div>
            </ul>

        </nav>
    </header>

    <main>
        <br><br><br>
        <section class="contact-hero">
            <h2>Contactez-nous</h2>
            <p>Notre équipe est à votre disposition pour répondre à toutes vos questions</p>
        </section>

        <section class="contact-info">
            <div class="contact-card">
                <h3>Siège social</h3>
                <address>
                    <p>Web4All</p>
                    <p>93 Boulevard de la Seine</p>
                    <p>92000 Nanterre</p>
                    <p>France</p>
                </address>
            </div>

            <div class="contact-card">
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

        <section class="contact-form">
            <h3>Formulaire de contact</h3>
            <form id="contactForm">
                <div class="form-group">
                    <label for="subject">Sujet *</label>
                    <select id="subject" name="subject" required>
                        <option value="">Choisissez un sujet</option>
                        <option value="info">Demande d'information</option>
                        <option value="problem">Signaler un problème</option>
                        <option value="partnership">Proposition de partenariat</option>
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

                        En envoyant le formulaire, vous consentez à ce que vous données soient traitées conformément à la politique de confidentialité</label>
                </div>

                <button type="submit">Envoyer</button>
            </form>
        </section>

</html>