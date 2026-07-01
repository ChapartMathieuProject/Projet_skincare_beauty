<?php

require_once "public/includes/db.php";

$q = trim($_GET['q'] ?? '');

$sql = "SELECT p.product_id, p.product_name, p.product_slug,
               p.product_buy_price, p.product_margin, p.product_quantity,
               p.product_alert, p.product_is_status,
               b.brand_name,
               pt.product_type_name,
               pr.promotion_percent, pr.promotion_is_active
        FROM products p
        JOIN brands b            ON b.brand_id = p.brand_id
        LEFT JOIN lien_product_type lpt ON lpt.product_id = p.product_id
        LEFT JOIN product_types pt      ON pt.product_type_id = lpt.product_type_id
        LEFT JOIN promotions pr         ON pr.product_id = p.product_id";

$params = [];
if ($q !== '') {
    $sql = $sql . " WHERE p.product_name LIKE ?";
    $params[] = '%' . $q . '%';
}
$sql = $sql . " ORDER BY p.product_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

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

<?php $menu_actif = 'produits'; ?> <!-- Changer le 'produits' en fonction de la page -->
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
            <P class="results-count">
                <?= count($products) ?> produit <?= count($products) > 1 ? "s" : "" ?>
                <?= $q !== "" ? 'pour " ' . e($q) . ' "' : '' ?>
            </P>
            <?php if (!$products): ?>
                <div class="profile-empty">
                    <i class="fa-solid fa-box-open"></i>
                    <p class="mb-0">Aucun produit <?= $q !== "" ? "ne correspond à cette recherche" : "pour le moment" ?> . </p>
                </div>
            <?php else: ?>

                <div class="table-scroll">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Catégorie</th>
                                <th>Prix de prix_vente</th>
                                <th>Stock</th>
                                <th>Statut</th>
                                <th>Promotion</th>
                                <th class="col-action">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($products as $prod) :
                                $vente = prix_vente($prod["product_buy_price"], $prod["product_margin"]);
                                $actif = (int) $prod["product_is_status"] === 1;
                                $stockBas = $prod["product_alert"] !== null && (int) $prod["promotion_is_active"] === 1;
                            ?>
                                <tr>
                                    <td class="clien-cell">
                                        <span class="nom"><?= e($prod["product_name"]) ?></span>
                                        <span class="mail"><?= e($prod["brand_name"]) ?></span>
                                    </td>
                                    <td><?= $prod["product_type_name"] ? e($prod["product_name"]) : "-" ?></td>
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