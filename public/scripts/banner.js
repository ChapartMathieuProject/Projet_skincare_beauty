// Fonction de défilement réutilisable (bannière + carrousel)
// vitesse en pixels par seconde : le défilement dépend du temps écoulé,
// pas du nombre d'images affichées (même vitesse à 60 Hz et à 120 Hz).
function defilement(track, zoneSurvol, vitesse) {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    // Écart maximal pris en compte entre deux images (évite un bond au retour sur l'onglet)
    const MAX_FRAME_MS = 100;

    let position = 0;
    let widthCopy = 0;
    let pause = false;
    let previousTime = null;

    function measure() {
        widthCopy = track.scrollWidth / 2;
    }
    measure();
    window.addEventListener("resize", measure);
    window.addEventListener("load", measure);

    zoneSurvol.addEventListener("mouseenter", () => { pause = true; });
    zoneSurvol.addEventListener("mouseleave", () => { pause = false; });

    function animate(now) {
        const elapsedSeconds = previousTime === null ? 0 : Math.min(now - previousTime, MAX_FRAME_MS) / 1000;
        previousTime = now;

        if (!pause && widthCopy > 0) {
            position -= vitesse * elapsedSeconds;
            if (position <= -widthCopy) {
                position += widthCopy;
            }
            track.style.transform = "translateX(" + position + "px)";
        }
        requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);
}

document.addEventListener("DOMContentLoaded", function () {

    // --- Bannière du haut : 48 px/s (rendu identique à l'ancien 0,8 px/image à 60 Hz) ---
    const promoTrack = document.querySelector(".promo-track");
    if (promoTrack) {
        defilement(promoTrack, document.querySelector(".promo-banner"), 48);
    }

    // --- Carrousel Promotions Flash : 60 px/s (24 px/s auparavant à 60 Hz) ---
    const flashTrack = document.querySelector(".flash-track");
    if (flashTrack) {
        defilement(flashTrack, flashTrack, 60);
    }
});