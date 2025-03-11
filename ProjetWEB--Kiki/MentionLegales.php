<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Mentions Légales</title>
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

    <main style="padding: 80px 20px 40px;">
        <section style="max-width: 800px; margin: 0 auto;">
            <h1 style="margin-bottom: 2rem;">Mentions Légales</h1>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">1. Éditeur du site</h2>
                <p><strong>Web4All</strong><br>
                93 Boulevard de la Seine<br>
                92000 Nanterre<br>
                France<br>
                Email : contact@web4all.fr<br>
                Tél : 01 23 45 67 89</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">2. Hébergement</h2>
                <p>Ce site est hébergé par :<br>
                OVH SAS<br>
                2 rue Kellermann<br>
                59100 Roubaix<br>
                France</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">3. Propriété intellectuelle</h2>
                <p>L'ensemble de ce site relève de la législation française et internationale sur le droit d'auteur et la propriété intellectuelle. Tous les droits de reproduction sont réservés, y compris pour les documents téléchargeables et les représentations iconographiques et photographiques.</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">4. Protection des données personnelles</h2>
                <p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant. Pour exercer ce droit, veuillez nous contacter par email à : privacy@web4all.fr</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">5. Cookies</h2>
                <p>Notre site utilise des cookies pour améliorer votre expérience de navigation. Vous pouvez configurer votre navigateur pour refuser les cookies ou être averti lors de leur utilisation.</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">6. Responsabilité</h2>
                <p>Les informations fournies sur ce site le sont à titre indicatif. Web4All ne saurait garantir l'exactitude, la complétude, l'actualité des informations diffusées sur le site. En conséquence, l'utilisateur reconnaît utiliser ces informations sous sa responsabilité exclusive.</p>
            </article>
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
    <script src="script.js"></script>
</body>
</html>