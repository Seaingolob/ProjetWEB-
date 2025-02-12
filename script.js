// script.js

// Fonction pour naviguer entre les pages avec une transition
function goToPage2() {
    // Ajouter la classe de transition à l'overlay
    document.getElementById('transition-overlay').classList.add('transition-active');
    
    // Appliquer la transition diagonale à la page
    setTimeout(function() {
        document.getElementById('page1').classList.add('page-transition');  // Page 1 reçoit la transition
        document.getElementById('page2').style.display = "flex"; // Afficher la page 2
        document.getElementById('page1').style.display = "none"; // Masquer la page 1
    }, 500); // Attendre un peu avant de lancer la transition pour que l'overlay soit visible
    
    // Retirer l'overlay après la transition
    setTimeout(function() {
        document.getElementById('transition-overlay').classList.remove('transition-active');
    }, 1500); // Retirer l'overlay après 1.5 seconde, lorsque l'animation est terminée
}

function goToPage1() {
    // Ajouter la classe de transition à l'overlay
    document.getElementById('transition-overlay').classList.add('transition-active');
    
    // Appliquer la transition diagonale à la page
    setTimeout(function() {
        document.getElementById('page2').classList.add('page-transition');  // Page 2 reçoit la transition
        document.getElementById('page1').style.display = "flex"; // Afficher la page 1
        document.getElementById('page2').style.display = "none"; // Masquer la page 2
    }, 500); // Attendre un peu avant de lancer la transition pour que l'overlay soit visible
    
    // Retirer l'overlay après la transition
    setTimeout(function() {
        document.getElementById('transition-overlay').classList.remove('transition-active');
    }, 1500); // Retirer l'overlay après 1.5 seconde, lorsque l'animation est terminée
}
