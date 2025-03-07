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
            <ul class="main-nav">
                <li><a href="Main.php">Accueil</a></li>
                <li><a href="Entreprises.php">Entreprises</a></li>
                <li><a href="Offres.php">Offres</a></li>
                <li><a href="Contact.php">Contact</a></li>
                <li><a href="Espace-Administration.php" class="active">Espace-Administration</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='Connection.html';">Déconnexion</button>
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
                            <span class="user-info-value">JDosSantos</span>
                        </div>
                        <div class="user-info-row">
                            <span class="user-info-label">Mot de passe :</span>
                            <span class="user-info-value">TripleCoca</span>
                        </div>
                        <div class="user-info-row">
                            <span class="user-info-label">Email :</span>
                            <span class="user-info-value">Warwick@viacesi.fr</span>
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
                    
                    <div class="user-profile-image">
                        <img src="https://via.placeholder.com/200x250" alt="Photo de Joris Dos-Santos">
                    </div>
                </div>
                
                <div class="user-action-buttons">
                    <button class="back-btn" onclick="window.location.href='Espace-Administration.html';">Retour</button>
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