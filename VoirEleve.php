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

// Vérifier si l'ID de l'utilisateur est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Rediriger vers une page d'erreur ou la page principale
    header("Location: Main.php");
    exit();
}

// Récupérer l'ID de l'utilisateur
$id_compte = $_GET['id'];


// Gérer les actions de suppression
if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] == 'delete') {
        $id = (int)$_GET['id'];
        $sql_delete_offer = "DELETE FROM utilisateur WHERE id_compte = :id";
        $stmt_delete_offer = $connexion->prepare($sql_delete_offer);
        $stmt_delete_offer->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt_delete_offer->execute();
        header("Location: VoirEleve.php");
        exit();
    }
}


try {
    // Détecter le type d'utilisateur (étudiant, admin ou pilote)
    $stmt = $connexion->prepare("SELECT 
                                    CASE 
                                        WHEN EXISTS (SELECT 1 FROM etudiant WHERE id_compte = :id) THEN 'etudiant'
                                        WHEN EXISTS (SELECT 1 FROM admin WHERE id_compte = :id) THEN 'admin'
                                        WHEN EXISTS (SELECT 1 FROM pilote WHERE id_compte = :id) THEN 'pilote'
                                        ELSE 'inconnu'
                                    END AS user_type");
    $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
    $stmt->execute();
    $user_type_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_type = $user_type_result['user_type'];

    // Récupérer les informations de base de l'utilisateur
    $stmt = $connexion->prepare("SELECT u.id_compte, u.nom, u.prenom, u.mail, u.mot_de_passe, u.telephone 
                                FROM utilisateur u 
                                WHERE u.id_compte = :id");
    $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // L'utilisateur n'existe pas, rediriger
        header("Location: Main.php");
        exit();
    }

    // Informations spécifiques selon le type d'utilisateur
    $specific_info = [];

    if ($user_type === 'etudiant') {
        // Récupérer la wishlist de l'étudiant avec statut de postulation
        $stmt = $connexion->prepare("SELECT 
                                        o.id_offre, 
                                        o.titre, 
                                        e.nom as entreprise_nom, 
                                        GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') as competences, 
                                        v.nom_ville,
                                        CASE 
                                            WHEN EXISTS (SELECT 1 FROM postuler p WHERE p.id_compte = :id AND p.id_offre = o.id_offre) THEN 'Postulée'
                                            ELSE 'Non-postulée'
                                        END AS statut_postulation
                                    FROM souhaiter s 
                                    JOIN offre o ON s.id_offre = o.id_offre 
                                    JOIN entreprise e ON o.id_entreprise = e.id_entreprise 
                                    JOIN adresse a ON e.id_adresse = a.id_adresse 
                                    JOIN ville v ON a.id_ville = v.id_ville 
                                    LEFT JOIN contenir co ON o.id_offre = co.id_offre 
                                    LEFT JOIN competence c ON co.id_competence = c.id_competence 
                                    WHERE s.id_compte = :id 
                                    GROUP BY o.id_offre");
        $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
        $stmt->execute();
        $specific_info['wishlist'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les informations sur la promotion de l'étudiant

        $stmt = $connexion->prepare("SELECT 
        p.nom as promotion_nom,
        p.id_promotion,
        c.nom_campus, 
        a.debut, 
        a.fin
        FROM appartenir a
        JOIN promotion p ON a.id_promotion = p.id_promotion
        JOIN campus c ON p.id_campus = c.id_campus
        WHERE a.id_compte = :id
        AND (a.debut <= CURRENT_DATE() AND (a.fin >= CURRENT_DATE() OR a.fin IS NULL))
        ORDER BY a.debut DESC
        LIMIT 1");

        $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
        $stmt->execute();
        $specific_info['promotion'] = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($user_type === 'pilote') {
        // Récupérer les promotions pilotées

        $stmt = $connexion->prepare("SELECT 
        p.id_promotion,
        p.nom as promotion_nom, 
        c.nom_campus,
        pi.debut,
        pi.fin
        FROM piloter pi
        JOIN promotion p ON pi.id_promotion = p.id_promotion
        JOIN campus c ON p.id_campus = c.id_campus
        WHERE pi.id_compte = :id
        AND (pi.debut <= CURRENT_DATE() AND (pi.fin >= CURRENT_DATE() OR pi.fin IS NULL))
        ORDER BY pi.debut DESC");
        $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
        $stmt->execute();
        $specific_info['promotions_pilotees'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($user_type === 'admin') {
        // Aucune information spécifique supplémentaire pour admin
    }
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
    <title>LeBonPlan - Détail Utilisateur</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
</head>

<body>
    <header class="header">
        <nav>
            <div class="logo">
                <a href="Main.php">
                    <h1>lebonplan</h1>
                </a>
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

    <div class="admin-dashboard">
        <div class="admin-header">
            <h2>Voir : ID <?php echo $user['id_compte']; ?> : <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?> (<?php echo ucfirst($user_type); ?>)</h2>
        </div>

        <div class="user-detail-container">
            <div class="user-detail-content">
                <div class="user-profile-grid">
                    <div class="user-profile-info">
                        <!-- Informations communes à tous les types d'utilisateurs -->
                        <div class="user-info-row">
                            <span class="user-info-label">Nom :</span>
                            <span class="user-info-value"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></span>
                        </div>
                        <div class="user-info-row">
                            <span class="user-info-label">ID d'utilisateur :</span>
                            <span class="user-info-value"><?php echo $user['id_compte']; ?></span>
                        </div>

                        <div class="user-info-row">
                            <span class="user-info-label">Email :</span>
                            <span class="user-info-value"><?php echo htmlspecialchars($user['mail']); ?></span>
                        </div>

                        <div class="user-info-row">
                            <span class="user-info-label">Téléphone :</span>
                            <span class="user-info-value"><?php echo htmlspecialchars($user['telephone']); ?></span>
                        </div>

                        <!-- Informations spécifiques selon le type d'utilisateur -->
                        <?php if ($user_type === 'etudiant'): ?>
                            <?php if (!empty($specific_info['promotion'])): ?>
                                <div class="user-info-row">
                                    <span class="user-info-label">Promotion Actuelle:</span>
                                    <span class="user-info-value">
                                        <a href="VoirPromo.php?id_promotion=<?php echo urlencode($specific_info['promotion']['id_promotion']); ?>">
                                            <?php echo htmlspecialchars($specific_info['promotion']['promotion_nom']); ?>
                                        </a>
                                    </span>
                                </div>
                                <div class="user-info-row">
                                    <span class="user-info-label">Campus :</span>
                                    <span class="user-info-value"><?php echo htmlspecialchars($specific_info['promotion']['nom_campus']); ?></span>
                                </div>
                            <?php endif; ?>


                            <div class="user-info-row wishlist-section">
                                <span class="user-info-label">Wishlist Détaillée :</span>
                            </div>

                            <?php if (!empty($specific_info['wishlist'])): ?>
                                <?php foreach ($specific_info['wishlist'] as $offre): ?>
                                    <div class="wishlist-detail-card">
                                        <div class="wishlist-header">ID : <?php echo $offre['id_offre']; ?></div>
                                        <div class="wishlist-content">
                                            <p><strong>Intitulé:</strong> <?php echo htmlspecialchars($offre['titre']); ?></p>
                                            <p><strong>Entreprise:</strong> <?php echo htmlspecialchars($offre['entreprise_nom']); ?></p>
                                            <p><strong>Compétences:</strong> <?php echo htmlspecialchars($offre['competences'] ?? 'Non spécifiées'); ?></p>
                                            <p><strong>Localisation:</strong> <?php echo htmlspecialchars($offre['nom_ville']); ?></p>
                                            <p><strong>Statut:</strong> <span class="status-<?php echo strtolower(str_replace('-', '', $offre['statut_postulation'])); ?>"><?php echo htmlspecialchars($offre['statut_postulation']); ?></span></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-wishlist">Aucune offre dans la wishlist.</p>
                            <?php endif; ?>
                        <?php elseif ($user_type === 'pilote'): ?>
                            <div class="user-info-row">
                                <span class="user-info-label">Promotions pilotées :</span>
                            </div>

                            <?php if (!empty($specific_info['promotions_pilotees'])): ?>
                                <?php foreach ($specific_info['promotions_pilotees'] as $promotion): ?>
                                    <div class="promotion-detail-card">
                                        <div class="promotion-header">
                                            <a href="VoirPromo.php?id_promotion=<?php echo urlencode($promotion['id_promotion']); ?>">
                                                Promotion : <?php echo htmlspecialchars($promotion['promotion_nom']); ?>
                                            </a>
                                        </div>
                                        <div class="promotion-content">
                                            <p><strong>Campus:</strong> <?php echo htmlspecialchars($promotion['nom_campus']); ?></p>
                                            <p><strong>Début:</strong> <?php echo htmlspecialchars($promotion['debut']); ?></p>
                                            <?php if (!empty($promotion['fin'])): ?>
                                                <p><strong>Fin:</strong> <?php echo htmlspecialchars($promotion['fin']); ?></p>
                                            <?php else: ?>
                                                <p><strong>Fin:</strong> En cours</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-promotions">Aucune promotion pilotée.</p>
                            <?php endif;
                        endif; ?>
                        
                    </div>
                </div>

                <div class="user-action-buttons">
                    <button class="back-btn" onclick="window.location.href='Main.php';">Retour</button>
                    <?php if ($_SESSION['user_type'] === 'admin'): ?>
                        <button class="delete-btn" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet élève ?')) { window.location.href='VoirEleve.php?action=delete&id=<?php echo $user['id_compte']; ?>'; } return false;">Supprimer</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <div class="footer-content">

        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 - Tous droits réservés - Web4All</p>
        </div>
    </footer>

</body>

</html>