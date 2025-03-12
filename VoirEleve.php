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
    <title>LeBonPlan - Détail Utilisateur</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
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
                    <li><a href="pilote.php">Espace-pilote</a></li>
                <?php endif; ?>
                <li><a href="Contact.php">Contact</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>

    <div class="admin-dashboard">
        <div class="admin-header">
            <h2>Voir : ID 003 : Joris Dos-Santos</h2>
        </div>

        <div class="user-detail-container">
            <div class="user-detail-content">
                <div class="user-profile-grid">
                    <div class="user-profile-info">
                        <div class="user-info-row">
                            <span class="user-info-label">Nom :</span>
                            <span class="user-info-value">Joris Dos-Santos</span>
                        </div>
                        <div class="user-info-row">
                            <span class="user-info-label">Nom d'utilisateur :</span>
                            <span class="user-info-value">3</span>
                        </div>
                        <div class="user-info-row">
                            <span class="user-info-label">Mot de passe :</span>
                            <span class="user-info-value">TripleCoca</span>
                        </div>
                        <div class="user-info-row">
                            <span class="user-info-label">Email :</span>
                            <span class="user-info-value">Warwick@lol.fr</span>
                        </div>
                        
                        <div class="user-info-row wishlist-section">
                            <span class="user-info-label">Wishlist Détaillée :</span>
                        </div>
                        
                        <div class="wishlist-detail-card">
                            <div class="wishlist-header">ID : 120</div>
                            <div class="wishlist-content">
                                <p><strong>Intitulé:</strong> Dev-Nancy</p>
                                <p><strong>Compétences:</strong> PHP, Java, HTML</p>
                                <p><strong>Localisation:</strong> Nancy</p>
                                <p><strong>Demandeur:</strong> 42</p>
                                <div class="wishlist-status">
                                    <div class="status-option">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="user-action-buttons">
                    <button class="back-btn" onclick="window.location.href='Espace-Administration.php';">Retour</button>
                    <button class="edit-btn">Modifier</button>
                    <button class="delete-btn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 - Tous droits réservés - Web4All</p>
        </div>
    </footer>
</body>
</html>