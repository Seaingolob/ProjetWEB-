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

// vérifier si l'utilisateur est un étudiant
if ($_SESSION['user_type'] == 'etudiant') {
    // Rediriger vers la page principale si ce n'est pas un administrateur
    header("Location: Main.php");
    exit();
}

// Inclusion du fichier de configuration
require_once 'config.php';

try {
    // Récupération de tous les campus
    $stmt = $connexion->prepare("SELECT id_campus, nom_campus FROM campus ORDER BY nom_campus");
    $stmt->execute();
    $campus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération de toutes les promotions
    $stmt = $connexion->prepare("SELECT id_promotion, nom FROM promotion ORDER BY nom");
    $stmt->execute();
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des régions
    $stmt = $connexion->prepare("SELECT id_region, nom_region FROM region ORDER BY nom_region");
    $stmt->execute();
    $regions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des villes (pour pré-charger)
    $stmt = $connexion->prepare("SELECT id_ville, nom_ville, id_region FROM ville ORDER BY nom_ville");
    $stmt->execute();
    $villes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur lors de la récupération des données: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Utilisateur</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
    <script>
        window.onload = function () {
            creationutilisateur();
        };
    </script>
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <a href="Main.php">
                    <h1>lebonplan</h1>
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
                    <li><a href="Admin.php" class="active">Espace-administration</a></li>
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
    </header class="header">
    <div class="form-container">
        <h2>Ajouter un Utilisateur</h2>
        <form action="processFormulaireUtilisateur.php" method="post" id="form-utilisateur">
            <div class="form-section">
                <h3>Informations personnelles</h3>

                <div class="form-group">
                    <label for="nom">Nom:</label>
                    <div class="message" id="nom_message">
                        Veuillez saisir un nom
                    </div>
                    <input type="text" id="nom" name="nom">
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom:</label>
                    <div class="message" id="prenom_message">
                        Veuillez saisir un prénom
                    </div>
                    <input type="text" id="prenom" name="prenom">
                </div>

                <div class="form-group">
                    <label for="mail">Email:</label>
                    <div class="message" id="mail_message">
                        Veuillez saisir un email valide
                    </div>
                    <input type="email" id="mail" name="mail">
                </div>

                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe:</label>
                    <div class="message" id="mot_de_passe_message">
                        Veuillez saisir un mot de passe
                    </div>
                    <input type="password" id="mot_de_passe" name="mot_de_passe">
                </div>

                <div class="form-group">
                    <label for="telephone">N° de Téléphone:</label>
                    <div class="message" id="telephone_message">
                        Veuillez saisir un numéro de téléphone
                    </div>
                    <input type="tel" id="telephone" name="telephone">
                </div>
            </div>

            <div class="form-section">
                <h3>Type d'utilisateur</h3>
                <div class="form-group">
                    <div class="radio-group form-group">
                        <input type="radio" id="type-etudiant" name="type_utilisateur" value="etudiant" checked>
                        <label for="type-etudiant">Étudiant</label>
                    </div>
                    <?php if ($_SESSION['user_type'] == 'admin'): ?>
                        <div class="radio-group form-group">
                            <input type="radio" id="type-pilote" name="type_utilisateur" value="pilote">
                            <label for="type-pilote">Pilote</label>
                        </div>

                        <div class="radio-group form-group">
                            <input type="radio" id="type-admin" name="type_utilisateur" value="admin">
                            <label for="type-admin">Administrateur</label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section commune pour Campus et Promotion (utilisée par étudiant et pilote) -->
            <div id="section-campus-promotion" class="form-section">
                <h3>Informations Campus et Promotion</h3>

                <div class="form-section">
                    <h4>Campus</h4>

                    <div class="radio-group form-group">
                        <input type="radio" id="campus-existant" name="campus-choix" value="existant" checked>
                        <label for="campus-existant">Choisir un campus existant</label>
                    </div>

                    <div class="radio-group form-group">
                        <input type="radio" id="nouveau-campus" name="campus-choix" value="nouveau">
                        <label for="nouveau-campus">Ajouter un nouveau campus</label>
                    </div>

                    <div id="section-campus-existant" class="form-group">
                        <label for="campus-select">Sélectionner un campus:</label>
                        <div class="message" id="campus_select_message">
                            Veuillez sélectionner un campus
                        </div>
                        <select id="campus-select" name="campus-id">
                            <option value="">Choisir un campus</option>
                            <?php foreach ($campus as $camp): ?>
                                <option value="<?= $camp['id_campus'] ?>"><?= htmlspecialchars($camp['nom_campus']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="section-nouveau-campus" class="hidden form-group">
                        <label for="nouveau-campus-nom">Nom du nouveau campus:</label>
                        <div class="message" id="nouveau_campus_nom_message">
                            Veuillez saisir un nom de campus
                        </div>
                        <input type="text" id="nouveau-campus-nom" name="nouveau-campus-nom">

                        <h5>Adresse du nouveau campus</h5>

                        <div class="form-group">
                            <label for="region">Région:</label>
                            <div class="message" id="region_message">
                                Sélectionner une région
                            </div>
                            <select id="region" name="region_id">
                                <option value="">Sélectionner une région</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region['id_region'] ?>">
                                        <?= htmlspecialchars($region['nom_region']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="radio-group">
                                <input type="radio" id="ville-existante" name="ville-choix" value="existante" checked>
                                <label for="ville-existante">Choisir une ville existante</label>
                            </div>

                            <div class="radio-group">
                                <input type="radio" id="nouvelle-ville" name="ville-choix" value="nouvelle">
                                <label for="nouvelle-ville">Ajouter une nouvelle ville</label>
                            </div>
                        </div>

                        <div id="section-ville-existante" class="form-group">
                            <label for="ville">Ville:</label>
                            <div class="message" id="ville_message">
                                Veuillez sélectionner une ville
                            </div>
                            <select id="ville" name="ville_id" disabled>
                                <option value="">D'abord sélectionner une région</option>
                            </select>
                        </div>

                        <div id="section-nouvelle-ville" class="hidden form-group">
                            <label for="nouvelle-ville-nom">Nom de la nouvelle ville:</label>
                            <div class="message" id="nouvelle_ville_nom_message">
                                Veuillez insérer une ville
                            </div>
                            <input type="text" id="nouvelle-ville-nom" name="nouvelle_ville_nom">
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse complète:</label>
                            <div class="message" id="adresse_message">
                                Veuillez insérer une adresse
                            </div>
                            <input type="text" id="adresse" name="adresse" placeholder="Numéro, rue, etc.">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4>Promotion</h4>

                    <div class="radio-group form-group">
                        <input type="radio" id="promotion-existante" name="promotion-choix" value="existante" checked>
                        <label for="promotion-existante">Choisir une promotion existante</label>
                    </div>

                    <div class="radio-group form-group">
                        <input type="radio" id="nouvelle-promotion" name="promotion-choix" value="nouvelle">
                        <label for="nouvelle-promotion">Ajouter une nouvelle promotion</label>
                    </div>

                    <div id="section-promotion-existante" class="form-group">
                        <label for="promotion-select">Sélectionner une promotion:</label>
                        <div class="message" id="promotion_select_message">
                            Veuillez sélectonner une promotion
                        </div>
                        <select id="promotion-select" name="promotion-id">
                            <option value="">Choisir une promotion</option>
                            <?php foreach ($promotions as $promotion): ?>
                                <option value="<?= $promotion['id_promotion'] ?>"><?= htmlspecialchars($promotion['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="section-nouvelle-promotion" class="hidden form-group">
                        <label for="nouvelle-promotion-nom">Nom de la nouvelle promotion:</label>
                        <div class="message" id="nouvelle_promotion_nom_message">
                            Insérer une nouvelle promotion
                        </div>
                        <input type="text" id="nouvelle-promotion-nom" name="nouvelle-promotion-nom">
                    </div>
                </div>
            </div>

            <div id="section-admin" class="form-section hidden">
                <h3>Informations administrateur</h3>

                <p>Aucune information supplémentaire requise pour les administrateurs.</p>
            </div>

            <button type="submit" class="btn-submit">Ajouter l'utilisateur</button>
        </form>
    </div>

    <script>
        <?php
        // Convertir le tableau PHP en objet JavaScript
        echo "const allVilles = " . json_encode($villes) . ";\n";
        ?>
    </script>
</body>

</html>