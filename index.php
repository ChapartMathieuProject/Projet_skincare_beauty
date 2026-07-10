<?php

require_once "public/includes/db.php";

// Instanciation des DAO
$productDAO     = new ProductDAO($pdo);
$brandDAO       = new BrandDAO($pdo);
$productTypeDAO = new ProductTypeDAO($pdo);

// Entités (objets) via les DAO
$brands        = $brandDAO->findAllKeyedById();        // [brand_id => Brand]
$product_types = $productTypeDAO->findAllKeyedById();  // [product_type_id => ProductType]

// Tables de liaison / attributs : simples maps clé => valeur
$product_type_of = [];
foreach ($pdo->query("SELECT product_id, product_type_id FROM lien_product_type") as $row) {
  $product_type_of[(int) $row["product_id"]] = (int) $row["product_type_id"];
}

$promotions = [];
foreach ($pdo->query("SELECT product_id, promotion_percent FROM promotions WHERE promotion_is_active = 1") as $row) {
  $promotions[(int) $row['product_id']] = (int) $row['promotion_percent'];
}

$pictures = [];
foreach ($pdo->query("SELECT product_id, picture_path FROM pictures") as $row) {
  if (!isset($pictures[(int) $row["product_id"]])) {
    $pictures[(int) $row["product_id"]] = $row["picture_path"];
  }
}

// Produits actifs, triés par nom (objets Product)
$products = $productDAO->findAllActiveOrderedByName();

// Image par défaut à enlever à terme ***
$default_image = 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg';


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

<?php
// Produits en promotion (objets Product)
$flash_products = [];
foreach ($products as $p) {
  if (isset($promotions[$p->getId()])) {
    $flash_products[] = $p;
  }
}

// Tri décroissant par pourcentage de promo
usort($flash_products, function ($a, $b) use ($promotions) {
  return $promotions[$b->getId()] <=> $promotions[$a->getId()];
});
?>

<?php if (!empty($flash_products)): ?>
  <section>
    <!-- Caroussel -->
    <section class="py-4 bg-white">
      <div class="container">
        <h2 class="flash-title h4 text-center mb-4">⚡ Promotions Flash</h2>

        <div class="overflow-hidden">
          <div class="flash-track">
            <?php
            for ($pass = 0; $pass < 2; $pass++):
              foreach ($flash_products as $fp):
                $img_flash = $pictures[$fp->getId()] ?? $default_image;
            ?>

                <div class="flash-item" <?= $pass === 1 ? ' aria-hidden="true"' : '' ?>>
                  <div class="position-relative">
                    <a href="product.php?slug=<?= urlencode($fp->getSlug()) ?>">
                      <img src="<?= htmlspecialchars($img_flash) ?>" alt="<?= htmlspecialchars($fp->getName()) ?>">
                    </a>
                    <span class="badge badge-flash position-absolute top-0 end-0 m-2">-<?= (int) $promotions[$fp->getId()] ?>%</span>
                  </div>
                </div>

            <?php
              endforeach;
            endfor;
            ?>
          </div>
        </div>
      </div>
    </section>
  </section>
<?php endif; ?>


<div class="py-4 bg-white">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-center gap-2" id="filter">
      <button class="filtre actif" data-filter="tous">Tous</button>
      <?php foreach ($product_types as $type): ?>
        <!-- $product_types contient des objets ProductType -->
        <button class="filtre" data-filter="<?= htmlspecialchars($type->getSlug()) ?>">
          <?= htmlspecialchars($type->getName()) ?>
        </button>
      <?php endforeach; ?>
      <button class="filtre" data-filter="promo">Promotions</button>
    </div>
  </div>
</div>



<section class="section-products py-5">
  <div class="container">
    <h2 class="h4 text-center mb-4">Nos Produits</h2>

    <div class="row g-4" id="grid-product">
      <?php foreach ($products as $p):

        /* On relie les données EN PHP grâce aux tableaux chargés en haut */
        $brand      = $brands[$p->getBrandId()] ?? null;
        $brand_name = $brand !== null ? $brand->getName() : 'Marque';

        $type_id   = $product_type_of[$p->getId()] ?? null;
        $type      = ($type_id !== null) ? ($product_types[$type_id] ?? null) : null;
        $type_slug = $type !== null ? $type->getSlug() : '';

        $promo_percent = $promotions[$p->getId()] ?? null;
        $has_promo     = $promo_percent !== null;

        $img = $pictures[$p->getId()] ?? $default_image;

        /* Le prix de vente vient de l'entité (buy_price × marge) */
        $base_price = $p->getSellPrice();

        $final_price = $base_price;
        if ($has_promo) {
          $final_price = $base_price - ($base_price * ($promo_percent / 100));
        }
      ?>
        <div class="product-col col-12 col-sm-6 col-lg-3"
          data-type="<?= htmlspecialchars($type_slug) ?>"
          data-promo="<?= $has_promo ? '1' : '0' ?>">

          <div class="card card-product h-100 border-0 shadow-sm position-relative">
            <div class="card-picture position-relative">
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p->getName()) ?>" class="card-img-top">
              <?php if ($has_promo): ?>
                <span class="badge badge-promo position-absolute top-0 start-0 m-2">-<?= (int) $promo_percent ?>%</span>
              <?php endif; ?>
            </div>

            <div class="card-body">
              <p class="brand mb-1"><?= htmlspecialchars($brand_name) ?></p>
              <h3 class="name-product h6 mb-2">
                <a href="product.php?slug=<?= urlencode($p->getSlug()) ?>"
                  class="stretched-link text-decoration-none text-reset">
                  <?= htmlspecialchars($p->getName()) ?>
                </a>
              </h3>

              <div class="d-flex align-items-center justify-content-between mt-3">
                <div>
                  <span class="price"><?= number_format($final_price, 2, ',', ' ') ?>€</span>
                  <?php if ($has_promo): ?>
                    <span class="ancient-price ms-2"><?= number_format($base_price, 2, ',', ' ') ?>€</span>
                  <?php endif; ?>
                </div>
                <button class="btn-bag position-relative" type="button" aria-label="Ajouter au panier">
                  <i class="fa-solid fa-cart-shopping"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<script src="public/scripts/index.js"></script>
<?php include "public/includes/footer.php"; ?>