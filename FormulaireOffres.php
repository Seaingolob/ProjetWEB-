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

// vérifier si l'utilisateur est un administrateur ou un pilote
if ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'pilote') {
    // Rediriger vers la page principale si ce n'est pas un administrateur ou un pilote
    header("Location: Main.php");
    exit();
}

// Inclusion du fichier de configuration
require_once 'config.php';

// Récupération de toutes les compétences existantes
$stmt = $connexion->prepare("SELECT id_competence, nom FROM competence ORDER BY nom");
$stmt->execute();
$competences = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de toutes les entreprises existantes
$stmt = $connexion->prepare("SELECT id_entreprise, nom FROM entreprise ORDER BY nom");
$stmt->execute();
$entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de toutes les villes
$stmt = $connexion->prepare("SELECT id_ville, nom_ville FROM ville ORDER BY nom_ville");
$stmt->execute();
$villes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de tous les secteurs d'activité
$stmt = $connexion->prepare("SELECT id_secteur_activite, nom FROM secteur_activite ORDER BY nom");
$stmt->execute();
$secteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de toutes les régions
$stmt = $connexion->prepare("SELECT id_region, nom_region FROM region ORDER BY nom_region");
$stmt->execute();
$regions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Offre</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
    <script>
        window.onload = function() {
            creationoffre();
            compteurmessage();
        };
    </script>

</head>

