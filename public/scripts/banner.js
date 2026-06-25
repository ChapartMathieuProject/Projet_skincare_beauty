// Fonction de défilement réutilisable (bannière + carrousel)
function defilement(track, zoneSurvol, vitesse) {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    let position = 0;
    let widthCopy = 0;
    let pause = false;

    function measure() {
        widthCopy = track.scrollWidth / 2;
    }
    measure();
    window.addEventListener("resize", measure);
    window.addEventListener("load", measure);

    zoneSurvol.addEventListener("mouseenter", () => { pause = true; });
    zoneSurvol.addEventListener("mouseleave", () => { pause = false; });

    function animate() {
        if (!pause && widthCopy > 0) {
            position -= vitesse;
            if (Math.abs(position) >= widthCopy) {
                position = 0;
            }
            track.style.transform = "translateX(" + position + "px)";
        }
        requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);
}

document.addEventListener("DOMContentLoaded", function () {

    // --- Bannière du haut ---
    const promoTrack = document.querySelector(".promo-track");
    if (promoTrack) {
        defilement(promoTrack, document.querySelector(".promo-banner"), 0.8);
    }

    // --- Carrousel Promotions Flash ---
    const flashTrack = document.querySelector(".flash-track");
    if (flashTrack) {
        defilement(flashTrack, flashTrack, 0.4);
    }
});