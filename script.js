document.addEventListener("DOMContentLoaded", function(){
    const cookieBanner = document.getElementById("cookie-banner")
    const acceptButton = document.getElementById("accept-cookies")

    /*if(localStorage.getItem("cookiesAccepted") === 'true'){
      cookieBanner.style.display = "none";  
    }*/

    acceptButton.addEventListener("click", function(){
        localStorage.setItem("cookiesAccepted", "true");
        cookieBanner.style.display = "none";
    })
})
