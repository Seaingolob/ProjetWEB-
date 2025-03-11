<?php
// Démarrer la session
session_start();?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Offres de Stage</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
    <nav>
            <div class="logo">
                <a href="Main.php"><h1>lebonplan</h1></a>
            </div>
    <div class="burger-menu">&#9776;</div> <!-- Icône du menu burger -->
    <ul class="main-nav" id="menu">
        <li><a href="Main.php">Accueil</a></li>
        <li><a href="Entreprises.php">Entreprises</a></li>
        <li><a href="Offres.php"class="active">Offres</a></li>
        <li><a href="Wishlist.php">Wishlist</a></li>
        <li><a href="Contact.php">Contact</a></li>
        <div class="logout-container">
            <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
        </div>
    </ul>
</nav>   
    </header>
    <br><br>
         <h2>Offres de stage (45 résultats)</h2>
         <select name="sort">
            <option value="recent">Plus récentes</option>
            <option value="company">Entreprise</option>
            <option value="duration">Durée</option>
        </select>
        <br>    <br>
            <article class="offer-card">
                <div class="offer-header">
                    <div class="company-info">
                        <img src="company-logo.jpg" alt="Logo Web4All" class="company-logo">
                        <div>
                            <h3>Stage - Développeur FullStack</h3>
                            <p class="company-name">Web4All</p>
                        </div>
                    </div>
                    <span class="heart" onclick="toggleHeart()">🤍</span>
                    <script src="script.js"></script>
                </div>
                <div class="offer-details">
                    <span class="location">Paris (75)</span>
                    <span class="duration">6 mois</span>
                    <span class="date">Publié le 15/02/2024</span>
                </div>
                <div class="skills">
                    <span class="skill-tag">React</span>
                    <span class="skill-tag">Node.js</span>
                    <span class="skill-tag">MongoDB</span>
                </div>
                <p class="description">
                    Nous recherchons un stagiaire développeur fullstack pour participer au développement de nos applications web...
                </p>
                <div class="offer-footer">
                    <a href="/offres/1234" class="view-details">Voir l'offre</a>
                    <a href="/postuler/1234" class="apply-btn">Postuler</a>
                </div>
            </article>

            <div class="pagination">
                <button class="prev">Précédent</button>
                <div class="pages">
                    <span class="current">1</span>
                    <a href="#">2</a>
                    <a href="#">3</a>
                </div>
                <button class="next">Suivant</button>
            </div>
        </section>
    </main>
    <br>
    <footer>
        <div class="pied">
        <div class="footer-content">
            <div class="footer-section">
                <h4>À propos</h4>
                <ul>
                    <li><a href="/qui-sommes-nous">Qui sommes-nous</a></li>
                    <li><a href="MentionLegales.html">Mentions légales</a></li>
                    <li><a href="CGU.html">CGU</a></li>

                </ul>
            </div>
            <div class="footer-section">
                <h4>Ressources</h4>
                <ul>
                    <li><a href="Blog.html">Blog</a></li>
                    <li><a href="FAQ.html">FAQ</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 - Tous droits réservés - Web4All</p>
        </div>
    </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>