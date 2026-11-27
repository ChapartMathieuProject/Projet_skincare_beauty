// Fonction de défilement réutilisable (bannière + carrousel)
// vitesse en pixels par seconde : le défilement dépend du temps écoulé,
// pas du nombre d'images affichées (même vitesse à 60 Hz et à 120 Hz).
// prepare (optionnel) : appelée avant chaque mesure (chargement, redimensionnement) pour ajuster
// le contenu de la piste ; la piste doit toujours contenir exactement 2 copies identiques.
function defilement(track, zoneSurvol, vitesse, prepare) {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    // Écart maximal pris en compte entre deux images (évite un bond au retour sur l'onglet)
    const MAX_FRAME_MS = 100;

    let position = 0;
    let widthCopy = 0;
    let pause = false;
    let previousTime = null;

    function measure() {
        if (prepare) {
            prepare();
        }
        widthCopy = track.scrollWidth / 2;
        // Le contenu est périodique : ramener la position dans [-widthCopy, 0] ne change pas l'image.
        if (widthCopy > 0) {
            position = -((-position) % widthCopy);
        }
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

// Carrousel Promotions Flash : la piste doit toujours être pleine, quel que soit le nombre de
// produits en promotion et la largeur d'écran. Une « copie » répète l'ensemble d'origine autant
// de fois qu'il faut pour couvrir la largeur visible ; la piste contient 2 copies (boucle sans saut).
// Les éléments ajoutés sont décoratifs : aria-hidden et liens hors de la tabulation.
function preparerPisteFlash(track) {
    // Produits d'origine : ceux rendus sans aria-hidden par le serveur (la 2e passe est un doublon).
    const originaux = Array.from(track.children).filter((el) => el.getAttribute("aria-hidden") !== "true");
    let repetitions = 0;

    function cloner(el) {
        const clone = el.cloneNode(true);
        clone.setAttribute("aria-hidden", "true");
        clone.dataset.flashClone = "1";
        clone.querySelectorAll("a, button, input, select, textarea, [tabindex]").forEach((n) => {
            n.setAttribute("tabindex", "-1");
        });
        return clone;
    }

    return function remplir() {
        if (originaux.length === 0) {
            return;
        }
        const zoneVisible = track.parentElement.clientWidth;
        const largeurEnsemble = originaux.reduce((somme, el) => somme + el.getBoundingClientRect().width, 0);
        if (largeurEnsemble <= 0) {
            return;
        }
        const besoin = Math.max(1, Math.ceil(zoneVisible / largeurEnsemble));
        if (besoin === repetitions) {
            return; // rien à changer : pas de reconstruction, donc pas d'à-coup
        }
        repetitions = besoin;

        // On ne garde que les originaux, puis on ajoute 2 × repetitions − 1 ensembles clonés.
        Array.from(track.children).forEach((el) => {
            if (!originaux.includes(el)) {
                el.remove();
            }
        });
        const fragment = document.createDocumentFragment();
        for (let i = 1; i < 2 * repetitions; i++) {
            originaux.forEach((el) => fragment.appendChild(cloner(el)));
        }
        track.appendChild(fragment);
    };
}

document.addEventListener("DOMContentLoaded", function () {

    // --- Bannière du haut : 48 px/s (rendu identique à l'ancien 0,8 px/image à 60 Hz) ---
    const promoTrack = document.querySelector(".promo-track");
    if (promoTrack) {
        defilement(promoTrack, document.querySelector(".promo-banner"), 48);
    }

    // --- Carrousel Promotions Flash : 60 px/s, piste toujours pleine ---
    const flashTrack = document.querySelector(".flash-track");
    if (flashTrack) {
        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            // Pas d'animation : on retire le doublon rendu par le serveur (aucun clone superflu).
            flashTrack.querySelectorAll('.flash-item[aria-hidden="true"]').forEach((el) => el.remove());
        } else {
            defilement(flashTrack, flashTrack, 60, preparerPisteFlash(flashTrack));
        }
    }
});