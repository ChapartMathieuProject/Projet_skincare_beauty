<?php
include "public/includes/header.php";
?>



<section class="hero py-5">
  <div class="container py-4 text-center">
    <div class="mx-auto" style="max-width: 720px;">
      <h1 class="hero-title mb-3">Révélez votre beauté naturelle</h1>
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
  <section class="py-4 bg-white">
    <div class="container">
      <h2 class="flash-title h4 text-center mb-4">⚡ Promotions Flash</h2>

      <div class="overflow-hidden">
        <div class="flash-track">

          <!-- Série 1 -->
          <div class="flash-item">
            <div class="position-relative">
              <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="Produit en promotion">
              <span class="badge badge-flash position-absolute top-0 end-0 m-2">-30%</span>
            </div>
          </div>
          <div class="flash-item">
            <div class="position-relative">
              <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="Produit en promotion">
              <span class="badge badge-flash position-absolute top-0 end-0 m-2">-25%</span>
            </div>
          </div>
          <div class="flash-item">
            <div class="position-relative">
              <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="Produit en promotion">
              <span class="badge badge-flash position-absolute top-0 end-0 m-2">-20%</span>
            </div>
          </div>

          <!-- Série 2 : copie EXACTE de la série 1, pour la boucle continue -->
          <div class="flash-item">
            <div class="position-relative">
              <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="Produit en promotion">
              <span class="badge badge-flash position-absolute top-0 end-0 m-2">-30%</span>
            </div>
          </div>
          <div class="flash-item">
            <div class="position-relative">
              <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="Produit en promotion">
              <span class="badge badge-flash position-absolute top-0 end-0 m-2">-25%</span>
            </div>
          </div>
          <div class="flash-item">
            <div class="position-relative">
              <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="Produit en promotion">
              <span class="badge badge-flash position-absolute top-0 end-0 m-2">-20%</span>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
</section>
<div class="py-4 bg-white">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-center gap-2" id="filter">
      <button class="filtre actif" data-filter="tous">Tous</button>
      <button class="filtre actif" data-filter="tous">Sérums</button>
      <button class="filtre actif" data-filter="tous">Crèmes</button>
      <button class="filtre actif" data-filter="tous">Parfums</button>
      <button class="filtre actif" data-filter="tous">Promotions</button>
    </div>
  </div>
</div>
<section class="section-product py-5">
  <div class="container">
    <h2 class="h4 text-center mb-4">Nos Produits</h2>
    <div class="row g-4" id="grid-product"></div>
  </div>
</section>

<section class="section-products py-5">
  <div class="container">
    <div class="row g-4">

      <!-- UNE carte = UNE colonne -->
      <div class="product-col col-12 col-sm-6 col-lg-3">
        <div class="card card-product h-100 border-0 shadow-sm position-relative">
          <div class="card-picture position-relative">
            <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="Sérum C E Ferulic" class="card-img-top">
            <span class="badge badge-promo position-absolute top-0 start-0 m-2">-27%</span>
          </div>
          <div class="card-body">
            <p class="brand mb-1">SkinCeuticals</p>
            <h3 class="name-product h6 mb-2">
              <a href="product.php" class="stretched-link text-decoration-none text-reset">
                Sérum C E Ferulic
              </a>
            </h3>
            <div class="note small mb-3"><i class="fa-solid fa-star"></i> 4.8</div>

            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="price">39,90€</span>
                <span class="ancient-price ms-2">54,90€</span>
              </div>
              <button class="btn-bag position-relative" type="button" aria-label="Ajouter au panier">
                <i class="fa-solid fa-cart-shopping"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Carte 2 : on recopie tout le bloc .product-col et on change le contenu -->
      <div class="product-col col-12 col-sm-6 col-lg-3">
        <div class="card card-product h-100 border-0 shadow-sm position-relative">
          <div class="card-picture position-relative">
            <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="Sérum C E Ferulic" class="card-img-top">
            <span class="badge badge-promo position-absolute top-0 start-0 m-2">-27%</span>
          </div>
          <div class="card-body">
            <p class="brand mb-1">SkinCeuticals</p>
            <h3 class="name-product h6 mb-2">
              <a href="produit.html" class="stretched-link text-decoration-none text-reset">
                Sérum C E Ferulic
              </a>
            </h3>
            <div class="note small mb-3"><i class="fa-solid fa-star"></i> 4.8</div>

            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="price">39,90€</span>
                <span class="ancient-price ms-2">54,90€</span>
              </div>
              <button class="btn-bag position-relative" type="button" aria-label="Ajouter au panier">
                <i class="fa-solid fa-cart-shopping"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<?php include "public/includes/footer.php"; ?>