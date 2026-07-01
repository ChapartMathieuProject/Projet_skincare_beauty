<?php
require_once "public/includes/db.php"; 
include "public/includes/header.php";

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug === '') {
    echo "<div class='container py-5'><div class='alert alert-danger'>Produit introuvable (aucun identifiant spécifié).</div></div>";
    include "public/includes/footer.php";
    exit;
}

$query = "
    SELECT p.*, b.brand_name, pt.product_type_name, pro.promotion_percent 
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN lien_product_type lpt ON p.product_id = lpt.product_id
    LEFT JOIN product_types pt ON lpt.product_type_id = pt.product_type_id
    LEFT JOIN promotions pro ON p.product_id = pro.product_id AND pro.promotion_is_active = 1
    WHERE p.product_slug = :slug
";

$stmt = $pdo->prepare($query);
$stmt->execute(['slug' => $slug]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Ce produit n'existe pas ou n'est plus disponible.</div></div>";
    include "public/includes/footer.php";
    exit;
}

$coefficient_marge = 1 + ($product['product_margin'] / 100);
$base_price = $product['product_buy_price'] * $coefficient_marge;

$has_promo = !empty($product['promotion_percent']);
$final_price = $base_price;
$saving = 0;

if ($has_promo) {
    $reduction = $base_price * ($product['promotion_percent'] / 100);
    $final_price = $base_price - $reduction;
    $saving = $reduction;
}


$query_similar = "
    SELECT DISTINCT p.*, b.brand_name, pro.promotion_percent
    FROM products p
    INNER JOIN lien_product_type lpt ON p.product_id = lpt.product_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN promotions pro ON p.product_id = pro.product_id AND pro.promotion_is_active = 1
    WHERE lpt.product_type_id = (
        SELECT product_type_id FROM lien_product_type WHERE product_id = ? LIMIT 1
    )
    AND p.product_id != ?
    AND p.product_is_status = 1
    ORDER BY RAND()
    LIMIT 4
";

$stmt_similar = $pdo->prepare($query_similar);
$stmt_similar->execute([$product["product_id"], $product["product_id"]]);
$similar_product = $stmt_similar->fetchAll();
?>

<div class="ariadne container small text-secondary pt-4 pb-1 d-flex gap-2">
    <a href="index.php">Accueil</a><span>></span>
    <a href="#"><?= htmlspecialchars($product['product_type_name'] ?? 'Catégorie') ?></a><span>></span>
    <span class="ariadne-product"><?= htmlspecialchars($product['product_name']) ?></span>
</div>

