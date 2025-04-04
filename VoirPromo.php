
<?php
require 'config.php'; // Assurez-vous que ce fichier contient la connexion à la base de données

session_start(); 

if (!isset($_GET['id_promotion'])) {
    die("ID de promotion manquant.");
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    // La session a expiré, déconnecter l'utilisateur
    session_unset();
    session_destroy();
    header("Location: Connexion.php?expired=1");
    exit();
}

// Mettre à jour le timestamp de dernière activité
$_SESSION['last_activity'] = time();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header("Location: Connexion.php");
    exit();
}

// Vérifier si l'utilisateur est un administrateur ou un pilote
if ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'pilote') {
    // Rediriger vers la page principale
    header("Location: Main.php");
    exit();
}

$id_promotion = intval($_GET['id_promotion']);

try {
    // Récupérer les infos de la promotion
    $query = "SELECT nom FROM promotion WHERE id_promotion = :id_promotion";
    $stmt = $connexion->prepare($query);
    $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
    $stmt->execute();
    $promotion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$promotion) {
        die("Promotion non trouvée.");
    }

    // Nombre d'étudiants dans la promotion
    $query = "SELECT COUNT(*) AS nb_etudiants FROM appartenir WHERE id_promotion = :id_promotion";
    $stmt = $connexion->prepare($query);
    $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
    $stmt->execute();
    $nb_etudiants = $stmt->fetch(PDO::FETCH_ASSOC)['nb_etudiants'];

    // Pilote responsable de la promotion
    $query = "SELECT u.nom, u.prenom, u.id_compte FROM utilisateur u 
              INNER JOIN piloter p ON u.id_compte = p.id_compte 
              WHERE p.id_promotion = :id_promotion";
    $stmt = $connexion->prepare($query);
    $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
    $stmt->execute();
    $pilote = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pilote) {
        die("Pilote non trouvé.");
    }

    // Nombre moyen d'offres postulées par étudiant
    $query = "SELECT AVG(nb_postulations) AS moyenne_postulations FROM (
                SELECT COUNT(p.id_offre) AS nb_postulations FROM utilisateur u
                INNER JOIN appartenir a ON u.id_compte = a.id_compte
                LEFT JOIN postuler p ON u.id_compte = p.id_compte
                WHERE a.id_promotion = :id_promotion
                GROUP BY u.id_compte
              ) AS subquery";
    $stmt = $connexion->prepare($query);
    $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
    $stmt->execute();
    $moyenne_postulations = $stmt->fetch(PDO::FETCH_ASSOC)['moyenne_postulations'];
    $moyenne_postulations = $moyenne_postulations ? number_format($moyenne_postulations, 2) : "0";

    // Liste des étudiants qui n'ont postulé à aucune offre
    $query = "SELECT u.nom, u.prenom FROM utilisateur u
              INNER JOIN appartenir a ON u.id_compte = a.id_compte
              LEFT JOIN postuler p ON u.id_compte = p.id_compte
              WHERE a.id_promotion = :id_promotion AND p.id_compte IS NULL";
    $stmt = $connexion->prepare($query);
    $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
    $stmt->execute();
    $etudiants_sans_postulation = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques de la Promotion <?php echo htmlspecialchars($promotion['nom']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</head>
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
            <li><a href="Main.php" class="active">Accueil</a></li>
            <li><a href="Offres.php">Offres</a></li>
            <?php if ($_SESSION['user_type'] == 'etudiant'): ?>
                <li><a href="Wishlist.php">Wishlist</a></li>
            <?php endif; ?>
            <?php if ($_SESSION['user_type'] == 'admin'): ?>
                <li><a href="Admin.php">Espace-administration</a></li>
            <?php endif; ?>
            <?php if ($_SESSION['user_type'] == 'pilote'): ?>
                <li><a href="Admin.php">Espace pilote</a></li>
            <?php endif; ?>
            <li><a href="Contact.php">Contact</a></li>
            <div class="logout-container">
                <button id="logout-btn" onclick="window.location.href='logout.php';">Déconnexion</button>
            </div>
        </ul>
    </nav>
</header>
<br><br>
<div class="contact-form">
<h1>Statistiques de la <?php echo htmlspecialchars($promotion['nom']); ?></h1>
<p><strong>Nombre d'étudiants :</strong> <?php echo $nb_etudiants; ?></p>
<p><strong>Pilote responsable :</strong> 
    <a href="VoirEleve.php?id=<?php echo $pilote['id_compte']; ?>">
        <?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?>
    </a>
</p>
<p><strong>Moyenne des offres postulées par étudiant :</strong> <?php echo $moyenne_postulations; ?></p>

<h2>Élèves n'ayant postulé à aucune offre :</h2>
<ul>
    <?php if (count($etudiants_sans_postulation) > 0): ?>
        <?php foreach ($etudiants_sans_postulation as $etudiant): ?>
            <li><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></li>
        <?php endforeach; ?>
    <?php else: ?>
        <li>Aucun élève n'est sans postulation.</li>
    <?php endif; ?>
    </div>
</ul>
<div class="graphe">
<canvas id="myPieChart" width="500" height="500"></canvas>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('myPieChart').getContext('2d');
    const myPieChart = new Chart(ctx, {
        type: 'pie', // Type de graphique
        data: {
            labels: ['Postulé au moins à un stage', 'Pas de postulation'], // Labels des données
            datasets: [{
                label: 'Postulations des étudiants',
                data: [<?php echo $nb_etudiants - count($etudiants_sans_postulation); ?>, <?php echo count($etudiants_sans_postulation); ?>], // Données
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw;
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
</body>
</html>











