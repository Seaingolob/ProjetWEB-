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

// Inclure le fichier de configuration de la base de données
require_once 'config.php';
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header("Location: connexion.php");
    exit();
}

// Récupérer les noms des entreprises
$sqlCompanies = "SELECT DISTINCT nom FROM entreprise ORDER BY nom";
$stmtCompanies = $connexion->prepare($sqlCompanies);
$stmtCompanies->execute();
$companies = $stmtCompanies->fetchAll(PDO::FETCH_COLUMN);

// Récupérer les noms des villes
$sqlCities = "SELECT DISTINCT nom_ville FROM ville ORDER BY nom_ville";
$stmtCities = $connexion->prepare($sqlCities);
$stmtCities->execute();
$cities = $stmtCities->fetchAll(PDO::FETCH_COLUMN);

// Récupérer les noms des competences
$sqlCompetences = "SELECT DISTINCT nom FROM competence ORDER BY nom";
$stmtCompetences = $connexion->prepare($sqlCompetences);
$stmtCompetences->execute();
$competences = $stmtCompetences->fetchAll(PDO::FETCH_COLUMN);

// Paramètres de pagination
$itemsPerPage = 5;
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);

// Initialiser les variables de recherche
$searchCompany = isset($_GET['company-name']) ? $_GET['company-name'] : '';
$searchLocation = isset($_GET['location']) ? $_GET['location'] : '';
$searchCompetences = isset($_GET['competences']) ? array_filter($_GET['competences']) : []; // Filtrer les valeurs vides

// Construire la requête SQL
$sqlCount = "SELECT COUNT(DISTINCT o.id_offre) FROM offre o 
JOIN entreprise e ON o.Id_entreprise = e.Id_entreprise
JOIN adresse ad ON e.Id_adresse = ad.Id_adresse
JOIN ville v ON ad.Id_ville = v.Id_ville";

$sqlOffres = "SELECT DISTINCT o.*, e.nom AS nom_entreprise, v.nom_ville 
FROM offre o 
JOIN entreprise e ON o.Id_entreprise = e.Id_entreprise
JOIN adresse ad ON e.Id_adresse = ad.Id_adresse
JOIN ville v ON ad.Id_ville = v.Id_ville";

// Conditions pour les compétences
if (!empty($searchCompetences)) {
    $sqlCount .= " JOIN contenir co ON o.id_offre = co.id_offre
                  JOIN competence c ON co.Id_competence = c.Id_competence";
    
    $sqlOffres .= " JOIN contenir co ON o.id_offre = co.id_offre
                   JOIN competence c ON co.Id_competence = c.Id_competence";
}

// Ajouter les conditions de recherche
$whereConditions = [];
$params = [];

if (!empty($searchCompany)) {
    $whereConditions[] = "e.nom = :company";
    $params[':company'] = $searchCompany;
}

if (!empty($searchLocation)) {
    $whereConditions[] = "(v.nom_ville LIKE :location OR r.nom_région LIKE :location)";
    
    // S'assurer que region est inclus dans la requête
    if (strpos($sqlOffres, "JOIN région r") === false) {
        $sqlOffres = str_replace(
            "JOIN ville v ON ad.Id_ville = v.Id_ville",
            "JOIN ville v ON ad.Id_ville = v.Id_ville JOIN région r ON v.Id_région = r.Id_région",
            $sqlOffres
        );
        
        $sqlCount = str_replace(
            "JOIN ville v ON ad.Id_ville = v.Id_ville",
            "JOIN ville v ON ad.Id_ville = v.Id_ville JOIN région r ON v.Id_région = r.Id_région",
            $sqlCount
        );
    }
    
    $params[':location'] = '%' . $searchLocation . '%';
}

