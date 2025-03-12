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
            <div class="burger-menu">&#9776;</div>
            <ul class="main-nav" id="menu">
                <li><a href="Main.php">Accueil</a></li>
                <li><a href="Offres.php">Offres</a></li>
                <?php if ($_SESSION['user_type'] === 'etudiant'): ?>
                    <li><a href="Wishlist.php">Wishlist</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['user_type'] === 'admin'): ?>
                    <li><a href="Admin.php" class="active">Espace-administration</a></li>
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
            <h2>Tableau de bord Administration</h2>
            <p>Bienvenue dans votre espace administrateur, gérez les utilisateurs et les offres.</p>
        </div>
        <div class="admin-sections">
            <div class="admin-nav">
                <button class="admin-tab active" id="btn-utilisateur">Utilisateur</button>
                <button class="admin-tab" id="btn-offre">Offre</button>
            </div>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Recherchez un utilisateur par ID...">
                <button id="search-btn">Rechercher</button>
            </div>
            <div class="admin-content">
                <!-- Section Utilisateurs -->
                <div class="user-management active" id="section-utilisateur">
                    <h3>Gestion des utilisateurs :</h3>
                    <button class="action-btn" onclick="window.location.href='FormulaireUtilisateur.php';">Ajouter un utilisateur</button>
                    
                    <div class="users-grid">
                        <div class="user-card">
                            <div class="user-header">ID : 001</div>
                            <div class="user-details">
                                <p><strong>Nom:</strong> Bastien Blumenfeld</p>
                                <p><strong>Nom d'utilisateur:</strong> 1</p>
                                <p><strong>Email:</strong> test@viacesi.fr</p>
                                <p><strong>Campus:</strong> Nancy</p>
                                <p><strong>Promotion:</strong> CPIA2 Informatique</p>
                                <p><strong>Wishlist:</strong> 10 offres</p>
                            </div>
                            <div class="user-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="view-btn" onclick="window.location.href='VoirEleve.php';">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>
                        <div class="user-card">
                            <div class="user-header">ID : 002</div>
                            <div class="user-details">
                                <p><strong>Nom:</strong> Niels Bour</p>
                                <p><strong>Nom d'utilisateur:</strong> 2</p>
                                <p><strong>Email:</strong> test@viacesi.fr</p>
                                <p><strong>Campus:</strong> PouetLand</p>
                                <p><strong>Promotion:</strong> M2 Agriculture</p>
                                <p><strong>Wishlist:</strong> 12 offres</p>
                            </div>
                            <div class="user-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="view-btn" onclick="window.location.href='VoirEleve.php';">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>
                        <div class="user-card">
                            <div class="user-header">ID : 003</div>
                            <div class="user-details">
                                <p><strong>Nom:</strong> Joris Dos Santos</p>
                                <p><strong>Nom d'utilisateur:</strong> JDosSantos</p>
                                <p><strong>Email:</strong> Warwick.fr@lol.fr</p>
                                <p><strong>Campus:</strong> Luxembourg</p>
                                <p><strong>Promotion:</strong> M1 Généraliste</p>
                                <p><strong>Wishlist:</strong> 7 offres</p>
                            </div>
                            <div class="user-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="view-btn" onclick="window.location.href='VoirEleve.php';">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Section Offres -->
                <div class="offer-management" id="section-offre">
                    <h3>Gestion des offres :</h3>
                    <button class="action-btn" onclick="window.location.href='FormulaireOffres.php';">Ajouter une offre</button>
                    
                    <div class="offers-container">
                        <div class="offer-card">
                            <div class="offer-header">ID : 001 <br> Stage Full Stack</div>
                            <div class="offer-details">
                                <p><strong>Entreprise:</strong> TechInnovate</p>
                                <p><strong>Localisation:</strong> Paris, 75008</p>
                                <p><strong>Poste:</strong> Développeur Full Stack</p>
                                <p><strong>Compétences requises:</strong></p>
                                <div class="skills-tags">
                                    <span class="skill-tag">PHP</span>
                                    <span class="skill-tag">JavaScript</span>
                                    <span class="skill-tag">React</span>
                                    <span class="skill-tag">MySQL</span>
                                    <span class="skill-tag">Laravel</span>
                                </div>
                            </div>
                            <div class="offer-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="view-btn" onclick="window.location.href='VoirOffre.php';">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>
                        <div class="offer-card">
                            <div class="offer-header">ID : 002 <br> Stage PHP et BDD</div>
                            <div class="offer-details">
                                <p><strong>Entreprise:</strong> DataSolution</p>
                                <p><strong>Localisation:</strong> Lyon, 69002</p>
                                <p><strong>Poste:</strong> Développeur Back-End</p>
                                <p><strong>Compétences requises:</strong></p>
                                <div class="skills-tags">
                                    <span class="skill-tag">Java</span>
                                    <span class="skill-tag">Spring Boot</span>
                                    <span class="skill-tag">PostgreSQL</span>
                                    <span class="skill-tag">Docker</span>
                                    <span class="skill-tag">Microservices</span>
                                </div>
                            </div>
                            <div class="offer-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="view-btn" onclick="window.location.href='VoirOffre.php';">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>
                            <div class="offer-header">ID : 003 <br> Stage Front-End</div>
                            <div class="offer-details">
                                <p><strong>Entreprise:</strong> WebCreative</p>
                                <p><strong>Localisation:</strong> Marseille, 13008</p>
                                <p><strong>Poste:</strong> Développeur Front-End</p>
                                <p><strong>Compétences requises:</strong></p>
                                <div class="skills-tags">
                                    <span class="skill-tag">HTML5/CSS3</span>
                                    <span class="skill-tag">Vue.js</span>
                                    <span class="skill-tag">TypeScript</span>
                                    <span class="skill-tag">SASS</span>
                                    <span class="skill-tag">Figma</span>
                                </div>
                            </div>
                            <div class="offer-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="view-btn" onclick="window.location.href='VoirOffre.php';">Voir</button>
                                    <button class="delete-btn">Supprimer</button>
                                </div>
                            </div>
                        </div>
                        <div class="offer-card">
                            <div class="offer-header">ID : 004 <br> Stage Cybersécurité</div>
                            <div class="offer-details">
                                <p><strong>Entreprise:</strong> CyberSecure</p>
                                <p><strong>Localisation:</strong> Lille, 59000</p>
                                <p><strong>Poste:</strong> Ingénieur Cybersécurité</p>
                                <p><strong>Compétences requises:</strong></p>
                                <div class="skills-tags">
                                    <span class="skill-tag">Python</span>
                                    <span class="skill-tag">Réseaux</span>
                                    <span class="skill-tag">Cryptographie</span>
                                    <span class="skill-tag">Pentest</span>
                                    <span class="skill-tag">Linux</span>
                                </div>
                            </div>
                            <div class="offer-actions">
                                <span>Actions possibles</span>
                                <div class="action-buttons">
                                    <button class="view-btn" onclick="window.location.href='VoirOffre.php';">Voir</button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des onglets d'administration
            const tabBtns = document.querySelectorAll('.admin-tab');
            const sections = document.querySelectorAll('.admin-content > div');
            const searchInput = document.getElementById('search-input');
            
            // Fonction pour changer d'onglet
            function switchTab(targetId) {
                // Supprimer la classe active de tous les boutons et sections
                tabBtns.forEach(b => b.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));
                
                // Ajouter la classe active au bouton et à la section correspondante
                document.getElementById('btn-' + targetId).classList.add('active');
                document.getElementById('section-' + targetId).classList.add('active');
                
                // Changer le placeholder de la barre de recherche en fonction de l'onglet
                if (targetId === 'utilisateur') {
                    searchInput.placeholder = "Recherchez un utilisateur par ID...";
                } else if (targetId === 'offre') {
                    searchInput.placeholder = "Recherchez une offre par ID...";
                }
            }
            
            // Ajouter des écouteurs d'événements aux boutons d'onglet
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetId = this.id.replace('btn-', '');
                    switchTab(targetId);
                });
            });
            
            // Initialiser la page avec l'onglet utilisateur actif
            switchTab('utilisateur');
        });
    </script>
</body>
</html>