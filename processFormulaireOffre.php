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

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inclusion du fichier de configuration
    require_once 'config.php';
    
    try {
        // Récupération des données du formulaire pour l'offre
        $titre = htmlspecialchars($_POST['titre']);
        $duree = intval($_POST['duree']);
        $date_publication = $_POST['date_publication'];
        $date_debut = $_POST['date_debut']; // Note: cette date n'est pas stockée dans la table offre selon le schéma
        $description = htmlspecialchars($_POST['description']);
        
        $id_entreprise = null;
        
        // Déterminer si l'utilisateur a choisi une entreprise existante ou une nouvelle
        if ($_POST['entreprise-choix'] === 'existante') {
            // Utiliser l'entreprise existante
            $id_entreprise = intval($_POST['entreprise-id']);
        } else {
            // Ajouter une nouvelle entreprise avec toutes ses informations
            $nom_entreprise = htmlspecialchars($_POST['nouvelle-entreprise-nom']);
            $entreprise_description = htmlspecialchars($_POST['entreprise-description']);
            $entreprise_site = filter_var($_POST['entreprise-site'], FILTER_SANITIZE_URL);
            
            // Gérer la région
            $id_region = null;
            
            if ($_POST['region-choix'] === 'existante') {
                $id_region = intval($_POST['region']);
            } else {
                // Ajouter une nouvelle région
                $nom_region = htmlspecialchars($_POST['nouvelle-region-nom']);
                
                $stmt = $connexion->prepare("INSERT INTO region (nom_region) VALUES (?)");
                $stmt->execute([$nom_region]);
                $id_region = $connexion->lastInsertId();
            }
            
            // Gérer la ville
            $id_ville = null;
            
            if ($_POST['ville-choix'] === 'existante') {
                $id_ville = intval($_POST['ville']);
            } else {
                // Ajouter une nouvelle ville
                $nom_ville = htmlspecialchars($_POST['nouvelle-ville-nom']);
                
                $stmt = $connexion->prepare("INSERT INTO ville (nom_ville, id_region) VALUES (?, ?)");
                $stmt->execute([$nom_ville, $id_region]);
                $id_ville = $connexion->lastInsertId();
            }
            
            // Ajouter l'adresse
            $adresse = htmlspecialchars($_POST['adresse']);
            $stmt = $connexion->prepare("INSERT INTO adresse (nom_adresse, id_ville) VALUES (?, ?)");
            $stmt->execute([$adresse, $id_ville]);
            $id_adresse = $connexion->lastInsertId();
            
            // Ajouter l'entreprise
            $stmt = $connexion->prepare("INSERT INTO entreprise (nom, description, site, id_adresse) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom_entreprise, $entreprise_description, $entreprise_site, $id_adresse]);
            $id_entreprise = $connexion->lastInsertId();
            
            // Traiter les secteurs d'activité existants
            if (isset($_POST['secteurs']) && is_array($_POST['secteurs'])) {
                foreach ($_POST['secteurs'] as $id_secteur) {
                    $stmt = $connexion->prepare("INSERT INTO travailler (id_entreprise, id_secteur_activite) VALUES (?, ?)");
                    $stmt->execute([$id_entreprise, $id_secteur]);
                }
            }
            
            // Traiter les nouveaux secteurs d'activité
            if (isset($_POST['nouveau-secteur-check']) && isset($_POST['nouveaux-secteurs']) && !empty($_POST['nouveaux-secteurs'])) {
                $nouveaux_secteurs = explode(',', $_POST['nouveaux-secteurs']);
                
                foreach ($nouveaux_secteurs as $nouveau_secteur) {
                    $nouveau_secteur = trim($nouveau_secteur);
                    
                    if (!empty($nouveau_secteur)) {
                        // Vérifier si le secteur existe déjà
                        $stmt = $connexion->prepare("SELECT id_secteur_activite FROM secteur_activite WHERE nom = ?");
                        $stmt->execute([$nouveau_secteur]);
                        $secteur = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        $id_secteur = null;
                        
                        if (!$secteur) {
                            // Ajouter le nouveau secteur
                            $stmt = $connexion->prepare("INSERT INTO secteur_activite (nom) VALUES (?)");
                            $stmt->execute([$nouveau_secteur]);
                            $id_secteur = $connexion->lastInsertId();
                        } else {
                            $id_secteur = $secteur['id_secteur_activite'];
                        }
                        
                        // Lier le secteur à l'entreprise
                        $stmt = $connexion->prepare("INSERT INTO travailler (id_entreprise, id_secteur_activite) VALUES (?, ?)");
                        $stmt->execute([$id_entreprise, $id_secteur]);
                    }
                }
            }
        }
        
        // Ajouter l'offre
        $stmt = $connexion->prepare("INSERT INTO offre (titre, description, duree_mois, date_publication, id_entreprise, id_compte) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $titre, 
            $description, 
            $duree, 
            $date_publication, 
            $id_entreprise, 
            $_SESSION['user_id']
        ]);
        $id_offre = $connexion->lastInsertId();
        
        // Traiter les compétences existantes sélectionnées
        if (isset($_POST['competences']) && is_array($_POST['competences'])) {
            foreach ($_POST['competences'] as $id_competence) {
                $stmt = $connexion->prepare("INSERT INTO contenir (id_offre, id_competence) VALUES (?, ?)");
                $stmt->execute([$id_offre, $id_competence]);
            }
        }
        
        // Traiter les nouvelles compétences
        if (isset($_POST['nouvelle-competence-check']) && isset($_POST['nouvelles-competences']) && !empty($_POST['nouvelles-competences'])) {
            $nouvelles_competences = explode(',', $_POST['nouvelles-competences']);
            
            foreach ($nouvelles_competences as $nouvelle_competence) {
                $nouvelle_competence = trim($nouvelle_competence);
                
                if (!empty($nouvelle_competence)) {
                    // Vérifier si la compétence existe déjà
                    $stmt = $connexion->prepare("SELECT id_competence FROM competence WHERE nom = ?");
                    $stmt->execute([$nouvelle_competence]);
                    $comp = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $id_competence = null;
                    
                    if (!$comp) {
                        // Ajouter la nouvelle compétence
                        $stmt = $connexion->prepare("INSERT INTO competence (nom) VALUES (?)");
                        $stmt->execute([$nouvelle_competence]);
                        $id_competence = $connexion->lastInsertId();
                    } else {
                        $id_competence = $comp['id_competence'];
                    }
                    
                    // Lier la compétence à l'offre
                    $stmt = $connexion->prepare("INSERT INTO contenir (id_offre, id_competence) VALUES (?, ?)");
                    $stmt->execute([$id_offre, $id_competence]);
                }
            }
        }
        
        // Redirection avec un message de succès
        $_SESSION['message'] = "Offre ajoutée avec succès!";
        header("Location: Admin.php");
        exit();
        
    } catch(PDOException $e) {
        // En cas d'erreur, afficher le message d'erreur
        echo "Erreur: " . $e->getMessage();
        // Ou rediriger vers une page d'erreur
        // header("Location: erreur.php?msg=" . urlencode($e->getMessage()));
        // exit();
    }
} else {
    // Si le formulaire n'a pas été soumis correctement, rediriger vers la page du formulaire
    header("Location: ajouterOffre.php");
    exit();
}
?>
   