
<?php
include "public/includes/header.php";
?>



<section class="hero py-5">
  <div class="container py-4 text-center">
    <div class="mx-auto" style="max-width: 720px;">
      <h1 class="hero-titre mb-3">Révélez votre beauté naturelle</h1>
      <p class="text-secondary mb-4">
        Découvrez notre collection exclusive de soins de la peau formulés avec des
        ingrédients naturels et éthiques pour une peau radieuse.
      </p>
      <button class="btn-rose">Découvrir la collection</button>

      <div class="d-flex flex-wrap justify-content-center gap-4 mt-5">
        <div class="atout d-flex align-items-center gap-2">
          <i class="fa-solid fa-leaf"></i><span class="small text-secondary">100% Naturel</span>
        </div>
        <div class="atout d-flex align-items-center gap-2">
          <i class="fa-solid fa-heart"></i><span class="small text-secondary">Cruelty-free</span>
        </div>
        <div class="atout d-flex align-items-center gap-2">
          <i class="fa-solid fa-truck"></i><span class="small text-secondary">Livraison 48h</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section>
  <!-- Caroussel -->
</section>
<div class="py-4 bg-white">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-center gap-2" id="filter">
      <button class="filtre actif" data-filtre="tous">Tous</button>
      <button class="filtre actif" data-filtre="tous">Sérums</button>
      <button class="filtre actif" data-filtre="tous">Crèmes</button>
      <button class="filtre actif" data-filtre="tous">Parfums</button>
      <button class="filtre actif" data-filtre="tous">Promotions</button>
    </div>
  </div>
</div>
<section class="section-produits py-5">
  <div class="container">
    <h2 class="h4 text-center mb-4">Nos Produits</h2>
    <div class="row g-4" id="grilleProduits"></div>
  </div>
</section>

<section>
  <div class="produit-col col-12 col-sm-6 col-lg-3">
    <div class="card carte-produit h-100 border-1 shadow-sm">
      <div class="card-picture position-relative">
        <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="" class="card-img-top">
        <span class="badge badge-promo position-absolute top-0 start-0 m-2"></span>
      </div>
      <div class="card-body">
        <p class="brand mb-1">Marque</p>
        <h3 class="product-name h6 mb-2">Nom</h3>
        <div class="note small mb-3"><i class="fa-solid fa-star">Note</i></div>
        <div class="d-flex align-items-center justify-content-between"></div>
        <div>
          <span class="price">2euros</span>
          <span class="ancient-price ms-2"></span>
        </div>
        <button class="btn-bag" type="button" aria-label="Ajouter au panier">
          <i class="fa-solid fa-cart-shopping"></i>

        </button>

      </div>

    </div>

  </div>
</section>

</body>

</html>