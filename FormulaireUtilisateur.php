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

// Inclusion du fichier de configuration
require_once 'config.php';

try {
    // Récupération de tous les campus
    $stmt = $connexion->prepare("SELECT id_campus, nom_campus FROM campus ORDER BY nom_campus");
    $stmt->execute();
    $campuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    
} catch(PDOException $e) {
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
    <style>
        .hidden {
            display: none;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-section {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], 
        input[type="email"], 
        input[type="tel"], 
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .radio-group {
            margin-bottom: 10px;
        }
        .radio-group label {
            display: inline;
            font-weight: normal;
            margin-left: 5px;
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
        <h2>Ajouter un Utilisateur</h2>
        <form action="processFormulaireUtilisateur.php" method="post">
            <div class="form-section">
                <h3>Informations personnelles</h3>
                
                <div class="form-group">
                    <label for="nom">Nom:</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom:</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                
                <div class="form-group">
                    <label for="mail">Email:</label>
                    <input type="email" id="mail" name="mail" required>
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe:</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                
                <div class="form-group">
                    <label for="telephone">N° de Téléphone:</label>
                    <input type="tel" id="telephone" name="telephone" required>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Type d'utilisateur</h3>
                <div class="form-group">
                    <div class="radio-group">
                        <input type="radio" id="type-etudiant" name="type_utilisateur" value="etudiant" checked>
                        <label for="type-etudiant">Étudiant</label>
                    </div>
                    
                    <div class="radio-group">
                        <input type="radio" id="type-pilote" name="type_utilisateur" value="pilote">
                        <label for="type-pilote">Pilote</label>
                    </div>
                    
                    <div class="radio-group">
                        <input type="radio" id="type-admin" name="type_utilisateur" value="admin">
                        <label for="type-admin">Administrateur</label>
                    </div>
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
                        <select id="campus-select" name="campus-id">
                            <option value="">Choisir un campus</option>
                            <?php foreach ($campuses as $campus): ?>
                                <option value="<?= $campus['id_campus'] ?>"><?= htmlspecialchars($campus['nom_campus']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="section-nouveau-campus" class="hidden form-group">
                        <label for="nouveau-campus-nom">Nom du nouveau campus:</label>
                        <input type="text" id="nouveau-campus-nom" name="nouveau-campus-nom">
                        
                        <h5>Adresse du nouveau campus</h5>
                        
                        <div class="form-group">
                            <label for="region">Région:</label>
                            <select id="region" name="region_id" required>
                                <option value="">Sélectionner une région</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region['id_region'] ?>"><?= htmlspecialchars($region['nom_region']) ?></option>
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
                            <select id="ville" name="ville_id" disabled>
                                <option value="">D'abord sélectionner une région</option>
                            </select>
                        </div>
                        
                        <div id="section-nouvelle-ville" class="hidden form-group">
                            <label for="nouvelle-ville-nom">Nom de la nouvelle ville:</label>
                            <input type="text" id="nouvelle-ville-nom" name="nouvelle_ville_nom">
                        </div>
                        
                        <div class="form-group">
                            <label for="adresse">Adresse complète:</label>
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
                        <select id="promotion-select" name="promotion-id">
                            <option value="">Choisir une promotion</option>
                            <?php foreach ($promotions as $promotion): ?>
                                <option value="<?= $promotion['id_promotion'] ?>"><?= htmlspecialchars($promotion['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="section-nouvelle-promotion" class="hidden form-group">
                        <label for="nouvelle-promotion-nom">Nom de la nouvelle promotion:</label>
                        <input type="text" id="nouvelle-promotion-nom" name="nouvelle-promotion-nom">
                    </div>
                </div>
            </div>
            
            <div id="section-admin" class="form-section hidden">
                <h3>Informations administrateur</h3>
                <!-- Aucun champ spécifique pour les admins dans votre schéma -->
                <p>Aucune information supplémentaire requise pour les administrateurs.</p>
            </div>
            
            <button type="submit" class="btn-submit">Ajouter l'utilisateur</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Type d'utilisateur
            const typeEtudiantRadio = document.getElementById('type-etudiant');
            const typePiloteRadio = document.getElementById('type-pilote');
            const typeAdminRadio = document.getElementById('type-admin');
            const sectionCampusPromotion = document.getElementById('section-campus-promotion');
            const sectionAdmin = document.getElementById('section-admin');
            
            // Campus
            const campusExistantRadio = document.getElementById('campus-existant');
            const nouveauCampusRadio = document.getElementById('nouveau-campus');
            const sectionCampusExistant = document.getElementById('section-campus-existant');
            const sectionNouveauCampus = document.getElementById('section-nouveau-campus');
            
            // Ville
            const villeExistanteRadio = document.getElementById('ville-existante');
            const nouvelleVilleRadio = document.getElementById('nouvelle-ville');
            const sectionVilleExistante = document.getElementById('section-ville-existante');
            const sectionNouvelleVille = document.getElementById('section-nouvelle-ville');
            
            // Promotion
            const promotionExistanteRadio = document.getElementById('promotion-existante');
            const nouvellePromotionRadio = document.getElementById('nouvelle-promotion');
            const sectionPromotionExistante = document.getElementById('section-promotion-existante');
            const sectionNouvellePromotion = document.getElementById('section-nouvelle-promotion');
            
            // Gestion du type d'utilisateur
            typeEtudiantRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionCampusPromotion.classList.remove('hidden');
                    sectionAdmin.classList.add('hidden');
                }
            });
            
            typePiloteRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionCampusPromotion.classList.remove('hidden');
                    sectionAdmin.classList.add('hidden');
                }
            });
            
            typeAdminRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionCampusPromotion.classList.add('hidden');
                    sectionAdmin.classList.remove('hidden');
                }
            });
            
            // Gestion des campus
            campusExistantRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionCampusExistant.classList.remove('hidden');
                    sectionNouveauCampus.classList.add('hidden');
                }
            });
            
            nouveauCampusRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionCampusExistant.classList.add('hidden');
                    sectionNouveauCampus.classList.remove('hidden');
                }
            });
            
            // Gestion des villes
            villeExistanteRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionVilleExistante.classList.remove('hidden');
                    sectionNouvelleVille.classList.add('hidden');
                }
            });
            
            nouvelleVilleRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionVilleExistante.classList.add('hidden');
                    sectionNouvelleVille.classList.remove('hidden');
                }
            });
            
            // Gestion des promotions
            promotionExistanteRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionPromotionExistante.classList.remove('hidden');
                    sectionNouvellePromotion.classList.add('hidden');
                }
            });
            
            nouvellePromotionRadio.addEventListener('change', function() {
                if (this.checked) {
                    sectionPromotionExistante.classList.add('hidden');
                    sectionNouvellePromotion.classList.remove('hidden');
                }
            });
            
            // Chargement des villes en fonction de la région sélectionnée
            const regionSelect = document.getElementById('region');
            const villeSelect = document.getElementById('ville');
            
            // Données des villes pré-chargées (pour éviter des requêtes AJAX)
            const villesByRegion = {};
            
            <?php 
            // Convertir le tableau PHP en objet JavaScript
            echo "const allVilles = " . json_encode($villes) . ";\n";
            ?>
            
            // Organiser les villes par région
            allVilles.forEach(ville => {
                if (!villesByRegion[ville.id_region]) {
                    villesByRegion[ville.id_region] = [];
                }
                villesByRegion[ville.id_region].push(ville);
            });
            
            regionSelect.addEventListener('change', function() {
                const regionId = this.value;
                villeSelect.innerHTML = '<option value="">Sélectionner une ville</option>';
                villeSelect.disabled = !regionId;
                
                if (regionId && villesByRegion[regionId]) {
                    villesByRegion[regionId].forEach(ville => {
                        const option = document.createElement('option');
                        option.value = ville.id_ville;
                        option.textContent = ville.nom_ville;
                        villeSelect.appendChild(option);
                    });
                }
            });
        });
    </script>
</body>
</html>
