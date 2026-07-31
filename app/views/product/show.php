<?php

/** @var Product $product */
/** @var Product[] $similar_products */
/** @var Brand[] $brands */
/** @var ProductType[] $product_types */
/** @var array $product_type_of */
/** @var array $promotions */
/** @var array $pictures */
/** @var array $gallery */

$resolveImage = function (?string $path): string {
  $default = 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg';
  return url('/' . ($path ?? $default));
};

$current_id = $product->getId();

$gallery      = $gallery ?? [];
$gallery_urls = array_map($resolveImage, $gallery);

$brand      = $brands[$product->getBrandId()] ?? null;
$brand_name = $brand !== null ? $brand->getName() : 'Marque';

$type_id   = $product_type_of[$current_id] ?? null;
$type      = ($type_id !== null) ? ($product_types[$type_id] ?? null) : null;
$type_name = $type !== null ? $type->getName() : 'Catégorie';
$type_slug = $type !== null ? $type->getSlug() : '';

$promo_percent = $promotions[$current_id] ?? null;
$has_promo     = $promo_percent !== null;

$img_main = $gallery_urls[0] ?? $resolveImage($pictures[$current_id] ?? null);

$base_price  = $product->getSellPrice();
$final_price = $base_price;
$saving      = 0;
if ($has_promo) {
  $saving      = $base_price * ($promo_percent / 100);
  $final_price = $base_price - $saving;
}
?>
<?php require __DIR__ . '/../../../public/includes/header.php'; ?>

<div class="ariadne container small text-secondary pt-4 pb-1 d-flex gap-2">
  <a href="<?= url('/') ?>">Accueil</a><span>></span>
  <a href="<?= url('/produits?cat=' . urlencode($type_slug)) ?>"><?= htmlspecialchars($type_name) ?></a><span>></span>
  <span class="ariadne-product"><?= htmlspecialchars($product->getName()) ?></span>
</div>

<section class="container py-4">
  <div class="row g-5 align-items-start">

    <div class="col-12 col-lg-6">
      <div class="gallery-sticky">
        <div class="img-main position-relative d-flex align-items-end justify-content-center">
          <?php if ($has_promo): ?>
            <span class="badge-reduction position-absolute top-0 start-0 m-3">-<?= (int) $promo_percent ?>%</span>
          <?php endif; ?>

          <button id="btn-wishlist" type="button" aria-label="Ajouter à la wishlist"
            class="btn position-absolute top-0 end-0 m-3 rounded-circle d-flex align-items-center justify-content-center">
            <i class="test fa-reg-he fa-regular fa-heart"></i>
          </button>

          <img src="<?= htmlspecialchars($img_main) ?>" alt="<?= htmlspecialchars($product->getName()) ?>">
        </div>

        <?php if (!empty($gallery_urls)): ?>
          <div class="d-flex gap-2 mt-3">
            <?php foreach ($gallery_urls as $i => $thumb): ?>
              <button type="button"
                class="vignette <?= $i === 0 ? 'active' : '' ?>"
                data-full="<?= htmlspecialchars($thumb) ?>"
                style="background-image:url('<?= htmlspecialchars($thumb) ?>');background-size:cover;background-position:center;">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-12 col-lg-6">

      <p class="overtitle-brand mb-2"><?= htmlspecialchars($brand_name) ?></p>
      <h1 class="title-product h2 mb-3"><?= htmlspecialchars($product->getName()) ?></h1>

      <ul class="atouts-paper list-unstyled d-flex flex-column gap-2 mb-4">
        <li class="d-flex gap-2"><span class="puce">✦</span>Formule haute efficacité</li>
        <li class="d-flex gap-2"><span class="puce">✦</span>Composition : <?= htmlspecialchars($product->getComposition() ?? '') ?></li>
      </ul>

      <div class="d-flex align-items-baseline gap-3 mb-1">
        <span class="actual-price" id="actual-price"><?= number_format($final_price, 2, ',', ' ') ?>€</span>
        <?php if ($has_promo): ?>
          <span class="strike-price fs-5" id="strike-price"><?= number_format($base_price, 2, ',', ' ') ?>€</span>
          <span class="promotion-badge-reduction badge-product rounded px-2 py-1" id="promotion-badge">-<?= (int) $promo_percent ?>%</span>
        <?php endif; ?>
      </div>

      <?php if ($has_promo): ?>
        <p class="saving small mb-4" id="saving">Vous économisez <?= number_format($saving, 2, ',', ' ') ?>€</p>
      <?php endif; ?>

      <p class="small fw-semibold mb-2">CONTENANCE</p>
      <div class="d-flex gap-3 mb-4">
        <button class="variante active p-3" data-price="<?= $final_price ?>" data-old="<?= $base_price ?>">
          <span class="nom d-block">Standard</span>
          <span class="sous d-block">EAN: <?= htmlspecialchars($product->getEan() ?? '') ?></span>
        </button>
      </div>

      <div class="d-flex gap-3 mb-3">
        <form method="post" action="<?= url('/panier_action.php') ?>" class="cart-form js-cart d-flex gap-3 mb-3 flex-fill">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= (int) $product->getId() ?>">
          <input type="hidden" name="quantity" id="qty-input" value="1">
          <input type="hidden" name="redirect" value="/produit/<?= htmlspecialchars($product->getSlug()) ?>">

          <div class="choice-quantity d-flex align-items-center">
            <button type="button" id="qty-less" aria-label="Diminuer">-</button>
            <span id="qty" class="text-center fw-semibold">1</span>
            <button type="button" id="qty-more" aria-label="Augmenter">+</button>
          </div>

          <button class="btn-rose btn-add flex-fill d-flex align-items-center justify-content-center gap-2" type="submit" id="btn-add">
            <i class="fa-solid fa-bag-shopping"></i> Ajouter au panier
          </button>
        </form>
      </div>

      <div class="d-flex align-items-center gap-2 mb-4 small text-secondary">
        <span class="spot-stock" style="background-color: <?= $product->getQuantity() > 0 ? '#28a745' : '#dc3545' ?>;"></span>
        <span>
          <?php if ($product->getQuantity() > 0): ?>
            En stock (<?= $product->getQuantity() ?> disponibles) - Expédié sous 48h
          <?php else: ?>
            Rupture de stock
          <?php endif; ?>
        </span>
      </div>

      <div class="reassurance row g-3 py-4">
        <div class="col-6 d-flex align-items-center gap-2 small"><i class="fa-solid fa-leaf"></i> 100% Naturel</div>
        <div class="col-6 d-flex align-items-center gap-2 small"><i class="fa-solid fa-heart"></i> Cruelty-free</div>
        <div class="col-6 d-flex align-items-center gap-2 small"><i class="fa-solid fa-truck"></i> Livraison 48h</div>
        <div class="col-6 d-flex align-items-center gap-2 small"><i class="fa-solid fa-rotate-left"></i> Retours 30 jours</div>
      </div>

    </div>
  </div>
