
// Principalement fait avec le travail réalisé sur ma bibliothèque SASS/SCSS
(function () {
    const banner = document.querySelector(".promo-banner");
    const track = document.querySelector(".promo-track");

    // Cela permet de désactiver l'animation si l'utilisateur à réglé "réduire les animations" dans ses paramètres
    const reduct = window.matchMedia("(prefers-reduced-motion: reduce)");
    if (reduct.matches) return;

    let position = 0;       // décalage horizontal en pixels
    let widthCopy = 0;      // largeur d'une copie du message
    const vitesse = 0.8;    // pixels par frame (49px/s à 60fps)
    let pause = false;

    //J'ai mis 2 copies de la bannière de sorte qu'il y ait un roulement, c'était plus simple pour le responsive de la bibliothèque SASS/SCSS
    function measure() {
        widthCopy = track.scrollWidth / 2; 
    }
    measure();
    window.addEventListener("resize", measure);

    //Pause au survol
    banner.addEventListener("mouseenter", () => { pause = true; });
    banner.addEventListener("mouseleave", () => { pause = false; });

    function animate() {
        if (!pause) {
            position -= vitesse; //Lorsque l'on a défilé une copie ça remet à 0; avec la 2nd ça fait un semblant de boucle invisible
            if (Math.abs(position) >= widthCopy) {
                position = 0;
            }
            track.style.transform = "translateX(" + position + "px)";
        }
        requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);
})();
