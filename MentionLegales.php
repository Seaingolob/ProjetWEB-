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
    <title>LeBonPlan - Mentions Légales</title>
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
                Azure <br>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const backButton = document.getElementById('backButton');
        
        if (backButton) {
            backButton.addEventListener('click', function() {
                // Utiliser window.history pour revenir à la page précédente
                window.history.back();
            });
        }
    });</script>
</body>
</html>