// Recherche par compétences
if (!empty($searchCompetences)) {
    if (count($searchCompetences) > 1) {
        // Requête pour trouver les offres qui ont toutes les compétences sélectionnées
        $whereConditions[] = "o.id_offre IN (
            SELECT co2.id_offre
            FROM contenir co2
            JOIN competence c2 ON co2.Id_competence = c2.Id_competence
            WHERE c2.nom IN (" . implode(', ', array_map(function($i) { return ':comp_in_' . $i; }, array_keys($searchCompetences))) . ")
            GROUP BY co2.id_offre
            HAVING COUNT(DISTINCT c2.Id_competence) = " . count($searchCompetences) . "
        )";
        
        // Ajouter les paramètres pour la sous-requête
        foreach ($searchCompetences as $index => $competence) {
            $params[':comp_in_' . $index] = $competence;
        }
    } else {
        // Si une seule compétence, la requête est plus simple
        $competenceParam = ':competence0';
        $whereConditions[] = "c.nom = $competenceParam";
        $params[$competenceParam] = reset($searchCompetences);
    }
}

// Function pour construire les paramètres d'URL pour la pagination
function buildPaginationParams($page, $searchCompany, $searchLocation, $searchCompetences) {
    $params = "page=" . $page;
    
    if (!empty($searchCompany)) {
        $params .= "&company-name=" . urlencode($searchCompany);
    }
    
    if (!empty($searchLocation)) {
        $params .= "&location=" . urlencode($searchLocation);
    }
    
    if (!empty($searchCompetences)) {
        foreach ($searchCompetences as $competence) {
            $params .= "&competences[]=" . urlencode($competence);
        }
    }
    
    return $params;
}

// Assembler la clause WHERE si nécessaire
if (!empty($whereConditions)) {
    $whereClause = " WHERE " . implode(' AND ', $whereConditions);
    $sqlCount .= $whereClause;
    $sqlOffres .= $whereClause;
}

// Ajouter l'ordre de tri par date décroissante (plus récentes en premier)
$sqlOffres .= " ORDER BY o.date_publication DESC";

// Ajouter la pagination
$sqlOffres .= " LIMIT :offset, :limit";
$params[':offset'] = ($page - 1) * $itemsPerPage;
$params[':limit'] = $itemsPerPage;

try {
    // Exécuter la requête pour compter le nombre total d'offres
    $stmtCount = $connexion->prepare($sqlCount);
    
    // Lier les paramètres pour la requête de comptage
    foreach ($params as $key => $value) {
        // Ne pas lier les paramètres de pagination pour la requête COUNT
        if ($key !== ':offset' && $key !== ':limit') {
            $stmtCount->bindValue($key, $value);
        }
    }
    
    $stmtCount->execute();
    $totalOffres = $stmtCount->fetchColumn();
    $totalPages = ceil($totalOffres / $itemsPerPage);
    
    // Exécuter la requête pour récupérer les offres de la page actuelle
    $stmtOffres = $connexion->prepare($sqlOffres);
    
    // Lier tous les paramètres
    foreach ($params as $key => $value) {
        if ($key === ':offset' || $key === ':limit') {
            $stmtOffres->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmtOffres->bindValue($key, $value);
        }
    }
    
    $stmtOffres->execute();
    $offres = $stmtOffres->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // En cas d'erreur, afficher un message
    die("Erreur lors de la récupération des offres : " . $e->getMessage());
}