<body>
    <header class="header">
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
    </header>
    <div class="form-container">
        <h2>Ajouter une Offre</h2>
        <form action="processFormulaireOffre.php" method="post" id="form-offres">
            <div class="form-section">
                <div class="message" id="titre_offre_message">
                    Veuillez insérer le titre de votre offre
                </div>
                <div class="form-title">Titre de l'offre:</div>
                <input type="text" id="titre" name="titre">
            </div>

            <div class="form-section">
                <div class="form-title">Informations sur l'entreprise</div>
                <div class="form-group">
                    <div class="radio-group form-group">
                        <input type="radio" id="entreprise-existante" name="entreprise-choix" value="existante" checked>
                        <label for="entreprise-existante">Choisir une entreprise existante</label>
                    </div>

                    <div class="radio-group form-group">
                        <input type="radio" id="nouvelle-entreprise" name="entreprise-choix" value="nouvelle">
                        <label for="nouvelle-entreprise">Ajouter une nouvelle entreprise</label>
                    </div>
                </div>

                <div id="section-entreprise-existante" class="form-group">
                    <label for="entreprise-select">Sélectionner une entreprise:</label>
                    <div class="message" id="entreprise_select_message">
                        Veuillez sélectionner une entreprise
                    </div>
                    <select id="entreprise-select" name="entreprise-id">
                        <option value="">Choisir une entreprise</option>
                        <?php foreach ($entreprises as $entreprise): ?>
                            <option value="<?= $entreprise['id_entreprise'] ?>"><?= htmlspecialchars($entreprise['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="section-nouvelle-entreprise" class="hidden">
                    <div class="form-group">
                        <label for="nouvelle-entreprise-nom">Nom de l'entreprise:</label>
                        <div class="message" id="nouvelle_entreprise_nom">
                            Veuillez insérer le nom de l'entreprise
                        </div>
                        <input type="text" id="nouvelle-entreprise-nom" name="nouvelle-entreprise-nom">
                    </div>

                    <div class="form-group">
                        <label for="entreprise-description">Description de l'entreprise:</label>
                        <div class="message" id="entreprise_description_message">
                            Veuillez insérer une description valide
                        </div>
                        <textarea id="entreprise-description" name="entreprise-description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="entreprise-site">Site web:</label>
                        <div class="message" id="entreprise_site_message">
                            Veuillez insérer un site web valide
                        </div>
                        <input type="url" id="entreprise-site" name="entreprise-site" placeholder="https://www.exemple.com">
                    </div>

                    <h4>Secteurs d'activité</h4>
                    <div class="secteurs-container form-group">
                        <?php foreach ($secteurs as $secteur): ?>
                            <div class="secteur-item">
                                <input type="checkbox" id="sect-<?= $secteur['id_secteur_activite'] ?>" name="secteurs[]" value="<?= $secteur['id_secteur_activite'] ?>">
                                <label for="sect-<?= $secteur['id_secteur_activite'] ?>"><?= htmlspecialchars($secteur['nom']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="radio-group form-group">
                        <input type="checkbox" id="nouveau-secteur-check" name="nouveau-secteur-check">
                        <label for="nouveau-secteur-check">Ajouter d'autres secteurs d'activité</label>
                    </div>

                    <div id="nouveau-secteur-section" class="hidden form-group">
                        <div class="message" id="nouveaux_secteurs_message">
                            Veuillez insérer un nouveau secteur valide
                        </div>
                        <label for="nouveaux-secteurs">Nouveaux secteurs (séparés par des virgules):</label>
                        <input type="text" id="nouveaux-secteurs" name="nouveaux-secteurs" placeholder="Finance, Technologie, Santé...">
                    </div>

                    <div class="form-section">
                        <h4>Adresse de l'entreprise</h4>

                        <div class="form-group">
                            <label for="region">Région:</label>
                            <div class="message" id="region_message">
                                Veuillez insérer une région valide
                            </div>
                            <select id="region" name="region">
                                <option value="">Choisir une région</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region['id_region'] ?>"><?= htmlspecialchars($region['nom_region']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="radio-group form-group">
                            <input type="radio" id="region-existante" name="region-choix" value="existante" checked>
                            <label for="region-existante">Choisir une région existante</label>
                        </div>
                        <div class="radio-group form-group">
                            <input type="radio" id="nouvelle-region" name="region-choix" value="nouvelle">
                            <label for="nouvelle-region">Ajouter une nouvelle région</label>
                        </div>

                        <div id="section-nouvelle-region" class="hidden form-group">
                            <label for="nouvelle-region-nom">Nom de la région:</label>
                            <div class="message" id="nouvelle_region_nom_message">
                                Veuillez insérer un nom de région valide
                            </div>
                            <input type="text" id="nouvelle-region-nom" name="nouvelle-region-nom">
                        </div>

                        <div class="form-group">
                            <label for="ville">Ville:</label>
                            <div class="message" id="ville_message">
                                Veuillez insérer une ville valide
                            </div>
                            <select id="ville" name="ville">
                                <option value="">Choisir une ville</option>
                                <?php foreach ($villes as $ville): ?>
                                    <option value="<?= $ville['id_ville'] ?>"><?= htmlspecialchars($ville['nom_ville']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="radio-group form-group">
                            <input type="radio" id="ville-existante" name="ville-choix" value="existante" checked>
                            <label for="ville-existante">Choisir une ville existante</label>
                        </div>
                        <div class="radio-group form-group">
                            <input type="radio" id="nouvelle-ville" name="ville-choix" value="nouvelle">
                            <label for="nouvelle-ville">Ajouter une nouvelle ville</label>
                        </div>

                        <div id="section-nouvelle-ville" class="hidden form-group">
                            <label for="nouvelle-ville-nom">Nom de la ville:</label>
                            <div class="message" id="nouvelle_ville_nom_message">
                                Veuillez insérer une nouvelle ville valide
                            </div>
                            <input type="text" id="nouvelle-ville-nom" name="nouvelle-ville-nom">
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse complète:</label>
                            <div class="message" id="adresse_message">
                                Veuillez insérer une adresse valide
                            </div>
                            <input type="text" id="adresse" name="adresse" placeholder="Numéro, rue, code postal">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-title">Détails de l'offre</div>

                <div class="form-group">
                    <label for="duree">Durée (en mois):</label>
                    <div class="message" id="duree_message">
                        Veuillez insérer une durée valide
                    </div>
                    <input type="number" id="duree" name="duree" min="1">
                </div>

                <div class="form-group">
                    <label for="date_publication">Date de publication:</label>
                    <input type="date" id="date_publication" name="date_publication" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label for="date_debut">Date de début du stage:</label>
                    <div class="message" id="date_debut_message">
                        Veuillez insérer une date valide
                    </div>
                    <input type="date" id="date-debut" name="date_debut">
                </div>

                <div class="form-group">
                    <label for="description">Description détaillée du poste:</label>
                    <div class="message" id="description_message">
                        Veuillez insérer une description valide
                    </div>
                    <textarea id="description" name="description" rows="5"></textarea>
                </div>

                <h4>Compétences requises</h4>
                <div class="competences-container form-group">
                    <?php foreach ($competences as $competence): ?>
                        <div class="competence-item">
                            <input type="checkbox" id="comp-<?= $competence['id_competence'] ?>" name="competences[]" value="<?= $competence['id_competence'] ?>">
                            <label for="comp-<?= $competence['id_competence'] ?>"><?= htmlspecialchars($competence['nom']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="radio-group form-group">
                    <input type="checkbox" id="nouvelle-competence-check" name="nouvelle-competence-check">
                    <label for="nouvelle-competence-check">Ajouter d'autres compétences</label>
                </div>

                <div id="nouvelle-competence-section" class="hidden form-group">
                    <label for="nouvelles-competences">Nouvelles compétences (séparées par des virgules):</label>
                    <div class="message" id="nouvelles_competences_message">
                        Veuillez insérer une nouvelle competence valide
                    </div>
                    <input type="text" id="nouvelles-competences" name="nouvelles-competences" placeholder="PHP, JavaScript, SQL...">
                </div>
            </div>
            <div class="btn-container">
                <button type="submit" class="btn-submit">Ajouter l'offre</button>
            </div>
        </form>
    </div>

</body>

</html>
