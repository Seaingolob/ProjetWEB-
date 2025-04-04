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

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Vérifier si l'ID de l'offre est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Rediriger vers une page d'erreur ou la page principale
    header("Location: Offres.php");
    exit();
}


// Gérer les actions de suppression
if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] == 'delete') {
        $id = (int)$_GET['id'];
        $sql_delete_offer = "DELETE FROM offre WHERE id_offre = :id";
        $stmt_delete_offer = $connexion->prepare($sql_delete_offer);
        $stmt_delete_offer->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_delete_offer->execute();
        header("Location: VoirOffre.php");
        exit();
    }
}


// Récupérer l'ID de l'offre
$id_offre = intval($_GET['id']);

try {
    // Récupérer les détails de l'offre
    $stmt = $connexion->prepare("SELECT 
                                o.id_offre,
                                o.titre,
                                o.description,
                                o.duree_mois,
                                o.date_publication,
                                e.nom AS entreprise_nom,
                                e.description AS entreprise_description,
                                e.site AS entreprise_site,
                                a.nom_adresse,
                                v.nom_ville,
                                r.nom_region,
                                u.nom AS createur_nom,
                                u.prenom AS createur_prenom,
                                u.id_compte AS createur_id
                                FROM offre o
                                JOIN entreprise e ON o.id_entreprise = e.id_entreprise
                                JOIN adresse a ON e.id_adresse = a.id_adresse
                                JOIN ville v ON a.id_ville = v.id_ville
                                JOIN region r ON v.id_region = r.id_region
                                JOIN utilisateur u ON o.id_compte = u.id_compte
                                WHERE o.id_offre = :id");
    $stmt->bindParam(':id', $id_offre);
    $stmt->execute();
    $offre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$offre) {
        // L'offre n'existe pas, rediriger
        header("Location: Offres.php");
        exit();
    }

    // Récupérer les compétences associées à l'offre
    $stmt = $connexion->prepare("SELECT c.id_competence, c.nom
                                FROM contenir co
                                JOIN competence c ON co.id_competence = c.id_competence
                                WHERE co.id_offre = :id");
    $stmt->bindParam(':id', $id_offre);
    $stmt->execute();
    $competences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les secteurs d'activité de l'entreprise
    $stmt = $connexion->prepare("SELECT sa.id_secteur_activite, sa.nom
                                FROM travailler t
                                JOIN secteur_activite sa ON t.id_secteur_activite = sa.id_secteur_activite
                                JOIN entreprise e ON t.id_entreprise = e.id_entreprise
                                JOIN offre o ON o.id_entreprise = e.id_entreprise
                                WHERE o.id_offre = :id");
    $stmt->bindParam(':id', $id_offre);
    $stmt->execute();
    $secteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur actuel a déjà postulé à cette offre
    $stmt = $connexion->prepare("SELECT COUNT(*) AS postule
                                FROM postuler
                                WHERE id_compte = :id_compte AND id_offre = :id_offre");
    $stmt->bindParam(':id_compte', $_SESSION['user_id']);
    $stmt->bindParam(':id_offre', $id_offre);
    $stmt->execute();
    $postule = $stmt->fetch(PDO::FETCH_ASSOC)['postule'] > 0;

    // Vérifier si l'utilisateur a ajouté l'offre à sa wishlist
    $stmt = $connexion->prepare("SELECT COUNT(*) AS wishlist
                                FROM souhaiter
                                WHERE id_compte = :id_compte AND id_offre = :id_offre");
    $stmt->bindParam(':id_compte', $_SESSION['user_id']);
    $stmt->bindParam(':id_offre', $id_offre);
    $stmt->execute();
    $wishlist = $stmt->fetch(PDO::FETCH_ASSOC)['wishlist'] > 0;

    // Récupérer les évaluations de l'offre
    $stmt = $connexion->prepare("SELECT 
                                e.note, 
                                e.avis, 
                                u.nom, 
                                u.prenom,
                                u.id_compte
                                FROM evaluation e
                                JOIN utilisateur u ON e.id_compte = u.id_compte
                                WHERE e.id_offre = :id");
    $stmt->bindParam(':id', $id_offre);
    $stmt->execute();
    $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur actuel a déjà évalué cette offre
    $stmt = $connexion->prepare("SELECT COUNT(*) AS evalue
                                FROM evaluation
                                WHERE id_compte = :id_compte AND id_offre = :id_offre");
    $stmt->bindParam(':id_compte', $_SESSION['user_id']);
    $stmt->bindParam(':id_offre', $id_offre);
    $stmt->execute();
    $a_evalue = $stmt->fetch(PDO::FETCH_ASSOC)['evalue'] > 0;
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - Détail Offre</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js" defer></script>
    <script>
        window.onload = function() {
            postuleroffre();
        };
    </script>
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
    <br><br>
    <div class="offre-detail-container">
        <div class="offre-header">
            <h2><?php echo htmlspecialchars($offre['titre']); ?> - ID <?php echo $offre['id_offre']; ?></h2>
        </div>

        <div class="offre-content">
            <div class="offre-detail-grid">
                <div class="offre-main-info">
                    <div class="info-section">
                        <div class="form-title">Détails de l'offre</div>
                        <div class="info-row">
                            <span class="info-label">Date de publication:</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($offre['date_publication'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Durée:</span>
                            <span class="info-value"><?php echo $offre['duree_mois']; ?> mois</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Créé par:</span>
                            <span class="info-value">
                                <?php if ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'pilote'): ?>
                                    <a href="VoirEleve.php?id=<?php echo $offre['createur_id']; ?>"><?php echo htmlspecialchars($offre['createur_prenom'] . ' ' . $offre['createur_nom']); ?></a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($offre['createur_prenom'] . ' ' . $offre['createur_nom']); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="form-title">Description</div>
                        <p><?php echo nl2br(htmlspecialchars($offre['description'])); ?></p>
                    </div>

                    <div class="info-section">
                        <div class="form-title">Compétences requises</div>
                        <?php if (!empty($competences)): ?>
                            <div class="skill-tags">
                                <?php foreach ($competences as $competence): ?>
                                    <span class="skill-tag"><?php echo htmlspecialchars($competence['nom']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Aucune compétence spécifique requise.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="offre-side-info">
                    <div class="info-section">
                            <div class="form-title">Entreprise</div>
                            <h4><?php echo htmlspecialchars($offre['entreprise_nom']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($offre['entreprise_description'] ?? 'Aucune description disponible.')); ?></p>
                            <?php if (!empty($offre['entreprise_site'])): ?>
                                <a href="<?php echo htmlspecialchars($offre['entreprise_site']); ?>" target="_blank" class="company-site">Site web</a>
                            <?php endif; ?>

                            <?php if (!empty($secteurs)): ?>
                                <div class="sector-tags">
                                    <?php foreach ($secteurs as $secteur): ?>
                                        <span class="sector-tag"><?php echo htmlspecialchars($secteur['nom']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="form-title">Localisation</div>
                        <div class="location-card">
                            <p><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo htmlspecialchars($offre['nom_adresse']); ?></p>
                            <p><?php echo htmlspecialchars($offre['nom_ville']); ?>, <?php echo htmlspecialchars($offre['nom_region']); ?></p>
                        </div>
                    </div>
                </div>
            

            <div class="reviews-section">
                <div class="offre-actions">
                    <?php if ($_SESSION['user_type'] === 'etudiant'): ?>
                        <?php if (!$postule): ?>
                            <form id="postulerForm" action="Postuler.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $offre['id_offre']; ?>">

                                <div class="file-upload-container">
                                    <label class="file-upload-label" for="cv">CV</label>
                                    <div class="file-input-container">
                                        <div class="message" id="cv_message" style="display: none;">
                                            Veuillez insérer votre CV
                                        </div>
                                        <input type="file" id="cv" name="cv" class="file-input" accept=".pdf">
                                        <span class="input-note">Format accepté: PDF uniquement</span>
                                    </div>
                                </div>

                                <div class="file-upload-container">
                                    <label class="file-upload-label" for="lettre_motivation">Lettre de motivation</label>
                                    <div class="file-input-container">
                                        <div class="message" id="lettre_motivation_message" style="display: none;">
                                            Veuillez insérer votre lettre de motivation
                                        </div>
                                        <input type="file" id="lettre_motivation" name="lettre_motivation" class="file-input" accept=".pdf">
                                        <span class="input-note">Format accepté: PDF uniquement</span>
                                    </div>
                                </div>

                                <button type="submit" class="action-btn apply-btn">Postuler</button>
                            </form>
                        <?php else: ?>
                            <button class="action-btn applied-btn" disabled>Déjà postulé</button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($_SESSION['user_type'] === 'admin'): ?>
                        <button class="action-btn delete-btn" onclick="window.location.href='VoirOffre.php?action=delete&id=<?php echo $offre['id_offre']; ?>'">Supprimer</button>
                    <?php endif; ?>
                </div>
            <div class="info-section">            
                <div class="form-title">Évaluations</div>
                <?php if ($_SESSION['user_type'] === 'etudiant' && !$a_evalue): ?>
                    <div class="review-form-container">
                        <h4>Ajouter une évaluation</h4>
                        <form action="AjouterEvaluation.php" method="post">
                            <input type="hidden" name="id_offre" value="<?php echo $offre['id_offre']; ?>">
                            <div class="form-group">
                                <label for="note">Note:</label>
                                <select name="note" id="note" required>
                                    <option value="Excellent">Excellent</option>
                                    <option value="Très bien">Très bien</option>
                                    <option value="Bien">Bien</option>
                                    <option value="Moyen">Moyen</option>
                                    <option value="À éviter">À éviter</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="avis">Avis (facultatif):</label>
                                <textarea name="avis" id="avis" rows="4"></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Envoyer</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="reviews-list">
                    <?php if (!empty($evaluations)): ?>
                        <?php foreach ($evaluations as $evaluation): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <span class="reviewer-name">
                                        <a href="VoirEleve.php?id=<?php echo $evaluation['id_compte']; ?>"><?php echo htmlspecialchars($evaluation['prenom'] . ' ' . $evaluation['nom']); ?></a>
                                    </span>
                                    <span class="review-rating <?php echo strtolower(str_replace(' ', '-', $evaluation['note'])); ?>"><?php echo htmlspecialchars($evaluation['note']); ?></span>
                                </div>
                                <?php if (!empty($evaluation['avis'])): ?>
                                    <div class="review-content">
                                        <p><?php echo nl2br(htmlspecialchars($evaluation['avis'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-reviews">Aucune évaluation disponible pour cette offre.</p>
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

</body>

</html>
