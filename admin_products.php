<?php

require_once "public/includes/db.php";
require_once "public/includes/auth.php";
require_admin();

$q = trim($_GET['q'] ?? '');

$sql    = "SELECT product_id, product_name, product_slug,
                  product_buy_price, product_margin, product_quantity,
                  product_alert, product_is_status, brand_id
           FROM products";
$params = [];

if ($q !== '') {
    $sql = $sql . " WHERE product_name LIKE ?";
    $params[] = '%' . $q . '%';
}
$sql = $sql . " ORDER BY product_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$marques_par_id   = [];   // brand_id      => brand_name
$type_par_produit = [];   // product_id    => product_type_name
$promo_par_produit = [];  // product_id    => ['percent' => .., 'active' => ..]

if ($rows) {
    $product_ids = array_column($rows, 'product_id');
    $in_prod     = implode(',', array_fill(0, count($product_ids), '?'));

    $brand_ids = array_values(array_unique(array_column($rows, 'brand_id')));
    if ($brand_ids) {
        $in_brand = implode(',', array_fill(0, count($brand_ids), '?'));
        $st = $pdo->prepare("SELECT brand_id, brand_name FROM brands WHERE brand_id IN ($in_brand)");
        $st->execute($brand_ids);
        foreach ($st->fetchAll() as $b) {
            $marques_par_id[(int) $b['brand_id']] = $b['brand_name'];
        }
    }

    $st = $pdo->prepare("SELECT product_id, product_type_id FROM lien_product_type WHERE product_id IN ($in_prod)");
    $st->execute($product_ids);
    $liens = $st->fetchAll();

    $noms_types = [];
    $type_ids   = array_values(array_unique(array_column($liens, 'product_type_id')));
    if ($type_ids) {
        $in_type = implode(',', array_fill(0, count($type_ids), '?'));
        $st = $pdo->prepare("SELECT product_type_id, product_type_name FROM product_types WHERE product_type_id IN ($in_type)");
        $st->execute($type_ids);
        foreach ($st->fetchAll() as $t) {
            $noms_types[(int) $t['product_type_id']] = $t['product_type_name'];
        }
    }

    foreach ($liens as $lien) {
        $pid = (int) $lien['product_id'];
        $tid = (int) $lien['product_type_id'];
        if (!isset($type_par_produit[$pid]) && isset($noms_types[$tid])) {
            $type_par_produit[$pid] = $noms_types[$tid];
        }
    }

    $st = $pdo->prepare(
        "SELECT product_id, promotion_percent, promotion_is_active
         FROM promotions
         WHERE product_id IN ($in_prod)"
    );
    $st->execute($product_ids);
    foreach ($st->fetchAll() as $pr) {
        $promo_par_produit[(int) $pr['product_id']] = [
            'percent' => (int) $pr['promotion_percent'],
            'active'  => (int) $pr['promotion_is_active'],
        ];
    }
}

$products = [];
foreach ($rows as $row) {
    $pid   = (int) $row['product_id'];
    $bid   = (int) $row['brand_id'];
    $promo = $promo_par_produit[$pid] ?? null;

    $row['brand_name']          = $marques_par_id[$bid] ?? null;
    $row['product_type_name']   = $type_par_produit[$pid] ?? null;
    $row['promotion_percent']   = $promo['percent'] ?? null;
    $row['promotion_is_active'] = $promo['active'] ?? null;

    $products[] = $row;
}

// Helpers
function euro($n)
{
    return number_format((float) $n, 2, ',', ' ') . ' €';
}
function e($v)
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}
function prix_vente($buy, $margin)
{
    return (float) $buy * (1 + (int) $margin / 100);
}

?>

<?php $menu_actif = 'produits'; ?>
<?php include "public/includes/header_admin.php"; ?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb_admin">
            Tableau de bord <span class="sep">,</span> <span class="current">Produits</span>
        </nav>
        <h1>Produits</h1>
        <div class="topbar-actions">
            <a class="btn-admin-primary" href="admin_add_product.php">
                <i class="fa-solid fa-plus"></i> Créer un produit
            </a>
        </div>
    </header>
    <div class="admin-content">
        <section class="admin-card">
            <form method="get" class="orders-toolbar">
                <div class="search-admin">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input class="input-admin" type="search" name="q" value="<?= e($q) ?>"
                           placeholder="Rechercher un produit par nom…">
                </div>
            </form>
            <p class="results-count">
                <?= count($products) ?> produit<?= count($products) > 1 ? "s" : "" ?>
                <?= $q !== "" ? 'pour " ' . e($q) . ' "' : '' ?>
            </p>
            <?php if (!$products): ?>
                <div class="profile-empty">
                    <i class="fa-solid fa-box-open"></i>
                    <p class="mb-0">Aucun produit <?= $q !== "" ? "ne correspond à cette recherche" : "pour le moment" ?>.</p>
                </div>
            <?php else: ?>

                <div class="table-scroll">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Catégorie</th>
                                <th>Prix de vente</th>
                                <th>Stock</th>
                                <th>Statut</th>
                                <th>Promotion</th>
                                <th class="col-action">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($products as $prod) :
                                $vente    = prix_vente($prod["product_buy_price"], $prod["product_margin"]);
                                $actif    = (int) $prod["product_is_status"] === 1;
                                $stockBas = $prod["product_alert"] !== null
                                            && (int) $prod["product_quantity"] <= (int) $prod["product_alert"];
                                $enPromo  = (int) $prod["promotion_is_active"] === 1;
                            ?>
                                <tr>
                                    <td class="clien-cell">
                                        <span class="nom"><?= e($prod["product_name"]) ?></span>
                                        <span class="mail"><?= e($prod["brand_name"]) ?></span>
                                    </td>
                                    <td><?= $prod["product_type_name"] ? e($prod["product_type_name"]) : "-" ?></td>
                                    <td class="order-total"><?= euro($vente) ?></td>
                                    <td>
                                        <?php if ($stockBas): ?>
                                            <span class="text-danger fw-semibold"><?= (int) $prod["product_quantity"] ?></span>
                                        <?php else: ?>
                                            <?= (int) $prod["product_quantity"] ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($actif): ?>
                                            <span class="statut-badge statut-active"><span class="point"></span>Actif</span>
                                        <?php else: ?>
                                            <span class="statut-badge statut-expiree"><span class="point"></span>Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($enPromo): ?>
                                            <span class="badge badge-reduction">-<?= (int) $prod["promotion_percent"] ?>%</span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td class="col-actions">
                                        <a class="btn-row-action" href="admin_add_product.php?id=<?= (int) $prod["product_id"] ?>"
                                            aria-label="Modifier le produit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
</div>

</body>

</html>