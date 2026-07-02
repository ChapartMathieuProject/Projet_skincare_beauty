<?php
require_once "public/includes/db.php";
 
$statut = $_GET['statut'] ?? 'toutes';
if (!in_array($statut, ['toutes', 'actives', 'desactivees'], true)) {
    $statut = 'toutes';
}
 
/* ---------- TRAITEMENT DES ACTIONS (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $back_statut = $_POST['statut'] ?? 'toutes';
 
    if ($action === 'toggle') {
        $promo_id = (int) ($_POST['promotion_id'] ?? 0);
        $active   = isset($_POST['active']) ? 1 : 0;
        $pdo->prepare("UPDATE promotions SET promotion_is_active = ? WHERE promotion_id = ?")
            ->execute([$active, $promo_id]);
 
    } elseif ($action === 'create') {
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $percent    = (int) ($_POST['percent'] ?? 0);
        if ($product_id > 0 && $percent >= 1 && $percent <= 99) {
            $pdo->prepare(
                "INSERT INTO promotions (product_id, promotion_percent, promotion_is_active)
                 VALUES (?, ?, 1)
                 ON DUPLICATE KEY UPDATE promotion_percent = VALUES(promotion_percent),
                                         promotion_is_active = 1"
            )->execute([$product_id, $percent]);
        }
 
    } elseif ($action === 'delete') {
        $promo_id = (int) ($_POST['promotion_id'] ?? 0);
        $pdo->prepare("DELETE FROM promotions WHERE promotion_id = ?")->execute([$promo_id]);
    }
 
    header("Location: admin_promotions.php?statut=" . urlencode($back_statut));
    exit;
}
 
/* ---------- CHARGEMENT POUR L'AFFICHAGE (SANS JOIN) ---------- */

// 1. CHARGEMENT DES DICTIONNAIRES (Marques et Produits complets)
$brands = [];
foreach ($pdo->query("SELECT brand_id, brand_name FROM brands") as $row) {
    $brands[$row["brand_id"]] = $row["brand_name"];
}

$products_dict = [];
$all_products = []; // Pour remplir le select de la modale
foreach ($pdo->query("SELECT * FROM products ORDER BY product_name") as $row) {
    $products_dict[$row["product_id"]] = $row;
    $all_products[] = [
        'product_id'   => $row['product_id'],
        'product_name' => $row['product_name']
    ];
}

// 2. RÉCUPÉRATION DES PROMOTIONS UNIQUEMENT
$where = '';
if ($statut === 'actives')      $where = 'WHERE promotion_is_active = 1';
if ($statut === 'desactivees')  $where = 'WHERE promotion_is_active = 0';
 
$sql = "SELECT promotion_id, product_id, promotion_percent, promotion_is_active 
        FROM promotions 
        $where 
        ORDER BY promotion_id DESC";
$promos = $pdo->query($sql)->fetchAll();


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

$menu_actif = 'promotions';
?>

