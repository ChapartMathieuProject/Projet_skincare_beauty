<?php
include "public/includes/header.php";
?>

<div class="ariadne container small text-secondary pt-4 pb-1 d-flex gap-2">
    <a href="#">Accueil</a><span>></span>
    <a href="#">Sérums</a><span>></span>
    <span class="ariadne-product">Sérum Vitamin C Eclat</span>
</div>

<section class="container py-4">
    <div class="row g-5 align-items-start">
        <div class="col-12 col-lg-6">
            <div class="gallery-sticky">
                <div class="img-main position-relative d-flex align-items-end justify-content-center">
                    <span class="badge-reduction position-absolute top-0 start-0 m-3">-27%</span>
                    <button id="btn btn-wishlist" type="button" aria-label="Ajouter à la wishlit"
                        class="btn position-absolute top-0 end-0 m-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class=" test fa-reg-he fa-regular fa-heart"></i>
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
            <p class="overtitle-brand mb-2">Glowlab</p>
            <h1 class="title-product h2 mb-3">Sérum Vitamin C Eclat</h1>
            <ul class="atouts-paper list-unstyled d-flex flex-column gap-2 mb-4">
                <li class="d-flex gap-2"><span class="puce">✦</span>Illumine, unifie le teint et booste l'éclat dès 7 jours</li>
                <li class="d-flex gap-2"><span class="puce">✦</span>Concentré à 15% en Vitamine C stabilisée + acide hyaluronique</li>
                <li class="d-flex gap-2"><span class="puce">✦</span>Formule naturelle, vegan, convient aux peaux sensibles</li>
            </ul>
            <div class="d-flex align-items-baseline gap-3 mb-1">
                <span class="actual-price" id="actual-price">39.90€</span>
                <span class="strike-price fs-5" id="strike-price">54.90€</span>
                <span class="promotion-badge-reduction badge rounded px-2 py-1" id="promotion-badge">-27%</span>
            </div>
            <p class="saving small mb-4" id="saving">Vous économisez 15.00€</p>

            <p class="small fw-semibold mb-2">CONTENANCE</p>
            <div class="d-flex gap-3 mb-4">
                <button class="variante active p-3" data-price="39.90" data-old="54.90">
                    <span class="nom d-block">30 ml</span>
                    <span class="sous d-block">Format découverte</span>
                </button>
            </div>
            <div class="d-flex gap-3 mb-3">
                <div class="choice-quantity d-flex align-items-center">
                    <button type="button" id="qty-less" aria-label="Diminiuer">-</button>
                    <span id="qty" class="text-center fw-semibold">1</span>
                    <button type="button" id="qty-more" aria-label="Augmenter">+</button>
                </div>
                <button class="btn-rose btn-add flex-fill d-flex align-items-center justify-content-center gap-2"
                    id="btn-add" type="button">
                    <i class="fa-solid fa-bag-shopping"></i> Ajouter au panier
                </button>
            </div>
            <div class="d-flex align-items-center gap-2 mb-4 small text-secondary">
                <span class="spot-stock"></span>
                <span>En stock - expédié sous 3ans</span>
            </div>

            <div class="reassurance row g-3 py-4">
                <div class="col-6 d-flex align-items-center gap-2 small">
                    <i class="fa-solid fa-leaf"></i> 100% Naturel
                </div>
                <div class="col-6 d-flex align-items-center gap-2 small">
                    <i class="fa-solid fa-heart"></i> Cruelty-free
                </div>
                <div class="col-6 d-flex align-items-center gap-2 small">
                    <i class="fa-solid fa-truck"></i> Livraison 48h
                </div>
                <div class="col-6 d-flex align-items-center gap-2 small">
                    <i class="fa-solid fa-rotate-left"></i> Retours 30 jours
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container pb-5">
    <div class="d-flex gap-1 border-bottom mb-4 overflow-auto" id="tab-bar">
        <button class="tab active" data-target="desc">Description</button>
        <button class="tab" data-target="ingr">Ingrédients</button>
        <button class="tab" data-target="usage">Conseils d'utilisation</button>
        <button class="tab" data-target="ship">Livraison &amp; retours</button>
    </div>
    <div class="tab-contents">
        <div data-sign="desc">
            <p class="mb-3">Le <strong>Sérum Vitamin C Éclat</strong> de GlowLab est un
                concentré quotidien pensé pour révéler la luminosité naturelle de la peau. Sa formule légère et
                non grasse pénètre instantanément pour un fini velouté.
            </p>
            <p class="mb-0">Enrichi en Vitamine C stabilisée à 15% et en acide hyaluronique, il agit sur les taches
                pigmentaires, unifie le teint et lisse le grain de peau. Utilisé matin et soir, il redonne éclat et
                fermeté tout en protégeant des agressions extérieures.</p>
        </div>
        <div data-sign="ingr" class="d-none">
            <p class="fw-medium mb-3">Actifs clés</p>
            <ul class="d-flex flex-column gap-2 mb-3">
                <li>Vitamine C (Ascorbic Acid) 15% — éclat &amp; anti-taches</li>
                <li>Acide hyaluronique — hydratation profonde</li>
                <li>Vitamine E — protection antioxydante</li>
            </ul>
            <p class="small text-muted mb-0">INCI : Aqua, Ascorbic Acid, Glycerin, Sodium</p>
        </div>
        <div data-sign="usage" class="d-none">
            <p class="mb-3">Appliquer 3 à 4 gouttes sur peau propre et sèche, matin et/ou soir, en évitant le
                contour des yeux.</p>
            <ol class="d-flex flex-column gap-2 mb-0">
                <li>Nettoyez et séchez délicatement votre visage.</li>
                <li>Déposez le sérum et faites pénétrer par petits mouvements circulaires.</li>
                <li>Faites suivre de votre crème hydratante, puis d'une protection solaire le matin.</li>
            </ol>
        </div>
    </div>
