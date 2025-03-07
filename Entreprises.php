<?php
// Simuler une liste d'entreprises
$entreprises = array_map(function ($i) {
    return [
        'nom' => "Entreprise $i",
        'secteur' => "Secteur " . ($i % 5 + 1),
        'ville' => "Ville " . ($i % 10 + 1),
        'note' => rand(30, 50) / 10, // Note entre 3.0 et 5.0
        'offres' => rand(1, 15) // Nombre d'offres aléatoire
    ];
}, range(1, 17)); // Générer des fausses entreprises

// Paramètres de pagination
$itemsPerPage = 5;
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$totalPages = ceil(count($entreprises) / $itemsPerPage);

// Extraire les entreprises pour la page actuelle
$currentItems = array_slice($entreprises, ($page - 1) * $itemsPerPage, $itemsPerPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Entreprises</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="Main.php"><h1>lebonplan</h1></a>
            </div>
            <ul class="main-nav">
                <li><a href="Main.php">Accueil</a></li>
                <li><a href="Entreprises.php" class="active">Entreprises</a></li>
                <li><a href="Offres.php">Offres</a></li>
                <li><a href="Wishlist.php">Wishlist</a></li>
                <li><a href="Contact.php">Contact</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='Connection.html';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>

    <main>
        <section class="search-section">
            <br>
            <div><br></div>
            <h2>Rechercher une entreprise</h2>
            <form class="advanced-search">
                <div class="search-filters">
                    <br>
                    <label for="company-name">Nom de l'entreprise</label>
                    <input type="text" id="company-name" name="company-name" placeholder="Ex: Web4All">
                    <label for="location">Localisation</label>
                    <input type="text" id="location" name="location" placeholder="Ville ou région">
                    <div><br></div>
                    <button type="submit">Rechercher</button>
                </div>
            </form>
        </section>
        <br>

        <section class="companies-results">
            <div class="results-header">
                <h2>Entreprises (<?php echo count($entreprises); ?> résultats)</h2>
            </div>

            <div class="companies-grid">
                <?php foreach ($currentItems as $entreprise): ?>
                    <article class="company-card">
                        <div class="company-header">
                            <h3><?php echo htmlspecialchars($entreprise['nom']); ?></h3>
                            <span class="rating"><?php echo $entreprise['note']; ?>/5 ★</span>
                        </div>
                        <div class="company-info">
                            <p class="location"><?php echo htmlspecialchars($entreprise['ville']); ?></p>
                            <p class="sector"><?php echo htmlspecialchars($entreprise['secteur']); ?></p>
                            <p class="internships-count"><?php echo $entreprise['offres']; ?> stages disponibles</p>
                        </div>
                        <div class="company-actions">
                            <a href="#" class="view-profile">Voir le profil</a>
                            <a href="#" class="view-offers">Voir les stages</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <style>
                .pagination {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin-top: 20px;
                    gap: 10px;
                }

                .pagination a, .pagination span {
                    padding: 10px 15px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    text-decoration: none;
                    font-weight: bold;
                    transition: background 0.3s ease;
                }

                .pagination a {
                    color:rgb(0, 0, 0);
                    background: #fff;
                }

                .pagination a:hover {
                    background:#3498db;
                    color: white;
                }

                .pagination .active {
                    background:#3498db;
                    color: black;
                    border-color:rgb(122, 122, 122);
                }

                .pagination .disabled {
                    color: #ccc;
                    cursor: not-allowed;
                    background: #f9f9f9;
                    border: 1px solid #ddd;
                }

                .page-numbers {
                    display: flex;
                    gap: 5px;
                }
            </style>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="prev-page">« Précédent</a>
                <?php else: ?>
                    <span class="disabled">« Précédent</span>
                <?php endif; ?>

                <div class="page-numbers">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="next-page">Suivant »</a>
                <?php else: ?>
                    <span class="disabled">Suivant »</span>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>À propos</h4>
                <ul>
                    <li><a href="QSN.html">Qui sommes-nous</a></li>
                    <li><a href="MentionLegales.html">Mentions légales</a></li>
                    <li><a href="/cgu">CGU</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Ressources</h4>
                <ul>
                    <li><a href="/blog">Blog</a></li>
                    <li><a href="FAQ.html">FAQ</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 - Tous droits réservés - Web4All</p>
        </div>
    </footer>
</body>
</html>
