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

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header("Location: connexion.php");
    exit();
}

// Inclure le fichier de configuration
require_once "config.php";

 // Récupérer les offres avec leurs notes moyennes
 $sql = "SELECT o.id_offre, o.titre, o.duree_mois, o.date_publication, o.id_entreprise, 
 ev.nom AS nom_entreprise, v.nom_ville, AVG(e.note) AS moyenne_note
 FROM offre o
 LEFT JOIN evaluation e ON o.id_offre = e.id_offre
 JOIN entreprise ev ON o.id_entreprise = ev.id_entreprise
 JOIN adresse ad ON ev.id_adresse = ad.id_adresse
 JOIN ville v ON ad.id_ville = v.id_ville
 LEFT JOIN contenir co ON o.id_offre = co.id_offre
 LEFT JOIN competence c ON co.id_competence = c.id_competence
 GROUP BY o.id_offre, ev.nom, v.nom_ville
 ORDER BY moyenne_note DESC
 LIMIT 2";
$stmt = $connexion->prepare($sql);
$stmt->execute();
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getCompetencesForOffer($connexion, $id_offre) {
    $sql = "SELECT c.nom 
            FROM competence c
            INNER JOIN contenir co ON c.id_competence = co.id_competence
            WHERE co.id_offre = :id_offre";
    $stmt = $connexion->prepare($sql);
    $stmt->execute([':id_offre' => $id_offre]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Recherche de stages</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
</head>
<body>
    <div class="cookie-banner" id="cookie-banner">
        <p>Nous utilisons des cookies pour améliorer votre expérience sur notre site...</p>
        <button id="accept-cookies">Accepter</button>
    </div>
    <header>
        <nav>
            <div class="logo">
                <a href="Main.php"><h1>lebonplan</h1></a>
            </div>
            <div class="burger-menu">&#9776;</div>
            <ul class="main-nav" id="menu">
                <li><a href="Main.php" class="active">Accueil</a></li>
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
    <br><br><br>
    <div class="slogan">
        <h1>Lebonplan</h1>
        <p>Trouvez le stage de vos rêves en quelques clics !</p>
    </div>
    <br>
    <main>
        <section class="offers-list">

        <?php if (empty($offres)): ?>
            <p class="no-results">Aucune offre ne correspond à votre recherche.</p>
        <?php else: ?>
            <div class="contact-form">
            <h2>Offres de stage en vedettes</h2>
            <br>
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
                    <h3><?php echo htmlspecialchars($offre['titre']); ?></h3>
                    <p class="company-name"><?php echo isset($offre['nom_entreprise']) ? htmlspecialchars($offre['nom_entreprise']) : 'Entreprise non spécifiée'; ?></p>
                    <p class="location">Lieu : <?php echo isset($offre['nom_ville']) ? htmlspecialchars($offre['nom_ville']) : 'Non spécifié'; ?></p>
                    <p class="duration">Durée : <?php echo htmlspecialchars($offre['duree_mois']); ?> mois</p>
                    <p class="date">Publié le <?php echo date('d/m/Y', strtotime($offre['date_publication'])); ?></p>
                    
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
                    
                    <?php if ($_SESSION['user_type'] === 'etudiant'): ?>
                    <div class="heart" data-id="<?php echo $offre['id_offre']; ?>" onclick="toggleHeart(event)">
                        <?php echo $isLiked ? '❤️' : '🤍'; ?>
                    </div>
                    <?php endif; ?>
                </article>
                
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>


        <section class="statistics">
            <h2>Nos chiffres clés</h2>
            <br>
            
            <div class="stats-container">
                <div class="stat-item">
                    <p class="stat-number">500+</p>
                    <p class="stat-label">Entreprises partenaires</p>
                </div>
                <div class="stat-item">
                    <p class="stat-number">1000+</p>
                    <p class="stat-label">Offres de stage</p>
                </div>
                <div class="stat-item">
                    <p class="stat-number">5000+</p>
                    <p class="stat-label">Étudiants inscrits</p>
                </div>
            </div>
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
                        <li><a href="FAQ.php">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 - Tous droits réservés - Web4All</p>
            </div>
        </div>
    </footer>

</body>
</html>