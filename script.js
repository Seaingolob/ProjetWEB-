document.addEventListener("DOMContentLoaded", function () {
    // Gestion de la bannière de cookies
    const cookieBanner = document.getElementById("cookie-banner");
    const acceptButton = document.getElementById("accept-cookies");

    if (cookieBanner && acceptButton) {
        if (localStorage.getItem("cookiesAccepted") === 'true') {
            cookieBanner.style.display = "none";
        }

        acceptButton.addEventListener("click", function () {
            localStorage.setItem("cookiesAccepted", "true");
            cookieBanner.style.display = "none";
        });
    }

    // Gestion du menu burger
    const burgerMenu = document.querySelector(".burger-menu");
    const menu = document.getElementById("menu");

    if (burgerMenu && menu) {
        burgerMenu.addEventListener("click", function () {
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
        champ.input.addEventListener("input", function () {
            checkfield(champ.input, champ.message);
        });
        // Ajouter pour les fichiers
        champ.input.addEventListener("change", function () {
            checkfield(champ.input, champ.message);
        });
    });

    form.addEventListener("submit", function (event) {
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

function compteurmessage() {
    const elements = ["message", "description"];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener("input", function () {
                const maxLength = 300;
                const currentLength = this.value.length;

                document.getElementById("compteur").textContent = `${currentLength}/${maxLength}`;

                if (currentLength == 250) {
                    document.getElementById("compteur").classList.add("compteur_active");
                }
            });
        }
    });
}


function connexion() {
    verifierChamps(document.getElementById("login-form"), [
        { input: document.getElementById("identifiant"), message: document.getElementById("utilisateur-message") },
        { input: document.getElementById("motdepasse"), message: document.getElementById("mdp-message") }
    ]);
}

function contact() {
    verifierChamps(document.getElementById("contact-form"), [
        { input: document.getElementById("name"), message: document.getElementById("nom_message") },
        { input: document.getElementById("email"), message: document.getElementById("email_message") },
        { input: document.getElementById("message"), message: document.getElementById("commentaire_message") },
        { input: document.getElementById("subject"), message: document.getElementById("subject_message") }
    ])
}

function postuleroffre() {
    verifierChamps(document.getElementById("postulerForm"), [
        { input: document.getElementById("cv"), message: document.getElementById("cv_message") },
        { input: document.getElementById("lettre_motivation"), message: document.getElementById("lettre_motivation_message") }
    ])
}

function creationoffre() {
    verifierChamps(document.getElementById("form-offres"), [
        { input: document.getElementById("titre"), message: document.getElementById("titre_offre_message") },
        { input: document.getElementById("entreprise-select"), message: document.getElementById("entreprise_select_message") },
        { input: document.getElementById("nouvelle-entreprise-nom"), message: document.getElementById("nouvelle_entreprise_nom") },
        { input: document.getElementById("entreprise-description"), message: document.getElementById("entreprise_description_message") },
        { input: document.getElementById("entreprise-site"), message: document.getElementById("entreprise_site_message") },
        { input: document.getElementById("region"), message: document.getElementById("region_message") },
        { input: document.getElementById("ville"), message: document.getElementById("ville_message") },
        { input: document.getElementById("nouvelle-ville-nom"), message: document.getElementById("nouvelle_ville_nom_message") },
        { input: document.getElementById("adresse"), message: document.getElementById("adresse_message") },
        { input: document.getElementById("duree"), message: document.getElementById("duree_message") },
        { input: document.getElementById("date-debut"), message: document.getElementById("date_debut_message") },
        { input: document.getElementById("description"), message: document.getElementById("description_message") },
        { input: document.getElementById("nouvelles-competences"), message: document.getElementById("nouvelles_competences_message") },
    ])

    // Entreprise
    const entrepriseExistanteRadio = document.getElementById('entreprise-existante');
    const nouvelleEntrepriseRadio = document.getElementById('nouvelle-entreprise');
    const sectionEntrepriseExistante = document.getElementById('section-entreprise-existante');
    const sectionNouvelleEntreprise = document.getElementById('section-nouvelle-entreprise');
    
    // Ville
    const villeExistanteRadio = document.getElementById('ville-existante');
    const nouvelleVilleRadio = document.getElementById('nouvelle-ville');
    const sectionNouvelleVille = document.getElementById('section-nouvelle-ville');
    
    // Région
    const regionExistanteRadio = document.getElementById('region-existante');
    const nouvelleRegionRadio = document.getElementById('nouvelle-region');
    const sectionNouvelleRegion = document.getElementById('section-nouvelle-region');
    
    // Compétences et Secteurs
    const nouvelleCompetenceCheck = document.getElementById('nouvelle-competence-check');
    const nouvelleCompetenceSection = document.getElementById('nouvelle-competence-section');
    const nouveauSecteurCheck = document.getElementById('nouveau-secteur-check');
    const nouveauSecteurSection = document.getElementById('nouveau-secteur-section');
    
    // Gestion des entreprises
    entrepriseExistanteRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionEntrepriseExistante.classList.remove('hidden');
            sectionNouvelleEntreprise.classList.add('hidden');
        }
    });
    
    nouvelleEntrepriseRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionEntrepriseExistante.classList.add('hidden');
            sectionNouvelleEntreprise.classList.remove('hidden');
        }
    });
    
    // Gestion des villes
    villeExistanteRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionNouvelleVille.classList.add('hidden');
        }
    });
    
    nouvelleVilleRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionNouvelleVille.classList.remove('hidden');
        }
    });
    
    // Gestion des régions
    regionExistanteRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionNouvelleRegion.classList.add('hidden');
        }
    });
    
    nouvelleRegionRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionNouvelleRegion.classList.remove('hidden');
        }
    });
    
    // Gestion des nouvelles compétences
    nouvelleCompetenceCheck.addEventListener('change', function() {
        if (this.checked) {
            nouvelleCompetenceSection.classList.remove('hidden');
        } else {
            nouvelleCompetenceSection.classList.add('hidden');
        }
    });
    
    // Gestion des nouveaux secteurs
    nouveauSecteurCheck.addEventListener('change', function() {
        if (this.checked) {
            nouveauSecteurSection.classList.remove('hidden');
        } else {
            nouveauSecteurSection.classList.add('hidden');
        }
    });
}

