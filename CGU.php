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
    <title>LeBonPlan - Conditions Générales d'Utilisation</title>
    <link rel="stylesheet" href="styles.css">
    <script src="backButton.js"></script>
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
                <li><a href="Contact.php">Contact</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>

    <main style="padding: 80px 20px 40px;">
        <section style="max-width: 800px; margin: 0 auto;">
            <h1 style="margin-bottom: 2rem;">Conditions Générales d'Utilisation</h1>
            
            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">1. Objet</h2>
                <p>Les présentes Conditions Générales d'Utilisation (CGU) ont pour objet de définir les conditions d'accès et d'utilisation du site LeBonPlan. En accédant à ce site, vous acceptez sans réserve les présentes conditions.</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">2. Accès au service</h2>
                <p>2.1. Le site LeBonPlan est accessible gratuitement à tout utilisateur disposant d'un accès Internet.</p>
                <p>2.2. L'utilisateur doit créer un compte pour accéder à certaines fonctionnalités du site.</p>
                <p>2.3. LeBonPlan se réserve le droit de refuser l'accès au service, unilatéralement et sans notification préalable, à tout utilisateur ne respectant pas les présentes conditions.</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">3. Inscription</h2>
                <p>3.1. L'inscription sur le site est réservée aux étudiants et aux entreprises.</p>
                <p>3.2. Les informations fournies lors de l'inscription doivent être exactes et à jour.</p>
                <p>3.3. L'utilisateur est responsable de la confidentialité de ses identifiants de connexion.</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">4. Publication d'offres</h2>
                <p>4.1. Les entreprises s'engagent à publier des offres de stage conformes à la législation en vigueur.</p>
                <p>4.2. Les offres doivent être claires, précises et correspondre à un réel besoin.</p>
                <p>4.3. LeBonPlan se réserve le droit de supprimer toute offre ne respectant pas ces conditions.</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">5. Responsabilités</h2>
                <p>5.1. LeBonPlan agit comme intermédiaire entre les entreprises et les étudiants.</p>
                <p>5.2. LeBonPlan ne peut être tenu responsable du contenu des offres publiées.</p>
                <p>5.3. Les utilisateurs sont seuls responsables de leurs interactions sur la plateforme.</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">6. Propriété intellectuelle</h2>
                <p>6.1. Le contenu du site est protégé par le droit d'auteur.</p>
                <p>6.2. Toute reproduction non autorisée est interdite.</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">7. Modification des CGU</h2>
                <p>LeBonPlan se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés des modifications par email ou par notification sur le site.</p>
            </article>

            <article style="margin-bottom: 2rem;">
                <h2 style="color: var(--gray-800); margin-bottom: 1rem;">8. Litiges</h2>
                <p>Les présentes CGU sont régies par le droit français. Tout litige relatif à leur interprétation ou leur exécution relève des tribunaux français.</p>
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

    <script>document.getElementById('backButton').addEventListener('click', function() {
        window.history.back();
    });
    </script>
</body>
</html>