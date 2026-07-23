<?php
/** @var string $page_title */
/** @var Product[] $products */
/** @var Brand[] $brands */
/** @var ProductType[] $product_types */
/** @var array $product_type_of */
/** @var array $promotions */
/** @var array $pictures */

$resolveImage = function (?string $path): string {
    $default = 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg';
    return url('/' . ($path ?? $default));
};
?>
<?php require __DIR__ . '/../../../public/includes/header.php'; ?>

<div class="ariadne container small text-secondary pt-4 pb-1 d-flex gap-2">
  <a href="<?= url('/') ?>">Accueil</a><span>></span>
  <span class="ariadne-product"><?= htmlspecialchars($page_title) ?></span>
</div>

<section class="section-products py-5">
  <div class="container">
    <h1 class="h3 text-center mb-4"><?= htmlspecialchars($page_title) ?></h1>

    <?php if (empty($products)): ?>
      <p class="text-center text-secondary py-5">Aucun produit à afficher pour le moment.</p>
    <?php else: ?>

    <div class="row g-4" id="grid-product">
      <?php foreach ($products as $p): ?>
        <?php
          $brand      = $brands[$p->getBrandId()] ?? null;
          $brand_name = $brand !== null ? $brand->getName() : 'Marque';

          $type_id   = $product_type_of[$p->getId()] ?? null;
          $type      = ($type_id !== null) ? ($product_types[$type_id] ?? null) : null;
          $type_slug = $type !== null ? $type->getSlug() : '';

          $promo_percent = $promotions[$p->getId()] ?? null;
          $has_promo     = $promo_percent !== null;

          $img = $resolveImage($pictures[$p->getId()] ?? null);

          $base_price  = $p->getSellPrice();
          $final_price = $has_promo
              ? $base_price - ($base_price * ($promo_percent / 100))
              : $base_price;
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
                <a href="<?= url('/produit/' . $p->getSlug()) ?>"
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

    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/../../../public/includes/footer.php'; ?>