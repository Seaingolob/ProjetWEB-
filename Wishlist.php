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


// Vérifier que l'utilisateur est un étudiant
if ($_SESSION['user_type'] !== 'etudiant') {
    // Rediriger vers la page principale si ce n'est pas un étudiant
    header("Location: Main.php");
    exit();
}

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

// Vérifier si l'id_compte est défini
if (!isset($_SESSION['user_id'])) {
    die("Erreur : l'utilisateur n'est pas correctement connecté.");
}

$userId = $_SESSION['user_id'];

// Récupérer les offres de la wishlist
$sql = "SELECT o.*, e.nom AS nom_entreprise, v.nom_ville 
        FROM offre o 
        JOIN entreprise e ON o.Id_entreprise = e.Id_entreprise
        JOIN adresse ad ON e.Id_adresse = ad.Id_adresse
        JOIN ville v ON ad.Id_ville = v.Id_ville
        JOIN souhaiter s ON o.id_offre = s.id_offre
        WHERE s.id_compte = :user_id
        ORDER BY o.date_publication DESC";

try {
    $stmt = $connexion->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des offres: " . $e->getMessage());
}

// Fonction pour récupérer les compétences pour chaque offre
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
<header class="header">
    <nav>
            <div class="logo">
                <a href="Main.php"><h1>lebonplan</h1></a>
            </div>
            <div class="user-info-left"> 
                <a href="VoirEleve.php?id=<?php echo $_SESSION['user_id']; ?>" class="profile-link">
                    👤 <?php echo $_SESSION['user_name']; ?>
                </a>
            </div>
    <div class="burger-menu">&#9776;</div> <!-- Icône du menu burger -->
    <ul class="main-nav" id="menu">
        <li><a href="Main.php">Accueil</a></li>

        <li><a href="Offres.php">Offres</a></li>
        <li><a href="Wishlist.php"class="active">Wishlist</a></li>
        <li><a href="Contact.php">Contact</a></li>
        <div class="logout-container">
            <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
        </div>
    </ul>
</nav> 
    </header>
    <main>
        <section class="offers-list">
            <br><br>
            <h2>Mes offres favorites (<?php echo count($offres); ?>)</h2>
            <?php if (empty($offres)): ?>
                <div class="empty-wishlist">
                    <p>Votre wishlist est vide.</p>
                </div>
            <?php else: ?>
            <?php foreach ($offres as $offre): ?>
            <?php 
            // Récupérer les competences pour cette offre
            $competences = getCompetencesForOffer($connexion, $offre['id_offre']);
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
                
                <div class="heart liked" data-id="<?php echo $offre['id_offre']; ?>" onclick="toggleHeart(event)">❤️</div>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
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
                        <li><a href="Blog.php">Blog</a></li>
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
</body>
</html>