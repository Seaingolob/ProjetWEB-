<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Utilisateur</title>
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
        <h2>Ajouter un Utilisateur</h2>
        <form action="processFormulaireUtilisateur.php" method="post">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" required>
            

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="campus">Campus:</label>
            <input type="text" id="campus" name="campus" required>
            
            <label for="promotion">Promotion:</label>
            <input type="text" id="promotion" name="promotion" required>
            

            <button type="submit">Ajouter</button>
        </form>
    </div>
</body>
</html>