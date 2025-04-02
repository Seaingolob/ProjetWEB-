<?php
// Démarrer la session
session_start();

// Vérifier si la session existe et si elle a expiré
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

// Vérifier si l'utilisateur est un étudiant
if ($_SESSION['user_type'] !== 'etudiant') {
    // Rediriger vers la page principale
    header("Location: Main.php");
    exit();
}

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Vérifier si c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si l'ID de l'offre est fourni
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        // Rediriger vers la page des offres
        header("Location: Offres.php");
        exit();
    }

    // Récupérer l'ID de l'offre
    $id_offre = intval($_POST['id']);
    $id_compte = $_SESSION['user_id'];

    try {
        // Vérifier si l'offre existe
        $stmt = $connexion->prepare("SELECT COUNT(*) FROM offre WHERE id_offre = :id");
        $stmt->bindParam(':id', $id_offre, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // L'offre n'existe pas
            header("Location: Offres.php");
            exit();
        }
        
        // Vérifier si l'étudiant a déjà postulé à cette offre
        $stmt = $connexion->prepare("SELECT COUNT(*) FROM postuler WHERE id_compte = :id_compte AND id_offre = :id_offre");
        $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
        $stmt->bindParam(':id_offre', $id_offre);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            // L'étudiant a déjà postulé
            header("Location: VoirOffre.php?id=" . $id_offre . "&error=already_applied");
            exit();
        }
        
        // Gestion des fichiers téléchargés
        $cv_filename = null;
        $lettre_filename = null;
        $upload_dir = "uploads/candidatures/";
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Traitement du CV
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
            // Vérifier le type de fichier (PDF uniquement)
            $allowed_types = ['application/pdf'];
            if (in_array($_FILES['cv']['type'], $allowed_types)) {
                // Générer un nom de fichier unique
                $cv_filename = $id_compte . '_' . $id_offre . '_cv_' . time() . '.pdf';
                $cv_path = $upload_dir . $cv_filename;
                
                // Déplacer le fichier uploadé
                if (!move_uploaded_file($_FILES['cv']['tmp_name'], $cv_path)) {
                    header("Location: VoirOffre.php?id=" . $id_offre . "&error=upload_failed");
                    exit();
                }
            } else {
                header("Location: VoirOffre.php?id=" . $id_offre . "&error=invalid_cv_type");
                exit();
            }
        } else {
            header("Location: VoirOffre.php?id=" . $id_offre . "&error=cv_required");
            exit();
        }
        
        // Traitement de la lettre de motivation
        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] == 0) {
            // Vérifier le type de fichier (PDF uniquement)
            $allowed_types = ['application/pdf'];
            if (in_array($_FILES['lettre_motivation']['type'], $allowed_types)) {
                // Générer un nom de fichier unique
                $lettre_filename = $id_compte . '_' . $id_offre . '_lettre_' . time() . '.pdf';
                $lettre_path = $upload_dir . $lettre_filename;
                
                // Déplacer le fichier uploadé
                if (!move_uploaded_file($_FILES['lettre_motivation']['tmp_name'], $lettre_path)) {
                    // Supprimer le CV déjà uploadé en cas d'échec
                    if (file_exists($upload_dir . $cv_filename)) {
                        unlink($upload_dir . $cv_filename);
                    }
                    header("Location: VoirOffre.php?id=" . $id_offre . "&error=upload_failed");
                    exit();
                }
            } else {
                // Supprimer le CV déjà uploadé en cas d'échec
                if (file_exists($upload_dir . $cv_filename)) {
                    unlink($upload_dir . $cv_filename);
                }
                header("Location: VoirOffre.php?id=" . $id_offre . "&error=invalid_lettre_type");
                exit();
            }
        } else {
            // Supprimer le CV déjà uploadé en cas d'échec
            if (file_exists($upload_dir . $cv_filename)) {
                unlink($upload_dir . $cv_filename);
            }
            header("Location: VoirOffre.php?id=" . $id_offre . "&error=lettre_required");
            exit();
        }
        
        // Insérer la candidature avec les noms des fichiers
        $stmt = $connexion->prepare("INSERT INTO postuler (id_compte, id_offre, cv_path, lettre_motivation_path, date_candidature) VALUES (:id_compte, :id_offre, :cv_path, :lettre_path, NOW())");
        $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
        $stmt->bindParam(':id_offre', $id_offre);
        $stmt->bindParam(':cv_path', $cv_filename);
        $stmt->bindParam(':lettre_path', $lettre_filename);
        $stmt->execute();
        
        // Rediriger vers la page de détail avec un message de succès
        header("Location: VoirOffre.php?id=" . $id_offre . "&success=applied");
        exit();
        
    } catch(PDOException $e) {
        // En cas d'erreur, afficher un message et rediriger
        echo "Erreur : " . $e->getMessage();
        exit();
    }
} else {
    // Si ce n'est pas une requête POST, rediriger
    header("Location: Offres.php");
    exit();
}
?>