function creationutilisateur() {
    verifierChamps(document.getElementById("form-utilisateur"), [
        { input: document.getElementById("nom"), message: document.getElementById("nom_message") },
        { input: document.getElementById("prenom"), message: document.getElementById("prenom_message") },
        { input: document.getElementById("mail"), message: document.getElementById("mail_message") },
        { input: document.getElementById("mot_de_passe"), message: document.getElementById("mot_de_passe_message") },
        { input: document.getElementById("telephone"), message: document.getElementById("telephone_message") },
        { input: document.getElementById("campus-select"), message: document.getElementById("campus_select_message") },
        { input: document.getElementById("ville"), message: document.getElementById("ville_message") },
        { input: document.getElementById("nouvelle-ville-nom"), message: document.getElementById("nouvelle_ville_nom_message") },
        { input: document.getElementById("adresse"), message: document.getElementById("adresse_message") },
        { input: document.getElementById("promotion-select"), message: document.getElementById("promotion_select_message") },
        { input: document.getElementById("nouvelle-promotion-nom"), message: document.getElementById("nouvelle_promotion_nom_message") },
        { input: document.getElementById("region"), message: document.getElementById("region_message") },
        { input: document.getElementById("nouveau-campus-nom"), message: document.getElementById("nouveau_campus_nom") },
    ])

    // Type d'utilisateur
    const typeEtudiantRadio = document.getElementById('type-etudiant');
    const typePiloteRadio = document.getElementById('type-pilote');
    const typeAdminRadio = document.getElementById('type-admin');
    const sectionCampusPromotion = document.getElementById('section-campus-promotion');
    const sectionAdmin = document.getElementById('section-admin');
    
    // Campus
    const campusExistantRadio = document.getElementById('campus-existant');
    const nouveauCampusRadio = document.getElementById('nouveau-campus');
    const sectionCampusExistant = document.getElementById('section-campus-existant');
    const sectionNouveauCampus = document.getElementById('section-nouveau-campus');
    
    // Ville
    const villeExistanteRadio = document.getElementById('ville-existante');
    const nouvelleVilleRadio = document.getElementById('nouvelle-ville');
    const sectionVilleExistante = document.getElementById('section-ville-existante');
    const sectionNouvelleVille = document.getElementById('section-nouvelle-ville');
    
    // Promotion
    const promotionExistanteRadio = document.getElementById('promotion-existante');
    const nouvellePromotionRadio = document.getElementById('nouvelle-promotion');
    const sectionPromotionExistante = document.getElementById('section-promotion-existante');
    const sectionNouvellePromotion = document.getElementById('section-nouvelle-promotion');
    
    // Gestion du type d'utilisateur
    typeEtudiantRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionCampusPromotion.classList.remove('hidden');
            sectionAdmin.classList.add('hidden');
        }
    });
    
    typePiloteRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionCampusPromotion.classList.remove('hidden');
            sectionAdmin.classList.add('hidden');
        }
    });
    
    typeAdminRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionCampusPromotion.classList.add('hidden');
            sectionAdmin.classList.remove('hidden');
        }
    });
    
    // Gestion des campus
    campusExistantRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionCampusExistant.classList.remove('hidden');
            sectionNouveauCampus.classList.add('hidden');
        }
    });
    
    nouveauCampusRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionCampusExistant.classList.add('hidden');
            sectionNouveauCampus.classList.remove('hidden');
        }
    });
    
    // Gestion des villes
    villeExistanteRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionVilleExistante.classList.remove('hidden');
            sectionNouvelleVille.classList.add('hidden');
        }
    });
    
    nouvelleVilleRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionVilleExistante.classList.add('hidden');
            sectionNouvelleVille.classList.remove('hidden');
        }
    });
    
    // Gestion des promotions
    promotionExistanteRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionPromotionExistante.classList.remove('hidden');
            sectionNouvellePromotion.classList.add('hidden');
        }
    });
    
    nouvellePromotionRadio.addEventListener('change', function() {
        if (this.checked) {
            sectionPromotionExistante.classList.add('hidden');
            sectionNouvellePromotion.classList.remove('hidden');
        }
    });
    
    // Chargement des villes en fonction de la région sélectionnée
    const regionSelect = document.getElementById('region');
    const villeSelect = document.getElementById('ville');
    
    // Données des villes pré-chargées (pour éviter des requêtes AJAX)
    const villesByRegion = {};

    // Organiser les villes par région
    allVilles.forEach(ville => {
        if (!villesByRegion[ville.id_region]) {
            villesByRegion[ville.id_region] = [];
        }
        villesByRegion[ville.id_region].push(ville);
    });
    
    regionSelect.addEventListener('change', function() {
        const regionId = this.value;
        villeSelect.innerHTML = '<option value="">Sélectionner une ville</option>';
        villeSelect.disabled = !regionId;
        
        if (regionId && villesByRegion[regionId]) {
            villesByRegion[regionId].forEach(ville => {
                const option = document.createElement('option');
                option.value = ville.id_ville;
                option.textContent = ville.nom_ville;
                villeSelect.appendChild(option);
            });
        }
    });
}

