<?php
// Simuler une liste d'offres de stage
$offres = array_map(function ($i) {
    $durees = [2, 3, 4, 6];
    $skills = ['React', 'Node.js', 'MongoDB', 'PHP', 'Laravel'];

    return [
        'titre' => "Stage - Développeur FullStack $i",
        'entreprise' => "Entreprise $i",
        'ville' => "Ville " . ($i % 10 + 1),
        'duree' => $durees[array_rand($durees)],
        'date' => date('d/m/Y', strtotime("-" . rand(0, 30) . " days")),
        'skills' => array_intersect_key($skills, array_flip(array_rand($skills, 3)))
    ];
}, range(1, 50)); // Générer 50 offres

// Paramètres de pagination
$itemsPerPage = 5;
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$totalPages = ceil(count($offres) / $itemsPerPage);

// Extraire les offres pour la page actuelle
$currentItems = array_slice($offres, ($page - 1) * $itemsPerPage, $itemsPerPage);
?>

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
            <ul class="main-nav">
                <li><a href="Main.php">Accueil</a></li>
                <li><a href="Entreprises.php">Entreprises</a></li>
                <li><a href="Offres.php" class="active">Offres</a></li>
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
            <h2>Rechercher une offre</h2>
            <form class="advanced-search">
                <div class="search-filters">
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

        <section class="offers-list">
            <div class="offers-header">
                <h2>Offres de stage (<?php echo count($offres); ?> résultats)</h2>
            </div>

            <?php foreach ($currentItems as $offre): ?>
                <article class="offer-card">
                    <h3><?php echo htmlspecialchars($offre['titre']); ?></h3>
                    <p class="company-name"><?php echo htmlspecialchars($offre['entreprise']); ?></p>
                    <p class="location">Lieu : <?php echo htmlspecialchars($offre['ville']); ?></p>
                    <p class="duration">Durée : <?php echo $offre['duree']; ?> mois</p>
                    <p class="date">Publié le <?php echo $offre['date']; ?></p>
                    <a href="#" class="view-details">Voir l'offre</a>
                </article>
            <?php endforeach; ?>
        </section>

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
    </main>

    <footer>
        <p>&copy; 2024 - Tous droits réservés - Web4All</p>
    </footer>
</body>
</html>
