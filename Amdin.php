<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Espace Administration</title>
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
                    <button id="logout-btn" onclick="window.location.href='Connexion.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>

    <div class="admin-dashboard">
        <div class="admin-header">
            <h2>Tableau de bord Administration</h2>
            <p>Bienvenue dans votre espace administrateur, gérez les utilisateurs, les offres et les différentes entreprises.</p>
        </div>

        <div class="admin-sections">
            <div class="admin-nav">
                <button class="admin-tab active" id="btn-utilisateur">Utilisateur</button>
                <button class="admin-tab" id="btn-entreprise">Entreprise</button>
                <button class="admin-tab" id="btn-offre">Offre</button>
            </div>

            <div class="admin-content">
                <div class="user-management">
                    <h3>Gestion des utilisateurs :</h3>
                    <button class="action-btn">Ajouter un utilisateur</button>
                    
                    <div class="users-grid">
                        <div class="user-card">
                            <div class="user-header">ID : 001</div>
                            <div class="user-details">
                                <p><strong>Nom:</strong> Bastien Blumerfield</p>
                                <p><strong>Nom d'utilisateur:</strong> BBumerfield</p>
                                <p><strong>Rôle de partie:</strong> Codi</p>
                                <p><strong>Email:</strong> bastien.blumerfield@ucac.fr</p>
                                <p><strong>Wishlist:</strong> 10 offres</p>
                            </div>
                            <div class="user-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="edit-btn">Modifier</button>
                                    <button class="view-btn">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>

                        <div class="user-card">
                            <div class="user-header">ID : 002</div>
                            <div class="user-details">
                                <p><strong>Nom:</strong> Maël Bour</p>
                                <p><strong>Nom d'utilisateur:</strong> MBour</p>
                                <p><strong>Rôle de partie:</strong> DevBackend</p>
                                <p><strong>Email:</strong> mael.bour@ucac.fr</p>
                                <p><strong>Wishlist:</strong> 12 offres</p>
                            </div>
                            <div class="user-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="edit-btn">Modifier</button>
                                    <button class="view-btn">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>

                        <div class="user-card">
                            <div class="user-header">ID : 003</div>
                            <div class="user-details">
                                <p><strong>Nom:</strong> Jose Dos-Santos</p>
                                <p><strong>Nom d'utilisateur:</strong> JDosSantos</p>
                                <p><strong>Rôle de partie:</strong> TestCovid</p>
                                <p><strong>Email:</strong> jose.dos@ucac.fr</p>
                                <p><strong>Wishlist:</strong> 7 offres</p>
                            </div>
                            <div class="user-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="edit-btn">Modifier</button>
                                    <button class="view-btn">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>

                        <div class="user-card">
                            <div class="user-header">ID : 004</div>
                            <div class="user-details">
                                <p><strong>Nom:</strong> Nolan Repram</p>
                                <p><strong>Nom d'utilisateur:</strong> NRepram</p>
                                <p><strong>Rôle de partie:</strong> GraphicCovid</p>
                                <p><strong>Email:</strong> nolan.repram@ucac.fr</p>
                                <p><strong>Wishlist:</strong> 35 offres</p>
                            </div>
                            <div class="user-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="edit-btn">Modifier</button>
                                    <button class="view-btn">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>

                        <div class="user-card">
                            <div class="user-header">ID : 005</div>
                            <div class="user-details">
                                <p><strong>Nom:</strong> Killian Barthier</p>
                                <p><strong>Nom d'utilisateur:</strong> KBarthier</p>
                                <p><strong>Rôle de partie:</strong> PortfoOolio</p>
                                <p><strong>Email:</strong> killian.barthier@ucac.fr</p>
                                <p><strong>Wishlist:</strong> 25 offres</p>
                            </div>
                            <div class="user-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="edit-btn">Modifier</button>
                                    <button class="view-btn">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <ul>

        <div class="footer-bottom">
            <p>&copy; 2024 - Tous droits réservés - Web4All</p>
        </div>
    </footer>

    <script>
        // Script pour changer entre les onglets d'administration
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.admin-tab');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Supprimer la classe active de tous les boutons
                    tabBtns.forEach(b => b.classList.remove('active'));
                    // Ajouter la classe active au bouton cliqué
                    this.classList.add('active');
                    
                    // Ici, vous pourriez ajouter du code pour afficher le contenu correspondant
                    // Par exemple, switch entre affichage des utilisateurs, entreprises, offres
                });
            });
        });
    </script>
</body>
</html>