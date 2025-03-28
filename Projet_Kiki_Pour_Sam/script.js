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
    
    // Initialisation des différents formulaires
    
    // Formulaire de candidature/postulation
    const postulerForm = document.getElementById('postulerForm');
    if (postulerForm) {
        const cvInput = document.getElementById('cv');
        const cvMessage = document.getElementById('cv_message');
        const lettreInput = document.getElementById('lettre_motivation');
        const lettreMessage = document.getElementById('lettre_motivation_message');
        
        if (cvInput && cvMessage && lettreInput && lettreMessage) {
            verifierChamps(postulerForm, [
                { input: cvInput, message: cvMessage },
                { input: lettreInput, message: lettreMessage }
            ]);
        }
    }
    
    // Formulaire de connexion
    const loginForm = document.getElementById("login-form");
    if (loginForm) {
        const identifiantInput = document.getElementById("identifiant");
        const identifiantMessage = document.getElementById("utilisateur-message");
        const mdpInput = document.getElementById("motdepasse");
        const mdpMessage = document.getElementById("mdp-message");
        
        if (identifiantInput && identifiantMessage && mdpInput && mdpMessage) {
            verifierChamps(loginForm, [
                { input: identifiantInput, message: identifiantMessage },
                { input: mdpInput, message: mdpMessage }
            ]);
        }
    }
    
    // Formulaire de contact
    const contactForm = document.getElementById("contact-form");
    if (contactForm) {
        const nameInput = document.getElementById("name");
        const nameMessage = document.getElementById("nom_message");
        const emailInput = document.getElementById("email");
        const emailMessage = document.getElementById("email_message");
        const messageInput = document.getElementById("message");
        const messageMessage = document.getElementById("commentaire_message");
        const subjectInput = document.getElementById("subject");
        const subjectMessage = document.getElementById("subject_message");
        
        if (nameInput && nameMessage && emailInput && emailMessage && 
            messageInput && messageMessage && subjectInput && subjectMessage) {
            verifierChamps(contactForm, [
                { input: nameInput, message: nameMessage },
                { input: emailInput, message: emailMessage },
                { input: messageInput, message: messageMessage },
                { input: subjectInput, message: subjectMessage }
            ]);
        }
        
        // Compteur de caractères pour le message
        if (messageInput) {
            const compteur = document.getElementById("compteur");
            if (compteur) {
                messageInput.addEventListener("input", function() {
                    const maxLength = 300;
                    const currentLength = this.value.length;
                    
                    compteur.textContent = `${currentLength}/${maxLength}`;
                    
                    if (currentLength >= 250) {
                        compteur.classList.add("compteur_active");
                    } else {
                        compteur.classList.remove("compteur_active");
                    }
                });
            }
        }
    }
});

// Fonction pour colorer un champ en erreur
function colorfield(input, message) {
    if (input.value.trim() === "" || (input.files && input.files.length === 0)) {
        input.style.border = "2px solid red";
        message.style.display = "block";
    }
}

// Fonction pour vérifier un champ
function checkfield(input, message) {
    if (input.value.trim() === "" || (input.files && input.files.length === 0)) {
        return true; // Champ vide
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
            alert("Veuillez remplir tous les champs requis avant de soumettre le formulaire.");
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
