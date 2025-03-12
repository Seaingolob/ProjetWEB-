document.addEventListener("DOMContentLoaded", function () {
    const cookieBanner = document.getElementById("cookie-banner");
    const acceptButton = document.getElementById("accept-cookies");
    const burgerMenu = document.querySelector(".burger-menu");
    const menu = document.getElementById("menu");

    if (burgerMenu && menu) {
        burgerMenu.addEventListener("click", function () {
            menu.classList.toggle("active");
        });
    }

    if (localStorage.getItem("cookiesAccepted") === "true") {
        cookieBanner.style.display = "none";
    }

    acceptButton.addEventListener("click", function () {
        localStorage.setItem("cookiesAccepted", "true");
        cookieBanner.style.display = "none";
    });
});

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

document.getElementById('backButton').addEventListener('click', function() {
window.history.back();
});

function connexion() {
  const identifiant = document.getElementById("identifiant");
  const motdepasse = document.getElementById("motdepasse");
  const form = document.getElementById("login-form");
  const utilmessage = document.getElementById("utilisateur-message");
  const mdpmessage = document.getElementById("mdp-message");

  identifiant.addEventListener("input", checkfield);
  motdepasse.addEventListener("input", checkfield);

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

  function colorfield() {
      if (identifiant.value.trim() === "") {
          identifiant.style.border = "2px solid red";
          utilmessage.style.display = "block";
      }
      if (motdepasse.value.trim() === "") {
          motdepasse.style.border = "2px solid red";
          mdpmessage.style.display = "block";
      }
  }

  form.addEventListener("submit", function(event) {
      const champsVides = checkfield();
      if (champsVides) {
          event.preventDefault();
          colorfield();
      }
  });
}


