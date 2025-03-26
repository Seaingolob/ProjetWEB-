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
        // Démarrer une transaction
        $connexion->beginTransaction();
        
        // Récupération des données communes
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $mail = filter_var($_POST['mail'], FILTER_SANITIZE_EMAIL);
        
        // Hashage du mot de passe
        $mot_de_passe_hash = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
        
        $telephone = htmlspecialchars($_POST['telephone']);
        $type_utilisateur = $_POST['type_utilisateur'];
        
        // Insérer dans la table utilisateur
        $stmt = $connexion->prepare("INSERT INTO utilisateur (nom, prenom, mail, mot_de_passe, telephone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $mail, $mot_de_passe_hash, $telephone]);
        $id_compte = $connexion->lastInsertId();
        
        // En fonction du type d'utilisateur
        if ($type_utilisateur === 'admin') {
            // Insérer dans la table admin
            $stmt = $connexion->prepare("INSERT INTO admin (id_compte) VALUES (?)");
            $stmt->execute([$id_compte]);
        }
        else {
            // Pour les étudiants et les pilotes, nous avons besoin d'une promotion
            
            // Gérer le campus
            if ($_POST['campus-choix'] === 'existant') {
                $id_campus = intval($_POST['campus-id']);
            } else {
                // Création d'un nouveau campus
                $nom_campus = htmlspecialchars($_POST['nouveau-campus-nom']);
                
                // Gestion de l'adresse du campus
                $adresse_complete = htmlspecialchars($_POST['adresse']);
                
                // Gestion de la ville
                $id_ville = null;
                
                if ($_POST['ville-choix'] === 'existante') {
                    $id_ville = intval($_POST['ville_id']);
                } else {
                    $nouvelle_ville_nom = htmlspecialchars($_POST['nouvelle_ville_nom']);
                    $id_region = intval($_POST['region_id']);
                    
                    $stmt = $connexion->prepare("INSERT INTO ville (nom_ville, id_region) VALUES (?, ?)");
                    $stmt->execute([$nouvelle_ville_nom, $id_region]);
                    $id_ville = $connexion->lastInsertId();
                }
                
                // Création de l'adresse
                $stmt = $connexion->prepare("INSERT INTO adresse (nom_adresse, id_ville) VALUES (?, ?)");
                $stmt->execute([$adresse_complete, $id_ville]);
                $id_adresse = $connexion->lastInsertId();
                
                // Création du campus
                $stmt = $connexion->prepare("INSERT INTO campus (nom_campus, id_adresse) VALUES (?, ?)");
                $stmt->execute([$nom_campus, $id_adresse]);
                $id_campus = $connexion->lastInsertId();
            }
            
            // Gérer la promotion
            if ($_POST['promotion-choix'] === 'existante') {
                $id_promotion = intval($_POST['promotion-id']);
            } else {
                $nom_promotion = htmlspecialchars($_POST['nouvelle-promotion-nom']);
                $stmt = $connexion->prepare("INSERT INTO promotion (nom, id_campus) VALUES (?, ?)");
                $stmt->execute([$nom_promotion, $id_campus]);
                $id_promotion = $connexion->lastInsertId();
            }
            
            $today = date("Y-m-d");
            
            // Traiter selon le type d'utilisateur
            if ($type_utilisateur === 'etudiant') {
                // Insérer dans la table etudiant
                $stmt = $connexion->prepare("INSERT INTO etudiant (id_compte) VALUES (?)");
                $stmt->execute([$id_compte]);
                
                // Associer l'étudiant à la promotion
                $stmt = $connexion->prepare("INSERT INTO appartenir (id_compte, id_promotion, debut) VALUES (?, ?, ?)");
                $stmt->execute([$id_compte, $id_promotion, $today]);
            } 
            elseif ($type_utilisateur === 'pilote') {
                // Insérer dans la table pilote
                $stmt = $connexion->prepare("INSERT INTO pilote (id_compte) VALUES (?)");
                $stmt->execute([$id_compte]);
                
                // Associer le pilote à la promotion
                $stmt = $connexion->prepare("INSERT INTO piloter (id_compte, id_promotion, debut) VALUES (?, ?, ?)");
                $stmt->execute([$id_compte, $id_promotion, $today]);
            }
        }
        
        // Valider la transaction
        $connexion->commit();
        
        // Redirection avec un message de succès
        $_SESSION['message'] = "Utilisateur ajouté avec succès!";
        header("Location: Admin.php");
        exit();
        
    } catch(PDOException $e) {
        // En cas d'erreur, annuler la transaction
        $connexion->rollBack();
        
        // Afficher l'erreur
        echo "Erreur: " . $e->getMessage();
        // Ou rediriger vers une page d'erreur
        // header("Location: erreur.php?msg=" . urlencode($e->getMessage()));
        // exit();
    }
} else {
    // Si le formulaire n'a pas été soumis correctement, rediriger vers la page du formulaire
    header("Location: FormulaireUtilisateur.php");
    exit();
}
?>