// Récupérer les competences pour chaque offre
function getCompetencesForOffer($connexion, $idOffre) {
    $sql = "SELECT c.nom FROM competence c 
    JOIN contenir co ON c.Id_competence = co.Id_competence 
    WHERE co.id_offre = :idOffre";
    
    try {
        $stmt = $connexion->prepare($sql);
        $stmt->bindValue(':idOffre', $idOffre, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}
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
            <div class="burger-menu">&#9776;</div>
            <ul class="main-nav" id="menu">
                <li><a href="Main.php">Accueil</a></li>
                <li><a href="Offres.php" class="active">Offres</a></li>
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
    <main>
        <br><br>
        <section class="search-section">
            <h2>Rechercher une offre</h2>
            <form class="advanced-search" method="GET" action="Offres.php">
                <div class="search-filters">
                    <label for="company-name">Nom de l'entreprise</label>
                    <select id="company-name" name="company-name">
                        <option value="">Sélectionner une entreprise</option>
                        <?php foreach ($companies as $company): ?>
                        <option value="<?php echo htmlspecialchars($company); ?>" <?php echo $searchCompany === $company ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($company); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="location">Localisation</label>
                    <select id="location" name="location">
                        <option value="">Sélectionner une ville</option>
                        <?php foreach ($cities as $city): ?>
                        <option value="<?php echo htmlspecialchars($city); ?>" <?php echo $searchLocation === $city ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($city); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="competence">Compétences</label>
                    <div id="competences-container">
                        <?php if (empty($searchCompetences)): ?>
                        <!-- Si aucune compétence n'est sélectionnée, afficher une seule ligne -->
                        <div class="competence-row">
                            <select name="competences[]" class="competence-select">
                                <option value="">Sélectionner une compétence</option>
                                <?php foreach ($competences as $competence): ?>
                                <option value="<?php echo htmlspecialchars($competence); ?>">
                                    <?php echo htmlspecialchars($competence); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="add-competence-btn">+</button>
                            <button type="button" class="remove-competence-btn" style="display: none;">-</button>
                        </div>
                        <?php else: ?>
                        <!-- Pour chaque compétence sélectionnée, afficher une ligne -->
                        <?php foreach ($searchCompetences as $index => $selectedCompetence): ?>
                        <div class="competence-row">
                            <select name="competences[]" class="competence-select">
                                <option value="">Sélectionner une compétence</option>
                                <?php foreach ($competences as $competence): ?>
                                <option value="<?php echo htmlspecialchars($competence); ?>" <?php echo $selectedCompetence === $competence ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($competence); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="add-competence-btn">+</button>
                            <button type="button" class="remove-competence-btn" style="<?php echo $index === 0 && count($searchCompetences) === 1 ? 'display: none;' : ''; ?>">-</button>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div><br></div>
                    <button type="submit">Rechercher</button>
                </div>
            </form>
        </section>
        <br>
        <section class="offers-list">
            <div class="offers-header">
                <h2>Offres de stage (<?php echo $totalOffres; ?> résultats)</h2>
            </div>
            <?php if (empty($offres)): ?>
            <p class="no-results">Aucune offre ne correspond à votre recherche.</p>
            <?php else: ?>
            <?php foreach ($offres as $offre): ?>
            <?php 
            // Récupérer les competences pour cette offre
            $competences = getCompetencesForOffer($connexion, $offre['id_offre']);
            // Vérifier si l'utilisateur a liké l'offre
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                $sqlLiked = "SELECT * FROM souhaiter WHERE id_compte = :user_id AND id_offre = :offer_id";
                $stmtLiked = $connexion->prepare($sqlLiked);
                $stmtLiked->execute([':user_id' => $userId, ':offer_id' => $offre['id_offre']]);
                $isLiked = $stmtLiked->rowCount() > 0;
            } else {
                $isLiked = false;
            }
            ?>
            <article class="offer-card">
                <div class="offre-titre">
                <p><?php echo htmlspecialchars($offre['titre']); ?></p>
                </div>
                <div class="offre-texte">
                    <div class="left">
                        <p>Nom : <?php echo htmlspecialchars($offre['nom_entreprise']); ?></p>
                        <p>Lieu : <?php echo htmlspecialchars($offre['nom_ville'] ?? 'Non spécifié'); ?></p>
                    </div>
                    <div class="right">
                        <p>Durée : <?php echo htmlspecialchars($offre['duree_mois']); ?> mois</p>
                        <p>Publié le <?php echo date('d/m/Y', strtotime($offre['date_publication'])); ?></p>
                    </div>
                </div>
                <div class="comp">
                <?php if (!empty($competences)): ?>
                <div class="skills">
                    <?php foreach ($competences as $competence): ?>
                    <span class="skill-tag"><?php echo htmlspecialchars($competence); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="no-skills">Aucune compétence spécifiée</p>
                <?php endif; ?>
                <a href="VoirOffre.php?id=<?php echo $offre['id_offre']; ?>" class="view-details">Voir l'offre</a>
                </div>

                <?php if ($_SESSION['user_type'] === 'etudiant'): ?>
                <div class="heart" data-id="<?php echo $offre['id_offre']; ?>" onclick="toggleHeart(event)">
                <?php echo $isLiked ? '❤️' : '🤍'; ?>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>
        </section>
        
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?php echo buildPaginationParams($page - 1, $searchCompany, $searchLocation, $searchCompetences); ?>" class="prev-page">« Précédent</a>
            <?php else: ?>
            <span class="disabled">« Précédent</span>
            <?php endif; ?>
            
            <div class="page-numbers">
                <?php 
                // Afficher un nombre limité de pages dans la pagination
                $maxPagesToShow = 5;
                $startPage = max(1, min($page - floor($maxPagesToShow / 2), $totalPages - $maxPagesToShow + 1));
                $endPage = min($startPage + $maxPagesToShow - 1, $totalPages);
                
                if ($startPage > 1): ?>
                <a href="?<?php echo buildPaginationParams(1, $searchCompany, $searchLocation, $searchCompetences); ?>">1</a>
                <?php if ($startPage > 2): ?>
                <span class="ellipsis">...</span>
                <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?<?php echo buildPaginationParams($i, $searchCompany, $searchLocation, $searchCompetences); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                <span class="ellipsis">...</span>
                <?php endif; ?>
                <a href="?<?php echo buildPaginationParams($totalPages, $searchCompany, $searchLocation, $searchCompetences); ?>"><?php echo $totalPages; ?></a>
                <?php endif; ?>
            </div>
            
            <?php if ($page < $totalPages): ?>
            <a href="?<?php echo buildPaginationParams($page + 1, $searchCompany, $searchLocation, $searchCompetences); ?>" class="next-page">Suivant »</a>
            <?php else:?>
            <span class="disabled">Suivant »</span>
            <?php endif; ?>
        </div>
    </main>
    <br><br>
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
    <script src="script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Conteneur de compétences
        const competencesContainer = document.getElementById('competences-container');
        
        // Configurer l'affichage initial des boutons
        if (competencesContainer.querySelectorAll('.competence-row').length > 1) {
            document.querySelectorAll('.remove-competence-btn').forEach(btn => {
                btn.style.display = 'inline-flex';
            });
        }
        
        // Gérer les clics sur les boutons + et -
        competencesContainer.addEventListener('click', function(e) {
            // Si bouton d'ajout cliqué
            if (e.target.classList.contains('add-competence-btn')) {
                // Cloner le premier sélecteur de compétence
                const firstRow = competencesContainer.querySelector('.competence-row');
                const newRow = firstRow.cloneNode(true);
                
                // Réinitialiser la sélection
                newRow.querySelector('select').selectedIndex = 0;
                
                // Afficher le bouton de suppression pour toutes les lignes
                document.querySelectorAll('.remove-competence-btn').forEach(btn => {
                    btn.style.display = 'inline-flex';
                });
                
                // Ajouter la nouvelle ligne
                competencesContainer.appendChild(newRow);
            }
            
            // Si bouton de suppression cliqué
            if (e.target.classList.contains('remove-competence-btn')) {
                // Ne pas supprimer si c'est la dernière ligne
                if (competencesContainer.querySelectorAll('.competence-row').length > 1) {
                    e.target.closest('.competence-row').remove();
                    
                    // Cacher le bouton de suppression s'il ne reste qu'une seule ligne
                    if (competencesContainer.querySelectorAll('.competence-row').length === 1) {
                        competencesContainer.querySelector('.remove-competence-btn').style.display = 'none';
                    }
                }
            }
        });
        
    });
    </script>
</body>
</html>
