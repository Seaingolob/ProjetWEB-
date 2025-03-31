document.addEventListener("DOMContentLoaded", function() {
    // Gestion de la bannière de cookies
    const cookieBanner = document.getElementById("cookie-banner");
    const acceptButton = document.getElementById("accept-cookies");
    
    if (cookieBanner && acceptButton) {
        if (localStorage.getItem("cookiesAccepted") === 'true') {
            cookieBanner.style.display = "none";
        }
        
        acceptButton.addEventListener("click", function() {
            localStorage.setItem("cookiesAccepted", "true");
            cookieBanner.style.display = "none";
        });
    }
    
    // Gestion du menu burger
    const burgerMenu = document.querySelector(".burger-menu");
    const menu = document.getElementById("menu");
    
    if (burgerMenu && menu) {
        burgerMenu.addEventListener("click", function() {
            menu.classList.toggle("active");
        });
    }
})

// Fonction pour colorer un champ en erreur
function colorfield(input, message) {
    if (input.value.trim() === "" || (input.files && input.files.length === 0)) {
        input.style.border = "2px solid red";
        message.style.display = "block";
    }
}

// Fonction pour vérifier un champ
function checkfield(input, message) {
    if (input.classList.contains('hidden') || input.closest('.hidden')) {
        return false; // Ignorer les champs cachés
    }
    if (input.value.trim() === "" || (input.files && input.files.length === 0)) {
        return true; // Champ vide
    } else if (input.files && input.files.length > 0) {
        const file = input.files[0];
        if (file.type !== "application/pdf") {
            input.style.border = "2px solid red";
            message.textContent = "Veuillez insérer un fichier PDF.";
            message.style.display = "block";
            return true; // Fichier non PDF
        } else {
            input.style.border = "";
            message.style.display = "none";
            return false; // Champ rempli avec un PDF
        }
    } else {
        input.style.border = "";
        message.style.display = "none";
        return false; // Champ rempli
    }
}



// Fonction principale pour vérifier tous les champs d'un formulaire
function verifierChamps(form, champs) {
    // Ajouter des écouteurs d'événements pour la saisie
    champs.forEach(champ => {
        champ.input.addEventListener("input", function() {
            checkfield(champ.input, champ.message);
        });
        // Ajouter pour les fichiers
        champ.input.addEventListener("change", function() {
            checkfield(champ.input, champ.message);
        });
    });

    form.addEventListener("submit", function(event) {
        let champsVides = false;

        // Vérifier tous les champs
        champs.forEach(champ => {
            if (checkfield(champ.input, champ.message)) {
                champsVides = true;
            }
        });

        // Si au moins un champ est vide, empêcher la soumission et afficher les erreurs
        if (champsVides) {
            event.preventDefault();
            champs.forEach(champ => {
                colorfield(champ.input, champ.message);
            });

        }
    });
}

// Fonction pour la recherche dans la page d'admin
function search() {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        const searchValue = searchInput.value;
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'utilisateur';
        window.location.href = `Admin.php?tab=${activeTab}&search=${searchValue}`;
    }
}

// Fonction pour gérer les favoris (wishlist)
function toggleHeart(event) {
    let heart = event.currentTarget;
    let offerId = heart.getAttribute('data-id');
    let isLiked = false;
    
    if (heart.innerText === "🤍") {
        heart.innerText = "❤️";
        heart.classList.add("liked");
        isLiked = true;
    } else {
        heart.innerText = "🤍";
        heart.classList.remove("liked");
        isLiked = false;
    }
    
    // Sauvegarder l'état du like
    fetch('save_like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'offer_id=' + offerId + '&liked=' + (isLiked ? 1 : 0)
    });
}

function compteurmessage(){
    document.getElementById("message").addEventListener("input", function() {
    const maxLength = 300;
    const currentLength = this.value.length;

    document.getElementById("compteur").textContent = `${this.value.length}/${maxLength}`;

    if (currentLength == 250){
        compteur.classList.add("compteur_active");
    }
});
}

function connexion() { 
    verifierChamps(document.getElementById("login-form"), [ 
        { input: document.getElementById("identifiant"), message: document.getElementById("utilisateur-message") }, 
        { input: document.getElementById("motdepasse"), message: document.getElementById("mdp-message") } 
    ]); 
}

function contact(){
    verifierChamps(document.getElementById("contact-form") , [
        { input: document.getElementById("name"), message: document.getElementById("nom_message")},
        { input: document.getElementById("email"), message: document.getElementById("email_message")},
        { input: document.getElementById("message"), message: document.getElementById("commentaire_message")},
        { input: document.getElementById("subject"), message: document.getElementById("subject_message")}
    ])
}

function postuleroffre(){
    verifierChamps(document.getElementById("postulerForm") , [
        { input: document.getElementById("cv"), message: document.getElementById("cv_message")},
        { input: document.getElementById("lettre_motivation"), message: document.getElementById("lettre_motivation_message")}
    ])
}

function creationoffre(){
    verifierChamps(document.getElementById("form-offres") , [
        { input: document.getElementById("titre"), message: document.getElementById("titre_offre_message")},
        { input: document.getElementById("entreprise-select"), message: document.getElementById("entreprise_select_message")},
        { input: document.getElementById("nouvelle-entreprise-nom"), message: document.getElementById("nouvelle_entreprise_nom")},
        { input: document.getElementById("entreprise-description"), message: document.getElementById("entreprise_description_message")},
        { input: document.getElementById("entreprise-site"), message: document.getElementById("entreprise_site_message")},
        { input: document.getElementById("region"), message: document.getElementById("region_message")},
        { input: document.getElementById("ville"), message: document.getElementById("ville_message")},
        { input: document.getElementById("nouvelle-ville-nom"), message: document.getElementById("nouvelle_ville_nom_message")},
        { input: document.getElementById("adresse"), message: document.getElementById("adresse_message")},
        { input: document.getElementById("duree"), message: document.getElementById("duree_message")},
        { input: document.getElementById("date-debut"), message: document.getElementById("date_debut_message")},
        { input: document.getElementById("description"), message: document.getElementById("description_message")},
        { input: document.getElementById("nouvelles-competences"), message: document.getElementById("nouvelles_competences_message")},
    ])
}

function creationutilisateur(){
    verifierChamps(document.getElementById("form-utilisateur") , [
        { input: document.getElementById("nom"), message: document.getElementById("nom_message")},
        { input: document.getElementById("prenom"), message: document.getElementById("prenom_message")},
        { input: document.getElementById("mail"), message: document.getElementById("mail_message")},
        { input: document.getElementById("mot_de_passe"), message: document.getElementById("mot_de_passe_message")},
        { input: document.getElementById("telephone"), message: document.getElementById("telephone_message")},
        { input: document.getElementById("campus-select"), message: document.getElementById("campus_select_message")},
        { input: document.getElementById("ville"), message: document.getElementById("ville_message")},
        { input: document.getElementById("nouvelle-ville-nom"), message: document.getElementById("nouvelle_ville_nom_message")},
        { input: document.getElementById("adresse"), message: document.getElementById("adresse_message")},
        { input: document.getElementById("promotion-select"), message: document.getElementById("promotion_select_message")},
        { input: document.getElementById("nouvelle-promotion-nom"), message: document.getElementById("nouvelle_promotion_nom_message")},
        { input: document.getElementById("region"), message: document.getElementById("region_message")},
        { input: document.getElementById("nouveau-campus-nom"), message: document.getElementById("nouveau_campus_nom")},   
    ])
}