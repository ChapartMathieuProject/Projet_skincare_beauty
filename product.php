<?php
require_once "public/includes/db.php";

// Instanciation des DAO
$productDAO     = new ProductDAO($pdo);
$brandDAO       = new BrandDAO($pdo);
$productTypeDAO = new ProductTypeDAO($pdo);

// 1. CHARGEMENT DES DICTIONNAIRES
// Entités (objets) via les DAO :
$brands        = $brandDAO->findAllKeyedById();        // [brand_id => Brand]
$product_types = $productTypeDAO->findAllKeyedById();  // [product_type_id => ProductType]

// Tables de liaison / attributs : simples maps clé => valeur, pas d'entité nécessaire
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

$default_image = 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg';

// 2. RÉCUPÉRATION DU PRODUIT PRINCIPAL (via le DAO)
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$product = ($slug !== '') ? $productDAO->findBySlug($slug) : null;

include "public/includes/header.php";

if ($product === null) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Ce produit n'existe pas ou n'est plus disponible.</div></div>";
    include "public/includes/footer.php";
    exit;
}

// 3. TRAITEMENT DES INFOS DU PRODUIT PRINCIPAL
$current_id = $product->getId();

// $brands contient des objets Brand : on récupère l'objet, puis son nom
$brand      = $brands[$product->getBrandId()] ?? null;
$brand_name = $brand !== null ? $brand->getName() : 'Marque';

// Idem pour le type de produit
$type_id   = $product_type_of[$current_id] ?? null;
$type      = ($type_id !== null) ? ($product_types[$type_id] ?? null) : null;
$type_name = $type !== null ? $type->getName() : 'Catégorie';

$promo_percent = $promotions[$current_id] ?? null;
$has_promo     = $promo_percent !== null;
$img_main      = $pictures[$current_id] ?? $default_image;

// Le prix de vente vient de l'entité (getSellPrice = buy_price × marge)
$base_price = $product->getSellPrice();

$final_price = $base_price;
$saving = 0;

if ($has_promo) {
    $reduction = $base_price * ($promo_percent / 100);
    $final_price = $base_price - $reduction;
    $saving = $reduction;
}

// 4. RÉCUPÉRATION DES PRODUITS SIMILAIRES (objets Product)
$all_products = $productDAO->findAllActive();

$similar_products = [];
foreach ($all_products as $p) {
    if ($p->getId() !== $current_id
        && isset($product_type_of[$p->getId()])
        && $product_type_of[$p->getId()] === $type_id) {
        $similar_products[] = $p;
    }
}
shuffle($similar_products);
$similar_products = array_slice($similar_products, 0, 4);
?>

<!-- product.php -->
<div class="ariadne container small text-secondary pt-4 pb-1 d-flex gap-2">
    <a href="index.php">Accueil</a><span>></span>
    <a href="#"><?= htmlspecialchars($type_name) ?></a><span>></span>
    <span class="ariadne-product"><?= htmlspecialchars($product->getName()) ?></span>
</div>

<section class="container py-4">
    <div class="row g-5 align-items-start">
        <div class="col-12 col-lg-6">
            <div class="gallery-sticky">
                <div class="img-main position-relative d-flex align-items-end justify-content-center">
                    <?php if ($has_promo): ?>
                        <span class="badge-reduction position-absolute top-0 start-0 m-3">-<?= (int)$promo_percent ?>%</span>
                    <?php endif; ?>

                    <button id="btn-wishlist" type="button" aria-label="Ajouter à la wishlist"
                        class="btn position-absolute top-0 end-0 m-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="test fa-reg-he fa-regular fa-heart"></i>
                    </button>

                    <img src="<?= htmlspecialchars($img_main) ?>" alt="<?= htmlspecialchars($product->getName()) ?>">
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
            <p class="overtitle-brand mb-2"><?= htmlspecialchars($brand_name) ?></p>
            <h1 class="title-product h2 mb-3"><?= htmlspecialchars($product->getName()) ?></h1>

            <ul class="atouts-paper list-unstyled d-flex flex-column gap-2 mb-4">
                <li class="d-flex gap-2"><span class="puce">✦</span>Formule haute efficacité</li>
                <li class="d-flex gap-2"><span class="puce">✦</span>Composition : <?= htmlspecialchars($product->getComposition()) ?></li>
            </ul>

            <div class="d-flex align-items-baseline gap-3 mb-1">
                <span class="actual-price" id="actual-price"><?= number_format($final_price, 2, ',', ' ') ?>€</span>
                <?php if ($has_promo): ?>
                    <span class="strike-price fs-5" id="strike-price"><?= number_format($base_price, 2, ',', ' ') ?>€</span>
                    <span class="promotion-badge-reduction badge-product rounded px-2 py-1" id="promotion-badge">-<?= (int)$promo_percent ?>%</span>
                <?php endif; ?>
            </div>

            <?php if ($has_promo): ?>
                <p class="saving small mb-4" id="saving">Vous économisez <?= number_format($saving, 2, ',', ' ') ?>€</p>
            <?php endif; ?>

            <p class="small fw-semibold mb-2">CONTENANCE</p>
            <div class="d-flex gap-3 mb-4">
                <button class="variante active p-3" data-price="<?= $final_price ?>" data-old="<?= $base_price ?>">
                    <span class="nom d-block">Standard</span>
                    <span class="sous d-block">EAN: <?= htmlspecialchars($product->getEan()) ?></span>
                </button>
            </div>

            <div class="d-flex gap-3 mb-3">
                <div class="choice-quantity d-flex align-items-center">
                    <button type="button" id="qty-less" aria-label="Diminuer">-</button>
                    <span id="qty" class="text-center fw-semibold">1</span>
                    <button type="button" id="qty-more" aria-label="Augmenter">+</button>
                </div>
                <button class="btn-rose btn-add flex-fill d-flex align-items-center justify-content-center gap-2"
                    id="btn-add"
                    type="button"
                    data-id="<?= $product['product_id'] ?>"
                    data-name="<?= htmlspecialchars($product['product_name']) ?>"
                    data-price="<?= $final_price ?>"
                    data-image="<?= htmlspecialchars($img_main) ?>">
                    <i class="fa-solid fa-bag-shopping"></i> Ajouter au panier
                </button>
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