function competences(){
    // Conteneur de compétences
    const competencesContainer = document.getElementById('competences-container');
        
    // Configurer l'affichage initial des boutons
    if (competencesContainer.querySelectorAll('.competence-row').length > 1) {
        document.querySelectorAll('.remove-competence-btn').forEach(btn => {
            btn.style.display = 'inline-flex';
        });
    }
    
    // Gérer les clics sur les boutons + et -
    competencesContainer.addEventListener('click', function(e) {
        // Si bouton d'ajout cliqué
        if (e.target.classList.contains('add-competence-btn')) {
            // Cloner le premier sélecteur de compétence
            const firstRow = competencesContainer.querySelector('.competence-row');
            const newRow = firstRow.cloneNode(true);
            
            // Réinitialiser la sélection
            newRow.querySelector('select').selectedIndex = 0;
            
            // Afficher le bouton de suppression pour toutes les lignes
            document.querySelectorAll('.remove-competence-btn').forEach(btn => {
                btn.style.display = 'inline-flex';
            });
            
            // Ajouter la nouvelle ligne
            competencesContainer.appendChild(newRow);
        }
        
        // Si bouton de suppression cliqué
        if (e.target.classList.contains('remove-competence-btn')) {
            // Ne pas supprimer si c'est la dernière ligne
            if (competencesContainer.querySelectorAll('.competence-row').length > 1) {
                e.target.closest('.competence-row').remove();
                
                // Cacher le bouton de suppression s'il ne reste qu'une seule ligne
                if (competencesContainer.querySelectorAll('.competence-row').length === 1) {
                    competencesContainer.querySelector('.remove-competence-btn').style.display = 'none';
                }
            }
        }
    });
}

