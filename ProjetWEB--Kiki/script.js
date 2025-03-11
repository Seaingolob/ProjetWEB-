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

function toggleHeart(event) {
let heart = event.currentTarget;
if (heart.innerText === "🤍") {
    heart.innerText = "❤️";
    heart.classList.add("liked");
} else {
    heart.innerText = "🤍";
    heart.classList.remove("liked");
}
}

document.getElementById('backButton').addEventListener('click', function() {
window.history.back();
});

function connexion(){
  const identifiant = document.getElementById("identifiant");
  const motdepasse = document.getElementById("motdepasse");
  const form = document.getElementById("login-form");
  const utilmessage = document.getElementById("utilisateur-message");
  const mdpmessage = document.getElementById("mdp-message");
  const incorrect_mes = document.getElementById("incorrect-message");

  // Fonction pour vérifier si les champs sont remplis et retirer les bordures rouges

      // Vérifie les champs à chaque modification
      identifiant.addEventListener("input", checkfield);
      motdepasse.addEventListener("input", checkfield);

      // Fonction pour gérer la bordure rouge des champs vides lors de la soumission
      function checkfield() {
          let champsVides = false;        
          if (identifiant.value.trim() === "") {
              champsVides = true;
          } else {
              identifiant.style.border = "";
              utilmessage.style.display = "none";
          }
          if (motdepasse.value.trim() === "") {
              champsVides = true;
          } else {
              motdepasse.style.border = "";
              mdpmessage.style.display = "none";
          }           
          return champsVides;
      }

      function colorfield(){
          if (identifiant.value.trim() === "") {
              identifiant.style.border = "2px solid red";
              utilmessage.style.display = "block"; 
          }      
          if (motdepasse.value.trim() === "") {
              motdepasse.style.border = "2px solid red";
              mdpmessage.style.display = "block";
          }
      }
      
      // Efface le contenu des champs mdp et identifiant
      function emptyfield(){
          identifiant.value = "";
          motdepasse.value = "";
      }

      // Soumettre le formulaire si les champs sont remplis
      form.addEventListener("submit", function(event) {
          // Vérifier d'abord si des champs sont vides
          const champsVides = checkfield();    
          // Si des champs sont vides, empêcher la soumission 
          if (champsVides) {
              event.preventDefault(); // Empêche la soumission du formulaire
              colorfield();
          }
  })
};