<section class="container py-4">
    <div class="row g-5 align-items-start">
        <div class="col-12 col-lg-6">
            <div class="gallery-sticky">
                <div class="img-main position-relative d-flex align-items-end justify-content-center">
                    <?php if ($has_promo): ?>
                        <span class="badge-reduction position-absolute top-0 start-0 m-3">-<?= $product['promotion_percent'] ?>%</span>
                    <?php endif; ?>
                    
                    <button id="btn-wishlist" type="button" aria-label="Ajouter à la wishlist"
                        class="btn position-absolute top-0 end-0 m-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="test fa-reg-he fa-regular fa-heart"></i>
                    </button>
                    
                    <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="<?= htmlspecialchars($product['product_name']) ?>">
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
            <p class="overtitle-brand mb-2"><?= htmlspecialchars($product['brand_name'] ?? 'Marque') ?></p>
            <h1 class="title-product h2 mb-3"><?= htmlspecialchars($product['product_name']) ?></h1>
            
            <ul class="atouts-paper list-unstyled d-flex flex-column gap-2 mb-4">
                <li class="d-flex gap-2"><span class="puce">✦</span>Formule haute efficacité</li>
                <li class="d-flex gap-2"><span class="puce">✦</span>Composition : <?= htmlspecialchars($product['product_composition']) ?></li>
            </ul>
            
            <div class="d-flex align-items-baseline gap-3 mb-1">
                <span class="actual-price" id="actual-price"><?= number_format($final_price, 2, ',', ' ') ?>€</span>
                <?php if ($has_promo): ?>
                    <span class="strike-price fs-5" id="strike-price"><?= number_format($base_price, 2, ',', ' ') ?>€</span>
                    <span class="promotion-badge-reduction badge-product rounded px-2 py-1" id="promotion-badge">-<?= $product['promotion_percent'] ?>%</span>
                <?php endif; ?>
            </div>
            
            <?php if ($has_promo): ?>
                <p class="saving small mb-4" id="saving">Vous économisez <?= number_format($saving, 2, ',', ' ') ?>€</p>
            <?php endif; ?>

            <p class="small fw-semibold mb-2">CONTENANCE</p>
            <div class="d-flex gap-3 mb-4">
                <button class="variante active p-3" data-price="<?= $final_price ?>" data-old="<?= $base_price ?>">
                    <span class="nom d-block">Standard</span>
                    <span class="sous d-block">EAN: <?= htmlspecialchars($product['product_ean']) ?></span>
                </button>
            </div>
            
            <div class="d-flex gap-3 mb-3">
                <div class="choice-quantity d-flex align-items-center">
                    <button type="button" id="qty-less" aria-label="Diminuer">-</button>
                    <span id="qty" class="text-center fw-semibold">1</span>
                    <button type="button" id="qty-more" aria-label="Augmenter">+</button>
                </div>
                <button class="btn-rose btn-add flex-fill d-flex align-items-center justify-content-center gap-2"
                    id="btn-add" type="button">
                    <i class="fa-solid fa-bag-shopping"></i> Ajouter au panier
                </button>
            </div>
            
            <div class="d-flex align-items-center gap-2 mb-4 small text-secondary">
                <span class="spot-stock" style="background-color: <?= $product['product_quantity'] > 0 ? '#28a745' : '#dc3545' ?>;"></span>
                <span>
                    <?php if ($product['product_quantity'] > 0): ?>
                        En stock (<?= $product['product_quantity'] ?> disponibles) - Expédié sous 48h
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
            <p class="mb-3"><?= nl2br(htmlspecialchars($product['product_description'])) ?></p>
        </div>
        <div data-sign="ingr" class="d-none">
            <p class="fw-medium mb-3">Formulation du produit</p>
            <p class="small text-muted mb-0"><?= htmlspecialchars($product['product_composition']) ?></p>
        </div>
    </div>
</section>


<?php if (!empty($similar_product)): ?>
<section class="container py-5">
    <h2 class="h3 text-center mb-4">Vous aimerez aussi</h2>
    <div class="row g-4">
        <?php foreach($similar_product as $sp):
            // CORRECTION : $stp -> $sp et ajout du $ manquant devant coef_marge_sp
            $coef_marge_sp = 1 + ($sp["product_margin"] / 100);
            $base_price_sp = $sp["product_buy_price"] * $coef_marge_sp;

            $has_promo_sp = !empty($sp["promotion_percent"]);
            $final_price_sp = $base_price_sp;

            if ($has_promo_sp) {
                $reduction_sp = $base_price_sp * ($sp["promotion_percent"] / 100);
                $final_price_sp = $base_price_sp - $reduction_sp;
            }
        ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card-bind h-100 d-flex flex-column">
                <div class="visual position-relative">
                    <a href="product.php?slug=<?= urlencode($sp["product_slug"]) ?>">
                        <img src="images/_C-E-Ferulic-30ml_SkinCeuticals.jpg" alt="<?= htmlspecialchars($sp["product_name"]) ?>">
                    </a>
                    <?php if ($has_promo_sp): ?>
                        <span class="badge-reduction position-absolute top-0 start-0 m-2">-<?= $sp["promotion_percent"] ?>%</span>
                    <?php endif; ?>
                </div>

                <div class="p-3 d-flex flex-column flex-fill">
                    <span class="brand"><?= htmlspecialchars($sp["brand_name"] ?? "Marque") ?></span>
                    <a href="product.php?slug=<?= urlencode($sp["product_slug"]) ?>" class="text-decoration-none text-dark">
                        <span class="name my-1 fw-semibold d-block"><?= htmlspecialchars($sp["product_name"]) ?></span>
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
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<script src="public/scripts/product.js"></script>

<?php include "public/includes/footer.php"; ?>