</section>

<section class="container py-5">
    <h2 class="h3 text-center mb-4">Vous aimerez aussi</h2>
    <div class="row g-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card-bind h-100 d-flex flex-column">
                <div class="visual position-relative">
                    <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="">
                    <span class="badge-reduction position-absolute top-0 start-0 m-2">-24%</span>
                </div>
                <div class="p-3 d-flex flex-column flex-fill">
                    <span class="brand">PureBeauty</span>
                    <span class="name my-1">Crème Hydratante Rose</span>
                    <div class="mt-auto d-flex align-items-center justify-content-between">
                        <span><span class="price-prom-badge fw-bold"">45,00 €</span>
                            <span class=" strike-price small ms-1">59,00 €</span></span>
                        <button class="btn-rose btn-bag rounded-circle d-flex align-items-center 
                        justify-content-center" aria-label="Ajouter au panier">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card-bind h-100 d-flex flex-column">
                <div class="visual position-relative">
                    <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="">
                    <span class="badge-reduction position-absolute top-0 start-0 m-2">-24%</span>
                </div>
                <div class="p-3 d-flex flex-column flex-fill">
                    <span class="brand">Essence</span>
                    <span class="name my-1">Parfum Fleur de Cerisier</span>
                    <span class="note small mb-3"><i class="fa-solid fa-star"></i> 4,7</span>
                    <div class="mt-auto d-flex align-items-center justify-content-between">
                        <span><span class="price-prom-badge fw-bold" ">89,90 €</span>
                            <span class="strike-price small ms-1">119,00 €</span></span>
                        <button class="btn-rose btn-bag rounded-circle d-flex align-items-center justify-content-center"
                             aria-label="Ajouter au panier">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card-bind h-100 d-flex flex-column">
                <div class="visual position-relative">
                    <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="">
                    <span class="badge-reduction position-absolute top-0 start-0 m-2">-24%</span>
                </div>
                <div class="p-3 d-flex flex-column flex-fill">
                    <span class="brand">Essence</span>
                    <span class="name my-1">Parfum Fleur de Cerisier</span>
                    <span class="note small mb-3"><i class="fa-solid fa-star"></i> 4,7</span>
                    <div class="mt-auto d-flex align-items-center justify-content-between">
                        <span><span class=".price-prom-badge fw-bold" ">89,90 €</span>
                            <span class="strike-price small ms-1">119,00 €</span></span>
                        <button class="btn-rose btn-bag rounded-circle d-flex align-items-center justify-content-center"
                             aria-label="Ajouter au panier">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="public/scripts/product.js"></script>

<?php include "public/includes/footer.php"; ?>