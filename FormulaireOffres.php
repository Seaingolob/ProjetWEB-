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
    <style>
        .hidden {
            display: none;
        }
        .competences-container, .secteurs-container {
            margin-bottom: 20px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .competence-item, .secteur-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 10px;
        }
        .entreprise-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-section {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
        }
    </style>
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
        <form action="processFormulaireOffre.php" method="post">
            <div class="form-group">
                <label for="titre">Titre de l'offre:</label>
                <input type="text" id="titre" name="titre" required>
            </div>
            
            <div class="entreprise-section">
                <h3>Informations sur l'entreprise</h3>
                <div class="form-group">
                    <div>
                        <input type="radio" id="entreprise-existante" name="entreprise-choix" value="existante" checked>
                        <label for="entreprise-existante">Choisir une entreprise existante</label>
                    </div>
                    
                    <div>
                        <input type="radio" id="nouvelle-entreprise" name="entreprise-choix" value="nouvelle">
                        <label for="nouvelle-entreprise">Ajouter une nouvelle entreprise</label>
                    </div>
                </div>
                
                <div id="section-entreprise-existante" class="form-group">
                    <label for="entreprise-select">Sélectionner une entreprise:</label>
                    <select id="entreprise-select" name="entreprise-id" required>
                        <option value="">Choisir une entreprise</option>
                        <?php foreach ($entreprises as $entreprise): ?>
                            <option value="<?= $entreprise['id_entreprise'] ?>"><?= htmlspecialchars($entreprise['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="section-nouvelle-entreprise" class="hidden">
                    <div class="form-group">
                        <label for="nouvelle-entreprise-nom">Nom de l'entreprise:</label>
                        <input type="text" id="nouvelle-entreprise-nom" name="nouvelle-entreprise-nom">
                    </div>
                    
                    <div class="form-group">
                        <label for="entreprise-description">Description de l'entreprise:</label>
                        <textarea id="entreprise-description" name="entreprise-description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="entreprise-site">Site web:</label>
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
                    
                    <div class="form-group">
                        <input type="checkbox" id="nouveau-secteur-check" name="nouveau-secteur-check">
                        <label for="nouveau-secteur-check">Ajouter d'autres secteurs d'activité</label>
                    </div>
                    
                    <div id="nouveau-secteur-section" class="hidden form-group">
                        <label for="nouveaux-secteurs">Nouveaux secteurs (séparés par des virgules):</label>
                        <input type="text" id="nouveaux-secteurs" name="nouveaux-secteurs" placeholder="Finance, Technologie, Santé...">
                    </div>
                    
                    <div class="form-section">
                        <h4>Adresse de l'entreprise</h4>
                        
                        <div class="form-group">
                            <label for="region">Région:</label>
                            <select id="region" name="region">
                                <option value="">Choisir une région</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region['id_region'] ?>"><?= htmlspecialchars($region['nom_region']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div>
                                <input type="radio" id="region-existante" name="region-choix" value="existante" checked>
                                <label for="region-existante">Choisir une région existante</label>
                            </div>
                            
                            <div>
                                <input type="radio" id="nouvelle-region" name="region-choix" value="nouvelle">
                                <label for="nouvelle-region">Ajouter une nouvelle région</label>
                            </div>
                        </div>
                        
                        <div id="section-nouvelle-region" class="hidden form-group">
                            <label for="nouvelle-region-nom">Nom de la région:</label>
                            <input type="text" id="nouvelle-region-nom" name="nouvelle-region-nom">
                        </div>
                        
                        <div class="form-group">
                            <label for="ville">Ville:</label>
                            <select id="ville" name="ville">
                                <option value="">Choisir une ville</option>
                                <?php foreach ($villes as $ville): ?>
                                    <option value="<?= $ville['id_ville'] ?>"><?= htmlspecialchars($ville['nom_ville']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div>
                                <input type="radio" id="ville-existante" name="ville-choix" value="existante" checked>
                                <label for="ville-existante">Choisir une ville existante</label>
                            </div>
                            
                            <div>
                                <input type="radio" id="nouvelle-ville" name="ville-choix" value="nouvelle">
                                <label for="nouvelle-ville">Ajouter une nouvelle ville</label>
                            </div>
                        </div>
                        
                        <div id="section-nouvelle-ville" class="hidden form-group">
                            <label for="nouvelle-ville-nom">Nom de la ville:</label>
                            <input type="text" id="nouvelle-ville-nom" name="nouvelle-ville-nom">
                        </div>
                        
                        <div class="form-group">
                            <label for="adresse">Adresse complète:</label>
                            <input type="text" id="adresse" name="adresse" placeholder="Numéro, rue, code postal">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Détails de l'offre</h3>
                
                <div class="form-group">
                    <label for="duree">Durée (en mois):</label>
                    <input type="number" id="duree" name="duree" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="date_publication">Date de publication:</label>
                    <input type="date" id="date_publication" name="date_publication" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="date_debut">Date de début du stage:</label>
                    <input type="date" id="date_debut" name="date_debut" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description détaillée du poste:</label>
                    <textarea id="description" name="description" rows="5" required></textarea>
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
                
                <div class="form-group">
                    <input type="checkbox" id="nouvelle-competence-check" name="nouvelle-competence-check">
                    <label for="nouvelle-competence-check">Ajouter d'autres compétences</label>
                </div>
                
                <div id="nouvelle-competence-section" class="hidden form-group">
                    <label for="nouvelles-competences">Nouvelles compétences (séparées par des virgules):</label>
                    <input type="text" id="nouvelles-competences" name="nouvelles-competences" placeholder="PHP, JavaScript, SQL...">
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Ajouter l'offre</button>
        </form>
    </div>

    <script>
        // Script pour gérer l'affichage/masquage des sections en fonction des choix
        document.addEventListener('DOMContentLoaded', function() {
            // Entreprise
            const entrepriseExistanteRadio = document.getElementById('entreprise-existante');
            const nouvelleEntrepriseRadio = document.getElementById('nouvelle-entreprise');
            const sectionEntrepriseExistante = document.getElementById('section-entreprise-existante');
            const sectionNouvelleEntreprise = document.getElementById('section-nouvelle-entreprise');
            
            // Ville
            const villeExistanteRadio = document.getElementById('ville-existante');
            const nouvelleVilleRadio = document.getElementById('nouvelle-ville');
            const sectionNouvelleVille = document.getElementById('section-nouvelle-ville');
            
            // Région
            const regionExistanteRadio = document.getElementById('region-existante');
            const nouvelleRegionRadio = document.getElementById('nouvelle-region');
            const sectionNouvelleRegion = document.getElementById('section-nouvelle-region');
            
            // Compétences et Secteurs
            const nouvelleCompetenceCheck = document.getElementById('nouvelle-competence-check');
            const nouvelleCompetenceSection = document.getElementById('nouvelle-competence-section');
            const nouveauSecteurCheck = document.getElementById('nouveau-secteur-check');
            const nouveauSecteurSection = document.getElementById('nouveau-secteur-section');
            
            // Gestion des entreprises
            entrepriseExistanteRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionEntrepriseExistante.classList.remove('hidden');
                    sectionNouvelleEntreprise.classList.add('hidden');
                }
            });
            
            nouvelleEntrepriseRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionEntrepriseExistante.classList.add('hidden');
                    sectionNouvelleEntreprise.classList.remove('hidden');
                }
            });
            
            // Gestion des villes
            villeExistanteRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionNouvelleVille.classList.add('hidden');
                }
            });
            
            nouvelleVilleRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionNouvelleVille.classList.remove('hidden');
                }
            });
            
            // Gestion des régions
            regionExistanteRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionNouvelleRegion.classList.add('hidden');
                }
            });
            
            nouvelleRegionRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionNouvelleRegion.classList.remove('hidden');
                }
            });
            
            // Gestion des nouvelles compétences
            nouvelleCompetenceCheck.addEventListener('change', function() {
                if (this.checked) {
                    nouvelleCompetenceSection.classList.remove('hidden');
                } else {
                    nouvelleCompetenceSection.classList.add('hidden');
                }
            });
            
            // Gestion des nouveaux secteurs
            nouveauSecteurCheck.addEventListener('change', function() {
                if (this.checked) {
                    nouveauSecteurSection.classList.remove('hidden');
                } else {
                    nouveauSecteurSection.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
