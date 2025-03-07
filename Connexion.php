<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <a href="/"><h1>lebonplan</h1></a>
            </div>
        </nav>
    </header>

    <div class="login-container">
        <div class="login-box">
            <h2>Connexion</h2>
            <form id="login-form" action="Main.php" method="post">
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" required>

                <label for="motdepasse">Mot de passe</label>
                <input type="password" id="motdepasse" required>

                <div class="btn-container">
                    <button type="submit">Se connecter</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const identifiant = document.getElementById("identifiant");
        const motdepasse = document.getElementById("motdepasse");
        const submitBtn = document.querySelector("button[type='submit']");
        const form = document.getElementById("login-form");

        // Initialement grisé et désactivé
        submitBtn.style.opacity = 0.5;  // Rend le bouton gris
        submitBtn.disabled = true;      // Désactive le bouton

        function checkFields() {
            if (identifiant.value.trim() !== "" && motdepasse.value.trim() !== "") {
                // Le bouton devient actif et pleinement visible
                submitBtn.style.transition = "opacity 0.3s ease";  // Transition pour l'opacité
                submitBtn.style.opacity = 1;  // Le bouton devient totalement visible
                submitBtn.disabled = false;  // Le bouton devient activable
            } else {
                // Le bouton devient gris et désactivé
                submitBtn.style.opacity = 0.5;  // Le bouton devient gris
                submitBtn.disabled = true;      // Le bouton est désactivé
            }
        }

        // Vérifie les champs à chaque modification
        identifiant.addEventListener("input", checkFields);
        motdepasse.addEventListener("input", checkFields);

        // Soumettre le formulaire si les champs sont remplis
        form.addEventListener("submit", function (e) {
            if (identifiant.value.trim() === "" || motdepasse.value.trim() === "") {
                e.preventDefault();  // Empêche l'envoi du formulaire si les champs sont vides
                alert("Veuillez remplir tous les champs.");
            }
        });
    });
    </script>  
</body>
</html>
