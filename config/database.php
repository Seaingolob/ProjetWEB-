<?php
// config/database.php
// Fichier principal de configuration de la base de données

// Inclure le fichier vault.php pour récupérer les identifiants
$credentials = include __DIR__ . '/vault.php';
$dbConfig = $credentials['database'];

// Construire le DSN
$dsn = sprintf(
    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['dbname'],
    $dbConfig['charset']
);

// Créer une instance PDO et la retourner
try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true
    ];
    
    $connexion = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
    
    return $connexion;
} catch (PDOException $e) {
    // En mode développement, tu peux afficher l'erreur
    // En production, il vaut mieux la logger et afficher un message générique
    error_log('Erreur de connexion à la base de données: ' . $e->getMessage());
    die('Erreur de connexion à la base de données. Veuillez contacter l\'administrateur.');
}