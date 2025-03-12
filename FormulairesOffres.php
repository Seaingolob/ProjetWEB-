<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Offre</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="Main.php"><h1>lebonplan</h1></a>
            </div>
            <ul class="main-nav">
                <li><a href="Main.php">Accueil</a></li>
                <li><a href="Offres.php">Offres</a></li>
                <li><a href="Contact.php">Contact</a></li>
                <li><a href="Espace-Administration.php" class="active">Espace-Administration</a></li>
                <div class="logout-container">
                    <button id="logout-btn" onclick="window.location.href='Connexion.php';">Déconnexion</button>
                </div>
            </ul>
        </nav>
    </header>
    <div class="form-container">
        <h2>Ajouter une Offre</h2>
        <form action="processFormulaireOffre.php" method="post">
            <label for="titre">Titre:</label>
            <input type="text" id="titre" name="titre" value="" required>
            
            <label for="entreprise">Entreprise:</label>
            <input type="text" id="entreprise" name="entreprise" value="" required>
            
            <label for="localisation">Localisation:</label>
            <input type="text" id="localisation" name="localisation" value="" required>
            
            <label for="duree">Durée:</label>
            <input type="text" id="duree" name="duree" value="" required>
            
            <label for="date_publication">Publiée le:</label>
            <input type="date" id="date_publication" name="date_publication" value="" required>
            
            <label for="date_debut">Début du stage:</label>
            <input type="date" id="date_debut" name="date_debut" value="" required>
            
            <label for="description">Description du poste:</label>
            <textarea id="description" name="description" required></textarea>
            
            <label for="competences">Compétences requises:</label>
            <textarea id="competences" name="competences" required></textarea>
            
            <label for="adresse">Adresse:</label>
            <input type="text" id="adresse" name="adresse" value="" required>
            
            <button type="submit">Ajouter</button>
        </form>
    </div>
</body>
</html>