<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Blog</title>
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
                <li><a href="Contact.php">Contact</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>

    <main class="blog-page">
        <section class="blog-hero">
            <h1>Le Blog LeBonPlan</h1>
            <p>Découvrez nos conseils, témoignages et guides pour réussir votre stage</p>
        </section>

        <section class="blog-posts">
            <article class="blog-card">
                <div class="blog-card-header">
                    <h2>Comment décrocher son premier stage</h2>
                    <span class="blog-date">15 février 2024</span>
                </div>
                <p class="blog-excerpt">
                    Nos experts partagent leurs conseils pour rédiger un CV percutant et réussir vos entretiens de stage.
                </p>
                <div class="blog-tags">
                    <span class="skill-tag">Candidature</span>
                    <span class="skill-tag">CV</span>
                    <span class="skill-tag">Entretien</span>
                </div>
                <a href="#" class="view-offer">Lire l'article</a>
            </article>

            <article class="blog-card">
                <div class="blog-card-header">
                    <h2>Les compétences les plus recherchées en 2024</h2>
                    <span class="blog-date">1 février 2024</span>
                </div>
                <p class="blog-excerpt">
                    Quelles sont les compétences technologiques et soft skills qui font la différence sur le marché des stages ?
                </p>
                <div class="blog-tags">
                    <span class="skill-tag">Compétences</span>
                    <span class="skill-tag">Tendances</span>
                    <span class="skill-tag">Emploi</span>
                </div>
                <a href="#" class="view-offer">Lire l'article</a>
            </article>
        </section>

        <section class="pagination">
            <button class="prev">Précédent</button>
            <div class="pages">
                <span class="current">1</span>
                <a href="#">2</a>
                <a href="#">3</a>
            </div>
            <button class="next">Suivant</button>
        </section>
    </main>

    <footer>
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
                    <li><a href="Blog.php">Blog</a></li>
                    <li><a href="FAQ.php">FAQ</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 - Tous droits réservés - Web4All</p>
        </div>
    </footer>
</body>
</html>