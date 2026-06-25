<?php
include "public/includes/header.php";
?>

<div class="ariadne container small text-secondary pt-4 pb-1 d-flex gap-2">
    <a href="#">Accueil</a><span>></span>
    <a href="#">Sérums</a><span>></span>
    <span class="ariadne-product">Sérum Vitamin C Eclat</span>
</div>

<section class="container- py-4">
    <div class="row g-5 align-items-start">
        <div class="col-12 col-lg-6">
            <div class="gallery-sticky">
                <div class="img-main position-relative d-flex align-items-end justify-content-center">
                    <span class="badge position-absolute top-0 start-0 m-3">-27%</span>
                    <button id="btn-wishlist" type="button" aria-label="Ajouter à la wishlit"
                        class="btn position-absolute top-0 end-0 m-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fa-reg-he fa-regular fa-heart">
                    </button>
                    <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="Produit en promotion">
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button class="vignette active" data-titre="flacon — vue face"></button>
                    <button class="vignette" data-titre="texture / swatch"></button>
                    <button class="vignette" data-titre="packaging"></button>
                    <button class="vignette" data-titre="en situation"></button>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <p class="overtitle-brand mb-2" Glowlab></p>
            <h1 class="title-product h2 mb-3">Sérum Vitamin C Eclat</h1>
            <ul class="atouts-paper list-unstyled d-flex flex-column gap-2 mb-4">
                <li class="d-flex gap-2"><span class="puce">✦</span>Illumine, unifie le teint et booste l'éclat dès 7 jours</li>
                <li class="d-flex gap-2"><span class="puce">✦</span>Concentré à 15% en Vitamine C stabilisée + acide hyaluronique</li>
                <li class="d-flex gap-2"><span class="puce">✦</span>Formule naturelle, vegan, convient aux peaux sensibles</li>
            </ul>
            <div class="d-flex align-items-baseline gap-3 mb-1">
                <span class="actual-price" id="actual-price">39.90€</span>
                <span class="strike-price fs-5" id="strike-price">54.90€</span>
                <span class="promotion-badge badge rounded px-2 py-1" id="promotion-badge">-27%</span>
            </div>
            <p class="saving small mb-4" id="saving">Vous économisez 15.00€</p>

            <p class="small fw-semibold mb-2">CONTENANCE</p>
            <div class="d-flex gap-3 mb-4">
                <button class="variante active p-3" data-price="39.90" data-old="54.90">
                    <span class="nom d-block">30 ml</span>
                    <span class="sous d-block">Format découverte</span>
                </button>
            </div>


        </div>
    </div>



</section>
<?php include "public/includes/footer.php"; ?>