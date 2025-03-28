document.addEventListener("DOMContentLoaded", function(){
    const cookieBanner = document.getElementById("cookie-banner")
    const acceptButton = document.getElementById("accept-cookies")
    const burgerMenu = document.querySelector(".burger-menu");
    const menu = document.getElementById("menu");
  
    if (burgerMenu && menu) {
        burgerMenu.addEventListener("click", function () {
            menu.classList.toggle("active");
        });
    }
   
    if(localStorage.getItem("cookiesAccepted") === 'true'){
      cookieBanner.style.display = "none";  
    }
  
    acceptButton.addEventListener("click", function(){
        localStorage.setItem("cookiesAccepted", "true");
        cookieBanner.style.display = "none";
    })
  });
  
  
  function colorfield(input, message) {
    if (input.value.trim() === "" || input.files.length === 0) {
        input.style.border = "2px solid red";
        message.style.display = "block";
    }
}

function checkfield(input, message) {
    if (input.value.trim() === "" || input.files.length === 0) {
        return true; // Champ vide
    } else {
        input.style.border = "";
        message.style.display = "none";
        return false; // Champ rempli
    }
}

function verifierChamps(form, champs) {
    // Ajouter des écouteurs d'événements pour la saisie
    champs.forEach(champ => {
        champ.input.addEventListener("input", function() {
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
  
  function search() {
      const searchInput = document.getElementById('search-input').value;
      const urlParams = new URLSearchParams(window.location.search);
      const activeTab = urlParams.get('tab') || 'utilisateur';
      window.location.href = `Admin.php?tab=${activeTab}&search=${searchInput}`;
  }

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
      // Ajouter seulement cette partie pour sauvegarder l'état du like
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

function postuleroffre(){
    verifierChamps(document.getElementById("candidature-form") , [
        { input: document.getElementById("cv"), message: document.getElementById("cv_message")},
        { input: document.getElementById("lettre_motivation"), message: document.getElementById("lettre_motivation_message")}
    ])
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

