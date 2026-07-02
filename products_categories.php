<?php
require_once "public/includes/db.php";

$brands = [];
foreach ($pdo->query("SELECT brand_id, brand_name FROM brands") as $row) {
    $brands[$row["brand_id"]] = $row["brand_name"];
}

$product_types = [];
foreach ($pdo->query("SELECT product_type_id, product_type_name, product_type_slug FROM product_types ORDER BY product_type_name") as $row) {
    $product_types[$row["product_type_id"]] = [
        "name" => $row["product_type_name"],
        "slug" => $row["product_type_slug"],
    ];
}

$product_type_of = [];
foreach ($pdo->query("SELECT product_id, product_type_id FROM lien_product_type") as $row) {
    $product_type_of[$row["product_id"]] = $row["product_type_id"];
}

$promotions = [];
foreach ($pdo->query("SELECT product_id, promotion_percent FROM promotions WHERE promotion_is_active = 1") as $row) {
    $promotions[$row["product_id"]] = $row["promotion_percent"];
}

$pictures = [];
foreach ($pdo->query("SELECT product_id, picture_path FROM pictures") as $row) {
    if (!isset($pictures[$row["product_id"]])) {
        $pictures[$row["product_id"]] = $row["picture_path"];
    }
}

$default_image = 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg';


/* ============================================================
   AIGUILLAGE — quelle requête remplit la grille ?
   ============================================================ */

$cat    = isset($_GET["cat"])    ? trim($_GET["cat"])    : "";
$filter = isset($_GET["filter"]) ? trim($_GET["filter"]) : "";

$page_title = "Tous nos produits";
$products   = [];

if ($cat !== "") {
    $type_id_active = null;
    foreach ($product_types as $id => $type) {
        if ($type["slug"] === $cat) {
            $type_id_active = $id;
            $page_title     = $type["name"];
            break;
        }
    }

    if ($type_id_active !== null) {
        $stmt_ids = $pdo->prepare("SELECT product_id FROM lien_product_type WHERE product_type_id = ?");
        $stmt_ids->execute([$type_id_active]);
        $ids = $stmt_ids->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($ids)) {
            $placeholders = implode(",", array_fill(0, count($ids), "?"));
            $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($placeholders) AND product_is_status = 1 ORDER BY product_name");
            $stmt->execute($ids);
            $products = $stmt->fetchAll();
        }
    } else {
        $page_title = "Catégorie introuvable";
    }

} elseif ($filter === "promotions") {
    $page_title = "Promotions";
    $ids = array_keys($promotions);

    if (!empty($ids)) {
        $placeholders = implode(",", array_fill(0, count($ids), "?"));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($placeholders) AND product_is_status = 1 ORDER BY product_name");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();
    }

} elseif ($filter === "nouveautes") {
    $page_title = "Nouveautés";
    $products = $pdo->query("SELECT * FROM products WHERE product_is_status = 1 ORDER BY product_id DESC LIMIT 8")->fetchAll();

} else {
    $page_title = "Tous nos produits";
    $products = $pdo->query("SELECT * FROM products WHERE product_is_status = 1 ORDER BY product_name")->fetchAll();
}

include "public/includes/header.php";
?>


<div class="ariadne container small text-secondary pt-4 pb-1 d-flex gap-2">
  <a href="index.php">Accueil</a><span>></span>
  <span class="ariadne-product"><?= htmlspecialchars($page_title) ?></span>
</div>


<section class="section-products py-5">
  <div class="container">
    <h1 class="h3 text-center mb-4"><?= htmlspecialchars($page_title) ?></h1>

    <?php if (empty($products)): ?>
      <p class="text-center text-secondary py-5">Aucun produit à afficher pour le moment.</p>
    <?php else: ?>

    <div class="row g-4" id="grid-product">
      <?php foreach ($products as $p):
          $brand_name = $brands[$p['brand_id']] ?? 'Marque';
          $type_id   = $product_type_of[$p['product_id']] ?? null;
          $type_slug = $type_id !== null ? ($product_types[$type_id]['slug'] ?? '') : '';
          $promo_percent = $promotions[$p['product_id']] ?? null;
          $has_promo     = $promo_percent !== null;
          $img = $pictures[$p['product_id']] ?? $default_image;
          $coef_marge = 1 + ($p['product_margin'] / 100);
          $base_price = $p['product_buy_price'] * $coef_marge;

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
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="card-img-top">
            <?php if ($has_promo): ?>
              <span class="badge badge-promo position-absolute top-0 start-0 m-2">-<?= (int) $promo_percent ?>%</span>
            <?php endif; ?>
          </div>

          <div class="card-body">
            <p class="brand mb-1"><?= htmlspecialchars($brand_name) ?></p>
            <h3 class="name-product h6 mb-2">
              <a href="product.php?slug=<?= urlencode($p['product_slug']) ?>"
                 class="stretched-link text-decoration-none text-reset">
                <?= htmlspecialchars($p['product_name']) ?>
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


<?php include "public/includes/footer.php"; ?>