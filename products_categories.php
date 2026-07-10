<?php
require_once "public/includes/db.php";

// Instanciation des DAO
$productDAO     = new ProductDAO($pdo);
$brandDAO       = new BrandDAO($pdo);
$productTypeDAO = new ProductTypeDAO($pdo);

// Entités (objets) via les DAO
$brands        = $brandDAO->findAllKeyedById(); 
$product_types = $productTypeDAO->findAllKeyedById(); 

// Tables de liaison / attributs : simples maps clé => valeur
$product_type_of = [];
foreach ($pdo->query("SELECT product_id, product_type_id FROM lien_product_type") as $row) {
    $product_type_of[(int) $row["product_id"]] = (int) $row["product_type_id"];
}

$promotions = [];
foreach ($pdo->query("SELECT product_id, promotion_percent FROM promotions WHERE promotion_is_active = 1") as $row) {
    $promotions[(int) $row["product_id"]] = (int) $row["promotion_percent"];
}

$pictures = [];
foreach ($pdo->query("SELECT product_id, picture_path FROM pictures") as $row) {
    if (!isset($pictures[(int) $row["product_id"]])) {
        $pictures[(int) $row["product_id"]] = $row["picture_path"];
    }
}

$default_image = 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg';


  //  AIGUILLAGE — quelle méthode du DAO remplit la grille ?

$cat    = isset($_GET["cat"])    ? trim($_GET["cat"])    : "";
$filter = isset($_GET["filter"]) ? trim($_GET["filter"]) : "";

$page_title = "Tous nos produits";
$products   = [];

if ($cat !== "") {
    // On cherche le type dont le slug correspond au paramètre ?cat=
    $type_id_active = null;
    foreach ($product_types as $id => $type) {
        if ($type->getSlug() === $cat) {
            $type_id_active = $id;
            $page_title     = $type->getName();
            break;
        }
    }

    if ($type_id_active !== null) {
        // Plus besoin d'interroger lien_product_type : le dictionnaire est déjà chargé.
        // On garde les product_id dont le type correspond.
        $ids = [];
        foreach ($product_type_of as $product_id => $type_id) {
            if ($type_id === $type_id_active) {
                $ids[] = $product_id;
            }
        }
        $products = $productDAO->findByIds($ids);
    } else {
        $page_title = "Catégorie introuvable";
    }

} elseif ($filter === "promotions") {
    $page_title = "Promotions";
    $products   = $productDAO->findByIds(array_keys($promotions));

} elseif ($filter === "nouveautes") {
    $page_title = "Nouveautés";
    $products   = $productDAO->findNewest(8);

} else {
    $page_title = "Tous nos produits";
    $products   = $productDAO->findAllActiveOrderedByName();
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
          // $brands contient des objets Brand
          $brand      = $brands[$p->getBrandId()] ?? null;
          $brand_name = $brand !== null ? $brand->getName() : 'Marque';

          // $product_types contient des objets ProductType
          $type_id   = $product_type_of[$p->getId()] ?? null;
          $type      = ($type_id !== null) ? ($product_types[$type_id] ?? null) : null;
          $type_slug = $type !== null ? $type->getSlug() : '';

          $promo_percent = $promotions[$p->getId()] ?? null;
          $has_promo     = $promo_percent !== null;

          $img = $pictures[$p->getId()] ?? $default_image;

          // Le prix de vente vient de l'entité (buy_price × marge)
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

    <?php endif; ?>
  </div>
</section>


<?php include "public/includes/footer.php"; ?>