</section>

<!-- Onglets -->
<section class="container pb-5">
  <div class="d-flex gap-1 border-bottom mb-4 overflow-auto" id="tab-bar">
    <button class="tab active" data-target="desc">Description</button>
    <button class="tab" data-target="ingr">Composition</button>
  </div>
  <div class="tab-contents">
    <div data-sign="desc">
      <p class="mb-3"><?= nl2br(htmlspecialchars($product->getDescription() ?? '')) ?></p>
    </div>
    <div data-sign="ingr" class="d-none">
      <p class="fw-medium mb-3">Formulation du produit</p>
      <p class="small text-muted mb-0"><?= htmlspecialchars($product->getComposition() ?? '') ?></p>
    </div>
  </div>
</section>

<?php if (!empty($similar_products)): ?>
  <section class="container py-5">
    <h2 class="h3 text-center mb-4">Vous aimerez aussi</h2>
    <div class="row g-4">
      <?php foreach ($similar_products as $sp): ?>
        <?php
        $brand_sp      = $brands[$sp->getBrandId()] ?? null;
        $brand_name_sp = $brand_sp !== null ? $brand_sp->getName() : 'Marque';

        $promo_percent_sp = $promotions[$sp->getId()] ?? null;
        $has_promo_sp     = $promo_percent_sp !== null;
        $img_sp           = $resolveImage($pictures[$sp->getId()] ?? null);

        $base_price_sp  = $sp->getSellPrice();
        $final_price_sp = $has_promo_sp
          ? $base_price_sp - ($base_price_sp * ($promo_percent_sp / 100))
          : $base_price_sp;
        ?>
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card-bind h-100 d-flex flex-column">
            <div class="visual position-relative">
              <a href="<?= url('/produit/' . $sp->getSlug()) ?>">
                <img src="<?= htmlspecialchars($img_sp) ?>" alt="<?= htmlspecialchars($sp->getName()) ?>">
              </a>
              <?php if ($has_promo_sp): ?>
                <span class="badge-reduction position-absolute top-0 start-0 m-2">-<?= (int) $promo_percent_sp ?>%</span>
              <?php endif; ?>
            </div>

            <div class="p-3 d-flex flex-column flex-fill">
              <span class="brand"><?= htmlspecialchars($brand_name_sp) ?></span>
              <a href="<?= url('/produit/' . $sp->getSlug()) ?>" class="text-decoration-none text-dark">
                <span class="name my-1 fw-semibold d-block"><?= htmlspecialchars($sp->getName()) ?></span>
              </a>

              <div class="mt-auto d-flex align-items-center justify-content-between pt-2">
                <span>
                  <span class="price-prom-badge fw-bold"><?= number_format($final_price_sp, 2, ',', ' ') ?> €</span>
                  <?php if ($has_promo_sp): ?>
                    <span class="strike-price small ms-1 text-muted text-decoration-line-through"><?= number_format($base_price_sp, 2, ',', ' ') ?> €</span>
                  <?php endif; ?>
                </span>

                <form method="post" action="<?= url('/panier_action.php') ?>" class="js-cart">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="id" value="<?= (int) $sp->getId() ?>">
                  <input type="hidden" name="quantity" value="1">
                  <input type="hidden" name="redirect" value="/produit/<?= htmlspecialchars($product->getSlug()) ?>">
                  <button class="btn-rose btn-bag rounded-circle d-flex align-items-center justify-content-center"
                    type="submit" aria-label="Ajouter au panier">
                    <i class="fa-solid fa-bag-shopping"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<script src="<?= url('/public/scripts/product.js') ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const mainImg   = document.querySelector('.img-main img');
  const vignettes = document.querySelectorAll('.vignette[data-full]');
  if (!mainImg) return;

  vignettes.forEach(v => {
    v.addEventListener('click', () => {
      mainImg.src = v.dataset.full;
      vignettes.forEach(x => x.classList.remove('active'));
      v.classList.add('active');
    });
  });
});
</script>

<?php require __DIR__ . '/../../../public/includes/footer.php'; ?>