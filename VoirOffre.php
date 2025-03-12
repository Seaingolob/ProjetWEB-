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
    <title>LeBonPlan - Détail de l'offre</title>
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
                <li><a href="Contact.php">Contact</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>
<br>
    <main>
        <div class="offre-detail-container">
            <div class="offre-header">
                </div>
                <div class="offre-title-container">
                    <h1>Stage - Développeur FullStack</h1>
                    <p class="entreprise-name">DigitalSolutions</p>
                    <p class="offre-location">Lyon, France</p>
                    <div class="offre-meta">
                        <span class="offre-meta-item">Durée: 6 mois</span>
                        <span class="offre-meta-item">Publiée le: 01/03/2024</span>
                        <span class="offre-meta-item">Début: 01/06/2024</span>
                    </div>
                </div>
            </div>

            <div class="offre-content">
                <div class="offre-details">
                    <div class="section">
                        <h2 class="section-title">Description du poste</h2>
                        <div class="section-content">
                            <p>Nous recherchons un développeur FullStack pour rejoindre notre équipe dynamique pour une durée de 6 mois. Vous participerez au développement de nouvelles fonctionnalités pour notre plateforme web, et travaillerez en étroite collaboration avec nos équipes de design et de marketing. Vous aurez l'opportunité de travailler sur des projets concrets et de développer vos compétences techniques dans un environnement professionnel stimulant.</p>
                        </div>
                    </div>

                    <div class="section">
                        <h2 class="section-title">Missions</h2>
                        <div class="section-content">
                            <ul class="missions-list">
                                <li>Développement de nouvelles fonctionnalités front-end et back-end</li>
                                <li>Participation à la refonte de l'interface utilisateur</li>
                                <li>Optimisation des performances des applications existantes</li>
                                <li>Tests et débogage de code</li>
                                <li>Veille technologique</li>
                            </ul>
                        </div>
                    </div>

                    <div class="section">
                        <h2 class="section-title">Profil recherché</h2>
                        <div class="section-content">
                            <p>Vous êtes en formation Bac+4/5 en informatique, vous avez une appétence particulière pour le développement web et les nouvelles technologies. Vous êtes curieux, autonome et avez un bon esprit d'équipe.</p>
                            <h3 style="margin-top: 15px; font-size: 1rem; font-weight: 600;">Compétences requises:</h3>
                            <div class="skills-container">
                                <span class="skill-tag">React</span>
                                <span class="skill-tag">Node.js</span>
                                <span class="skill-tag">MongoDB</span>
                                <span class="skill-tag">JavaScript</span>
                                <span class="skill-tag">Git</span>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h2 class="section-title">Rémunération</h2>
                        <div class="section-content">
                            <p>900€ / mois</p>
                        </div>
                    </div>

                    <div class="section">
                        <h2 class="section-title">À propos de l'entreprise</h2>
                        <div class="section-content">
                            <p>DigitalSolutions est une entreprise spécialisée dans le développement d'applications web et mobiles innovantes. Fondée en 2015, notre entreprise compte aujourd'hui plus de 50 collaborateurs passionnés par les nouvelles technologies. Nous intervenons dans divers secteurs d'activité (e-commerce, finance, santé) et accompagnons nos clients dans leur transformation digitale.</p>
                            <p style="margin-top: 10px;"><strong>Adresse:</strong> 15 rue de la République, 69002 Lyon</p>
                        </div>
                    </div>
                </div>

                <div class="postuler-sidebar">
                    <h2 class="section-title">Postuler à cette offre</h2>
                    
                    <form id="candidature-form" action="traitement-candidature.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="offre_id" value="1">
                        
                        <div class="file-upload-container">
                            <label class="file-upload-label" for="cv">CV</label>
                            <div class="file-input-container">
                                <input type="file" id="cv" name="cv" class="file-input" accept=".pdf" required>
                                <span class="input-note">Format accepté: PDF uniquement</span>
                            </div>
                        </div>
                        
                        <div class="file-upload-container">
                            <label class="file-upload-label" for="lettre_motivation">Lettre de motivation</label>
                            <div class="file-input-container">
                                <input type="file" id="lettre_motivation" name="lettre_motivation" class="file-input" accept=".pdf" required>
                                <span class="input-note">Format accepté: PDF uniquement</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="postuler-btn">Postuler</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 - Tous droits réservés - Web4All</p>
    </footer>

    <script>
        // JavaScript pour afficher les noms des fichiers sélectionnés
        document.getElementById('cv').addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            if (fileName) {
                this.nextElementSibling.textContent = "Fichier sélectionné: " + fileName;
            }
        });
        
        document.getElementById('lettre_motivation').addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            if (fileName) {
                this.nextElementSibling.textContent = "Fichier sélectionné: " + fileName;
            }
        });
    </script>
</body>
</html>