<section class="container pb-5">
    <div class="d-flex gap-1 border-bottom mb-4 overflow-auto" id="tab-bar">
        <button class="tab active" data-target="desc">Description</button>
        <button class="tab" data-target="ingr">Composition</button>
    </div>
    <div class="tab-contents">
        <div data-sign="desc">
            <p class="mb-3"><?= nl2br(htmlspecialchars($product->getDescription())) ?></p>
        </div>
        <div data-sign="ingr" class="d-none">
            <p class="fw-medium mb-3">Formulation du produit</p>
            <p class="small text-muted mb-0"><?= htmlspecialchars($product->getComposition()) ?></p>
        </div>
    </div>
</section>

<?php if (!empty($similar_products)): ?>
    <section class="container py-5">
        <h2 class="h3 text-center mb-4">Vous aimerez aussi</h2>
        <div class="row g-4">
            <?php foreach ($similar_products as $sp):
                $brand_sp      = $brands[$sp->getBrandId()] ?? null;
                $brand_name_sp = $brand_sp !== null ? $brand_sp->getName() : 'Marque';

                $promo_percent_sp = $promotions[$sp->getId()] ?? null;
                $has_promo_sp     = $promo_percent_sp !== null;
                $img_sp           = $pictures[$sp->getId()] ?? $default_image;

                $base_price_sp = $sp->getSellPrice();

                $final_price_sp = $base_price_sp;
                if ($has_promo_sp) {
                    $final_price_sp = $base_price_sp - ($base_price_sp * ($promo_percent_sp / 100));
                }
            ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-bind h-100 d-flex flex-column">
                        <div class="visual position-relative">
                            <a href="product.php?slug=<?= urlencode($sp->getSlug()) ?>">
                                <img src="<?= htmlspecialchars($img_sp) ?>" alt="<?= htmlspecialchars($sp->getName()) ?>">
                            </a>
                            <?php if ($has_promo_sp): ?>
                                <span class="badge-reduction position-absolute top-0 start-0 m-2">-<?= (int)$promo_percent_sp ?>%</span>
                            <?php endif; ?>
                        </div>

                        <div class="p-3 d-flex flex-column flex-fill">
                            <span class="brand"><?= htmlspecialchars($brand_name_sp) ?></span>
                            <a href="product.php?slug=<?= urlencode($sp->getSlug()) ?>" class="text-decoration-none text-dark">
                                <span class="name my-1 fw-semibold d-block"><?= htmlspecialchars($sp->getName()) ?></span>
                            </a>

                            <div class="mt-auto d-flex align-items-center justify-content-between pt-2">
                                <span>
                                    <span class="price-prom-badge fw-bold"><?= number_format($final_price_sp, 2, ',', ' ') ?> €</span>
                                    <?php if ($has_promo_sp): ?>
                                        <span class="strike-price small ms-1 text-muted text-decoration-line-through"><?= number_format($base_price_sp, 2, ',', ' ') ?> €</span>
                                    <?php endif; ?>
                                </span>

                                <button class="btn-rose btn-bag rounded-circle d-flex align-items-center justify-content-center" aria-label="Ajouter au panier">
                                    <i class="fa-solid fa-bag-shopping"></i>

                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>


<script src="public/scripts/cart-manager.js"></script>
<script src="public/scripts/product.js"></script>

<?php include "public/includes/footer.php"; ?>