<?php include "public/includes/header_admin.php"; ?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">›</span> <span class="current">Promotions</span>
        </nav>
        <h1>Promotions</h1>
 
        <div class="topbar-actions">
            <button type="button" class="btn-admin-primary" data-bs-toggle="modal" data-bs-target="#promo-create-modal">
                <i class="fa-solid fa-plus"></i> Créer une promo
            </button>
        </div>
    </header>
 
    <div class="admin-content">
 
        <div class="orders-toolbar">
            <div class="filter-pills">
                <a class="filter-chip <?= $statut === 'toutes' ? 'active' : '' ?>" href="?statut=toutes">Toutes</a>
                <a class="filter-chip <?= $statut === 'actives' ? 'active' : '' ?>" href="?statut=actives">Actives</a>
                <a class="filter-chip <?= $statut === 'desactivees' ? 'active' : '' ?>" href="?statut=desactivees">Désactivées</a>
            </div>
        </div>
 
        <p class="results-count"><?= count($promos) ?> promotion<?= count($promos) > 1 ? 's' : '' ?></p>
 
        <?php if (!$promos): ?>
            <div class="profile-empty">
                <i class="fa-solid fa-tag"></i>
                <p class="mb-0">Aucune promotion <?= $statut !== 'toutes' ? 'dans ce filtre' : 'pour le moment' ?>.</p>
            </div>
        <?php else: ?>
 
        <div class="promo-grid">
            <?php foreach ($promos as $promo): 
                $associated_product = $products_dict[$promo['product_id']] ?? null;
            
                if (!$associated_product) continue;
                $brand_name   = $brands[$associated_product['brand_id']] ?? 'Marque inconnue';
                $product_name = $associated_product['product_name'];
                $vente  = prix_vente($associated_product['product_buy_price'], $associated_product['product_margin']);
                $remise = $vente * (1 - (int) $promo['promotion_percent'] / 100);
                $actif  = (int) $promo['promotion_is_active'] === 1;
            ?>
            <article class="promo-card">
                <div class="promo-visual" style="background: linear-gradient(135deg, #f7e7e3, #ecd1cb);">
                    <span class="badge badge-reduction">-<?= (int) $promo['promotion_percent'] ?>%</span>
                    <?php if ($actif): ?>
                        <span class="statut-badge statut-active"><span class="point"></span>Active</span>
                    <?php else: ?>
                        <span class="statut-badge statut-expiree"><span class="point"></span>Désactivée</span>
                    <?php endif; ?>
                </div>
 
                <div class="promo-body">
                    <span class="promo-brand"><?= e($brand_name) ?></span>
                    <h3 class="promo-name"><?= e($product_name) ?></h3>
                    <div class="promo-prices">
                        <span class="promo-new"><?= euro($remise) ?></span>
                        <span class="strike-price"><?= euro($vente) ?></span>
                    </div>
                </div>
 
                <div class="promo-footer">
                    <form method="post" class="d-flex align-items-center gap-2 m-0">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="promotion_id" value="<?= (int) $promo['promotion_id'] ?>">
                        <input type="hidden" name="statut" value="<?= e($statut) ?>">
                        <label class="switch">
                            <input type="checkbox" name="active" value="1"
                                   onchange="this.form.submit()" <?= $actif ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                        <span class="promo-toggle-label"><?= $actif ? 'Activée' : 'Désactivée' ?></span>
                    </form>
 
                    <div class="promo-actions">
                        <form method="post" class="m-0"
                              onsubmit="return confirm('Supprimer cette promotion ?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="promotion_id" value="<?= (int) $promo['promotion_id'] ?>">
                            <input type="hidden" name="statut" value="<?= e($statut) ?>">
                            <button type="submit" class="btn-row-action" aria-label="Supprimer la promotion">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
 
        <?php endif; ?>
 
    </div>
</div>
 
<div class="modal fade" id="promo-create-modal" tabindex="-1" aria-labelledby="promo-create-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content client-modal">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="promo-create-label">Créer une promotion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
 
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="statut" value="<?= e($statut) ?>">
 
                    <div class="mb-3">
                        <label class="form-label-admin" for="promo-product">Produit</label>
                        <select class="input-admin" id="promo-product" name="product_id" required>
                            <option value="">— Choisir un produit —</option>
                            <?php foreach ($all_products as $prod): ?>
                                <option value="<?= (int) $prod['product_id'] ?>"><?= e($prod['product_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
 
                    <div class="mb-1">
                        <label class="form-label-admin" for="promo-percent">Remise (%)</label>
                        <input class="input-admin" type="number" id="promo-percent" name="percent"
                               min="1" max="99" placeholder="ex : 27" required>
                    </div>
                    <p class="hint m-0">Si le produit a déjà une promo, elle sera mise à jour.</p>
                </div>
 
                <div class="modal-footer">
                    <button type="button" class="btn-draft" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-admin-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
 
</div>
 
</body>
</html>