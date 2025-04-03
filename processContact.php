<?php
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

// Définir le répertoire d'upload
$uploadDir = "/var/www/html/site/uploads/contacts/";

// Vérifier si le répertoire existe, sinon le créer
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

// Fonction pour nettoyer les caractères spéciaux dans un nom de fichier
function sanitizeFileName($string) {
    // Remplacer les accents et caractères spéciaux
    $string = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $string);
    // Remplacer les espaces par des underscores
    $string = str_replace(' ', '_', $string);
    // Supprimer tout ce qui n'est pas alphanumérique ou underscore
    return preg_replace('/[^a-z0-9_]/', '', $string);
}

// Créer un nom de fichier unique

$cleanName = sanitizeFileName(strtolower($name));
$cleanSubject = sanitizeFileName($subject);
$date = date('Y-m-d');
$uniqueId = substr(uniqid(), -6); // Utilise seulement les 6 derniers caractères de uniqid()

$filename = "{$cleanName}_{$date}_{$cleanSubject}_{$uniqueId}.txt";


$filepath = $uploadDir . $filename;

// Enregistrer le fichier
$success = file_put_contents($filepath, $content);

// Vérifier si l'enregistrement a réussi
if ($success === false) {
    error_log("Erreur d'enregistrement du contact: $filepath");
    header('Location: Contact.php?error=save_error');
    exit();
}

// Rediriger avec un message de succès
header('Location: Contact.php?success=1');
exit();
?>
