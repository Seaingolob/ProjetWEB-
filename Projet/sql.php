<?php
// Connexion à la base de données
$serveur = "localhost"; // ou l'adresse du serveur
$utilisateur = "root"; // ton nom d'utilisateur MySQL
$mot_de_passe = "Password"; // ton mot de passe MySQL
$base_de_donnees = "projet_web"; // Remplace par le nom de ta base

$connexion = new mysqli($serveur, $utilisateur, $mot_de_passe, $base_de_donnees);

// Vérifie la connexion
if ($connexion->connect_error) {
    die("Échec de la connexion : " . $connexion->connect_error);
}

// Requête SQL pour récupérer les utilisateurs
$sql = "SELECT * FROM utilisateur";
$resultat = $connexion->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Utilisateurs</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

<h2>Liste des Utilisateurs</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Email</th>
        <th>Téléphone</th>
        <th>Mot de Passe</th>
    </tr>
    
    <?php
    // Vérifie s'il y a des résultats et affiche chaque ligne dans le tableau
    if ($resultat->num_rows > 0) {
        while ($ligne = $resultat->fetch_assoc()) {
            echo "<tr>
                    <td>" . $ligne["id_compte"] . "</td>
                    <td>" . $ligne["nom"] . "</td>
                    <td>" . $ligne["prenom"] . "</td>
                    <td>" . $ligne["mail"] . "</td>
                    <td>" . $ligne["telephone"] . "</td>
                    <td>" . $ligne["mot_de_passe"] . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Aucun utilisateur trouvé.</td></tr>";
    }
    ?>

</table>

</body>
</html>

<?php
// Ferme la connexion à la base de données
$connexion->close();
?>