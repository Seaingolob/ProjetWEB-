<?php
// Démarrer la session
session_start();

// Activer l'affichage des erreurs pour le débogage (à retirer en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: connexion.php');
    exit();
}

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Contact.php?error=method_not_allowed');
    exit();
}

// Récupérer les données du formulaire
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$name = isset($_POST['name']) ? $_POST['name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';

// Validation rapide
if (empty($subject) || empty($name) || empty($email) || empty($message)) {
    header('Location: Contact.php?error=empty_fields');
    exit();
}

// Préparer le contenu du fichier
$subjectLabels = [
    'info' => 'Demande d\'information',
    'problem' => 'Signaler un problème',
    'partnership' => 'Proposition de partenariat',
    'other' => 'Autre'
];

$subjectText = isset($subjectLabels[$subject]) ? $subjectLabels[$subject] : $subject;

$content = "Date: " . date('Y-m-d H:i:s') . "\n";
$content .= "Sujet: " . $subjectText . "\n";
$content .= "Nom: " . $name . "\n";
$content .= "Email: " . $email . "\n";
$content .= "Message:\n" . $message . "\n";
$content .= "------------------------------------------------\n";

// Définir le répertoire d'upload (utiliser un chemin relatif)
$uploadDir = __DIR__ . "/uploads/contacts/";

// Journaliser l'action pour débogage
error_log("Tentative de création du répertoire: " . $uploadDir);

// Vérifier si le répertoire existe, sinon le créer
if (!is_dir($uploadDir)) {
    $success = mkdir($uploadDir, 0777, true);
    error_log("Création du répertoire: " . ($success ? "Réussie" : "Échec"));
    
    if (!$success) {
        header('Location: Contact.php?error=directory_creation_failed');
        exit();
    }
}

// Fonction pour nettoyer les caractères spéciaux dans un nom de fichier
function sanitizeFileName($string) {
    // Version simplifiée sans dépendance à l'extension intl
    $accents = array(
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'ae',
        'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
        'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o',
        'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u',
        'ý'=>'y', 'ÿ'=>'y'
    );
    $string = strtolower(strtr($string, $accents));
    $string = str_replace(' ', '_', $string);
    return preg_replace('/[^a-z0-9_]/', '', $string);
}

// Créer un nom de fichier unique
$cleanName = sanitizeFileName(strtolower($name));
$cleanSubject = sanitizeFileName($subject);
$date = date('Y-m-d');
$uniqueId = substr(uniqid(), -6); // Utilise seulement les 6 derniers caractères de uniqid()

$filename = "{$cleanName}_{$date}_{$cleanSubject}_{$uniqueId}.txt";
$filepath = $uploadDir . $filename;

// Journaliser le chemin du fichier pour débogage
error_log("Tentative d'écriture dans le fichier: " . $filepath);

try {
    // Enregistrer le fichier
    $success = file_put_contents($filepath, $content);
    
    // Vérifier si l'enregistrement a réussi
    if ($success === false) {
        throw new Exception("Échec de l'écriture du fichier");
    }
    
    // Rediriger avec un message de succès
    header('Location: Contact.php?success=1');
    exit();
    
} catch (Exception $e) {
    error_log("Erreur lors du traitement du formulaire de contact: " . $e->getMessage());
    header('Location: Contact.php?error=save_error&details=' . urlencode($e->getMessage()));
    exit();
}
?>
