<?php
// Démarrer la session
session_start();
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header("Location: connexion.php");
    exit();
}

// Inclure le fichier de configuration
require_once 'config.php';

// Gérer les actions de suppression
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete_user') {
        // Supprimer l'utilisateur
        $sql_delete_user = "DELETE FROM utilisateur WHERE id_compte = :id";
        $stmt_delete_user = $connexion->prepare($sql_delete_user);
        $stmt_delete_user->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_delete_user->execute();

    
    } elseif ($_GET['action'] === 'delete_offer') {
        // Supprimer l'offre
        $sql_delete_offer = "DELETE FROM offre WHERE id_offre = :id";
        $stmt_delete_offer = $connexion->prepare($sql_delete_offer);
        $stmt_delete_offer->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_delete_offer->execute();
    }
    // Rediriger pour éviter la répétition de l'action
    header("Location: Admin.php?tab=" . $_GET['tab']);
    exit();
}

// Définir les paramètres de pagination pour les utilisateurs
$itemsPerPage = 4; // Nombre d'éléments par page pour les utilisateurs
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Définir les paramètres de pagination pour les offres
$itemsPerPageOffres = 4; // Nombre d'éléments par page pour les offres
$pageOffres = isset($_GET['pageOffres']) ? (int)$_GET['pageOffres'] : 1;
$offsetOffres = ($pageOffres - 1) * $itemsPerPageOffres;

// Récupérer le terme de recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Requête pour récupérer le nombre total d'utilisateurs
$sql_count = "SELECT COUNT(*) FROM utilisateur WHERE nom LIKE :search OR prenom LIKE :search";
$stmt_count = $connexion->prepare($sql_count);
$stmt_count->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt_count->execute();
$totalItems = $stmt_count->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

// Requête pour récupérer les utilisateurs avec limite et offset
$sql = "SELECT * FROM utilisateur WHERE nom LIKE :search OR prenom LIKE :search LIMIT :limit OFFSET :offset";
$stmt = $connexion->prepare($sql);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Requête pour récupérer le nombre total d'offres
$sql_count_offres = "SELECT COUNT(*) FROM offre WHERE titre LIKE :search";
$stmt_count_offres = $connexion->prepare($sql_count_offres);
$stmt_count_offres->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt_count_offres->execute();
$totalItemsOffres = $stmt_count_offres->fetchColumn();
$totalPagesOffres = ceil($totalItemsOffres / $itemsPerPageOffres);

// Requête pour récupérer les offres avec limite et offset
$sql_offres = "SELECT * FROM offre WHERE titre LIKE :search LIMIT :limit OFFSET :offset";
$stmt_offres = $connexion->prepare($sql_offres);
$stmt_offres->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt_offres->bindParam(':limit', $itemsPerPageOffres, PDO::PARAM_INT);
$stmt_offres->bindParam(':offset', $offsetOffres, PDO::PARAM_INT);
$stmt_offres->execute();
$offres = $stmt_offres->fetchAll(PDO::FETCH_ASSOC);
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
            <div class="burger-menu">☰</div>
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
                <li><a href="Admin.php" class="active">Espace-pilote</a></li>
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
                <button class="admin-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'utilisateur' ? 'active' : ''; ?>" id="btn-utilisateur" onclick="window.location.href='Admin.php?tab=utilisateur';">Utilisateur</button>
                <button class="admin-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'offre' ? 'active' : ''; ?>" id="btn-offre" onclick="window.location.href='Admin.php?tab=offre';">Offre</button>
            </div>
            <div class="search-bar">
                <input type="text" id="search-input" name="search" placeholder="Recherchez par nom..." value="<?php echo htmlspecialchars($search); ?>">
                <button id="search-btn" onclick="search()">Rechercher</button>
            </div>
            <div class="admin-content">

                
                <!-- Section Utilisateurs -->
                <div class="user-management <?php echo isset($_GET['tab']) && $_GET['tab'] === 'utilisateur' ? 'active' : ''; ?>" id="section-utilisateur">
                    <h3>Gestion des utilisateurs :</h3>
                    <?php if ($_SESSION['user_type'] === 'admin'): ?>
                    <button class="action-btn" onclick="window.location.href='FormulaireUtilisateur.php';">Ajouter un utilisateur</button>
                    <?php endif; ?>
                    
                    <div class="users-grid">
                        <?php
                        if (count($utilisateurs) > 0) {
                            foreach ($utilisateurs as $utilisateur) {
                                echo "<div class='user-card'>";
                                echo "<div class='user-header'>ID : " . $utilisateur["id_compte"] . "</div>";
                                echo "<div class='user-details'>";
                                echo "<p><strong>Nom:</strong> " . $utilisateur["nom"] . " " . $utilisateur["prenom"] . "</p>";
                                echo "<p><strong>Email:</strong> " . $utilisateur["mail"] . "</p>";
                                echo "<p><strong>Téléphone:</strong> " . $utilisateur["telephone"] . "</p>";
                                echo "</div>";
                                echo "<div class='user-actions'>";
                                echo "<span>Actions possibles</span>";
                                echo "<div class='action-buttons'>";
                                echo "<button class='view-btn' onclick=\"window.location.href='VoirEleve.php?id=" . $utilisateur["id_compte"] . "';\">Voir</button>";
                                if ($_SESSION['user_type'] === 'admin') {
                                    echo "<button class='delete-btn' onclick=\"window.location.href='Admin.php?action=delete_user&id=" . $utilisateur["id_compte"] . "&tab=utilisateur';\">Supprimer</button>";
                                }
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                            }
                        } else {
                            echo "Aucun utilisateur trouvé.";
                        }
                        ?>
                    </div>
                    <!-- Pagination -->
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&tab=utilisateur&search=<?php echo urlencode($search); ?>" class="prev-page">« Précédent</a>
                        <?php else: ?>
                            <span class="disabled">« Précédent</span>
                        <?php endif; ?>

                        <div class="page-numbers">
                            <?php 
                            $maxPagesToShow = 5;
                            $startPage = max(1, min($page - floor($maxPagesToShow / 2), $totalPages - $maxPagesToShow + 1));
                            $endPage = min($startPage + $maxPagesToShow - 1, $totalPages);

                            if ($startPage > 1): ?>
                                <a href="?page=1&tab=utilisateur&search=<?php echo urlencode($search); ?>">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="ellipsis">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&tab=utilisateur&search=<?php echo urlencode($search); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="ellipsis">...</span>
                                <?php endif; ?>
                                <a href="?page=<?php echo $totalPages; ?>&tab=utilisateur&search=<?php echo urlencode($search); ?>"><?php echo $totalPages; ?></a>
                            <?php endif; ?>
                        </div>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&tab=utilisateur&search=<?php echo urlencode($search); ?>" class="next-page">Suivant »</a>
                        <?php else: ?>
                            <span class="disabled">Suivant »</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section Offres -->
                <div class="offer-management <?php echo isset($_GET['tab']) && $_GET['tab'] === 'offre' ? 'active' : ''; ?>" id="section-offre">
                    <h3>Gestion des offres :</h3>
                    <button class="action-btn" onclick="window.location.href='FormulaireOffres.php';">Ajouter une offre</button>
                    
                    <div class="offers-container">
                        <?php
                        if (count($offres) > 0) {
                            foreach ($offres as $offre) {
                                echo "<div class='offer-card'>";
                                echo "<div class='offer-header'>ID : " . $offre["id_offre"] . "<br>" . $offre["titre"] . "</div>";
                                echo "<div class='offer-details'>";
                                echo "<p><strong>Description:</strong> " . $offre["description"] . "</p>";
                                echo "<p><strong>Durée:</strong> " . $offre["duree_mois"] . " mois</p>";
                                echo "<p><strong>Date de publication:</strong> " . $offre["date_publication"] . "</p>";
                                echo "</div>";
                                echo "<div class='offer-actions'>";
                                echo "<span>Actions possibles</span>";
                                echo "<div class='action-buttons'>";
                                echo "<button class='view-btn' onclick=\"window.location.href='VoirOffre.php?id=" . $offre["id_offre"] . "';\">Voir</button>";
                                if ($_SESSION['user_type'] === 'admin') {
                                    echo "<button class='delete-btn' onclick=\"window.location.href='Admin.php?action=delete_offer&id=" . $offre["id_offre"] . "&tab=offre';\">Supprimer</button>";
                                }
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                            }
                        } else {
                            echo "Aucune offre trouvée.";
                        }
                        ?>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <?php if ($pageOffres > 1): ?>
                            <a href="?pageOffres=<?php echo $pageOffres - 1; ?>&tab=offre&search=<?php echo urlencode($search); ?>" class="prev-page">« Précédent</a>
                        <?php else: ?>
                            <span class="disabled">« Précédent</span>
                        <?php endif; ?>

                        <div class="page-numbers">
                            <?php 
                            $maxPagesToShow = 5;
                            $startPage = max(1, min($pageOffres - floor($maxPagesToShow / 2), $totalPagesOffres - $maxPagesToShow + 1));
                            $endPage = min($startPage + $maxPagesToShow - 1, $totalPagesOffres);

                            if ($startPage > 1): ?>
                                <a href="?pageOffres=1&tab=offre&search=<?php echo urlencode($search); ?>">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="ellipsis">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="?pageOffres=<?php echo $i; ?>&tab=offre&search=<?php echo urlencode($search); ?>" class="<?php echo $i == $pageOffres ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPagesOffres): ?>
                                <?php if ($endPage < $totalPagesOffres - 1): ?>
                                    <span class="ellipsis">...</span>
                                <?php endif; ?>
                                <a href="?pageOffres=<?php echo $totalPagesOffres; ?>&tab=offre&search=<?php echo urlencode($search); ?>"><?php echo $totalPagesOffres; ?></a>
                            <?php endif; ?>
                        </div>

                        <?php if ($pageOffres < $totalPagesOffres): ?>
                            <a href="?pageOffres=<?php echo $pageOffres + 1; ?>&tab=offre&search=<?php echo urlencode($search); ?>" class="next-page">Suivant »</a>
                        <?php else: ?>
                            <span class="disabled">Suivant »</span>
                        <?php endif; ?>
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
    function search() {
        const searchInput = document.getElementById('search-input').value;
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'utilisateur';
        window.location.href = `Admin.php?tab=${activeTab}&search=${searchInput}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tabBtns = document.querySelectorAll('.admin-tab');
        const sections = document.querySelectorAll('.admin-content > div');
        const searchInput = document.getElementById('search-input');
        
        function switchTab(targetId) {
            tabBtns.forEach(b => b.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            
            document.getElementById('btn-' + targetId).classList.add('active');
            document.getElementById('section-' + targetId).classList.add('active');
            
            if (targetId === 'utilisateur') {
                searchInput.placeholder = "Recherchez un utilisateur par ID...";
            } else if (targetId === 'offre') {
                searchInput.placeholder = "Recherchez une offre par ID...";
            }
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'utilisateur';
        switchTab(activeTab);
    });
    </script>
</body>
</html>