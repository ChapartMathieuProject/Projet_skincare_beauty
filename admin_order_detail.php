<?php
require_once "public/includes/db.php";

$id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int) $_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status']) && $id > 0) {
    $newStatus = (int) $_POST['new_status'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM order_status WHERE order_type_id = ?");
    $check->execute([$newStatus]);
    if ($check->fetchColumn()) {
        $update = $pdo->prepare("UPDATE orders SET order_type_id = ? WHERE order_id = ?");
        $update->execute([$newStatus, $id]);
    }
    header("Location: admin_order_detail.php?id=$id");
    exit;
}

$allStatuses = $pdo->query("SELECT order_type_id, order_type_name FROM order_status")->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

$menu_actif = 'commandes';

$date_fr = '';
$status_class = '';
$status_name = '—';
$payment_name = '—';
$client_name = 'Client inconnu';
$client_mail = '';
$total = 0;
$lines = [];
$bill = null;
$company = null;
$delivery_address = null;
$invoice_date_fr = '';

$mois_fr = [1=>'janvier',2=>'février',3=>'mars',4=>'avril',5=>'mai',6=>'juin',
            7=>'juillet',8=>'août',9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'];

$status_meta = [
    'En cours' => 'statut-preparation',
    'Expédié'  => 'statut-expediee',
    'Annulé'   => 'statut-attente',
];


if ($order) {

    $stmt = $pdo->prepare("SELECT customer_name, customer_firstname, user_id FROM customers WHERE customer_id_account = ?");
    $stmt->execute([$order['customer_id_account']]);
    $customer = $stmt->fetch();

    $client_name = $customer ? ($customer['customer_firstname'] . ' ' . $customer['customer_name']) : 'Client inconnu';
    $client_mail = '';
    if ($customer) {
        $stmt = $pdo->prepare("SELECT user_mail FROM users WHERE user_id = ?");
        $stmt->execute([$customer['user_id']]);
        $client_mail = $stmt->fetchColumn() ?: '';
    }

    $stmt = $pdo->prepare("SELECT order_type_name FROM order_status WHERE order_type_id = ?");
    $stmt->execute([$order['order_type_id']]);
    $status_name  = $stmt->fetchColumn() ?: '—';
    $status_class = $status_meta[$status_name] ?? '';

    $stmt = $pdo->prepare("SELECT payement_type_name FROM payement_types WHERE payement_type_id = ?");
    $stmt->execute([$order['payment_type_id']]);
    $payment_name = $stmt->fetchColumn() ?: '—';

    $stmt = $pdo->prepare("SELECT * FROM contains WHERE order_id = ?");
    $stmt->execute([$id]);
    $lines = $stmt->fetchAll();

    $products = [];
    foreach ($pdo->query("SELECT product_id, product_name, product_slug FROM products") as $row) {
        $products[$row['product_id']] = ['name' => $row['product_name'], 'slug' => $row['product_slug']];
    }

    $total = 0;
    foreach ($lines as $l) {
        $total += $l['contains_quantity'] * $l['contains_unit_price'];
    }

    $d       = new DateTime($order['order_date']);
    $date_fr = $d->format('j') . ' ' . $mois_fr[(int) $d->format('n')] . ' ' . $d->format('Y');

    /* ---------- Données de FACTURE (pour la modale) ---------- */

    $stmt = $pdo->prepare("SELECT bill_number, bill_delivery_date FROM bills WHERE order_id = ?");
    $stmt->execute([$id]);
    $bill = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM companies WHERE company_id_account = ?");
    $stmt->execute([$order['company_id_account']]);
    $company = $stmt->fetch();

    $delivery_address = null;
    $stmt = $pdo->prepare("SELECT address_id FROM deliveries WHERE delivery_id = ?");
    $stmt->execute([$order['deliveries_id']]);
    $address_id = $stmt->fetchColumn();
    if ($address_id) {
        $stmt = $pdo->prepare("SELECT * FROM addresses WHERE address_id = ?");
        $stmt->execute([$address_id]);
        $delivery_address = $stmt->fetch();
    }

    $invoice_date_fr = $date_fr;
    if ($bill && !empty($bill['bill_delivery_date'])) {
        $bd = new DateTime($bill['bill_delivery_date']);
        $invoice_date_fr = $bd->format('j') . ' ' . $mois_fr[(int) $bd->format('n')] . ' ' . $bd->format('Y');
    }
}

include "public/includes/header_admin.php";
?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            <a href="admin_orders.php">Commandes</a> <span class="sep">›</span>
            <span class="current"><?= $order ? '#' . htmlspecialchars($order['order_number']) : 'Introuvable' ?></span>
        </nav>
        <h1><?= $order ? 'Commande #' . htmlspecialchars($order['order_number']) : 'Commande introuvable' ?></h1>

        <?php if ($order): ?>
        <div class="topbar-actions">
            <?php if ($bill): ?>
                <button type="button" class="btn-admin-primary" data-bs-toggle="modal" data-bs-target="#invoice-modal">
                    <i class="fa-solid fa-file-invoice"></i> Voir la facture
                </button>
            <?php endif; ?>
            <a href="admin_orders.php" class="btn-draft"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </div>
        <?php endif; ?>
    </header>

    <div class="admin-content">

        <?php if (!$order): ?>

            <div class="profile-alert profile-alert--error">
                <i class="fa-solid fa-circle-exclamation"></i> Cette commande n'existe pas.
            </div>
            <a href="admin_orders.php" class="btn-draft"><i class="fa-solid fa-arrow-left"></i> Retour aux commandes</a>

        <?php else: ?>

            <section class="admin-card">
                <div class="card-title">
                    <span class="num"><i class="fa-solid fa-receipt"></i></span>
                    <h2>Résumé</h2>
                </div>
                <div class="client-info-grid">
                    <div class="info-item">
                        <span class="info-label">Date</span>
                        <span class="info-value"><?= htmlspecialchars($date_fr) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Statut</span>
                        <span class="info-value">
                            <span class="statut-badge <?= htmlspecialchars($status_class) ?>"><span class="point"></span><?= htmlspecialchars($status_name) ?></span>
                        </span>
                        <form method="post" style="display:flex; gap:.5rem; margin-top:.5rem;">
                            <select name="new_status" class="form-select form-select-sm" style="width:auto;">
                                <?php foreach ($allStatuses as $s): ?>
                                    <option value="<?= (int) $s['order_type_id'] ?>" <?= $s['order_type_id'] == $order['order_type_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['order_type_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Mettre à jour</button>
                        </form>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Paiement</span>
                        <span class="info-value"><?= htmlspecialchars($payment_name) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total</span>
                        <span class="info-value order-total"><?= number_format($total, 2, ',', ' ') ?> €</span>
                    </div>
                </div>
            </section>

            <section class="admin-card">
                <div class="card-title">
                    <span class="num"><i class="fa-solid fa-user"></i></span>
                    <h2>Client</h2>
                </div>
                <div class="client-info-grid">
                    <div class="info-item">
                        <span class="info-label">Nom</span>
                        <span class="info-value"><?= htmlspecialchars($client_name) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">E-mail</span>
                        <span class="info-value"><?= htmlspecialchars($client_mail) ?></span>
                    </div>
                </div>
            </section>

            <section class="admin-card">
                <div class="card-title">
                    <span class="num"><i class="fa-solid fa-box"></i></span>
                    <h2>Produits</h2>
                </div>

                <div class="table-scroll">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lines)): ?>
                                <tr><td colspan="4" class="text-center text-secondary py-4">Aucun produit dans cette commande.</td></tr>
                            <?php else: ?>
                                <?php foreach ($lines as $l):
                                    $prod     = $products[$l['product_id']] ?? null;
                                    $name     = $prod ? $prod['name'] : ('Produit #' . (int) $l['product_id']);
                                    $slug     = $prod['slug'] ?? '';
                                    $subtotal = $l['contains_quantity'] * $l['contains_unit_price'];
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($slug !== ''): ?>
                                            <a href="product.php?slug=<?= urlencode($slug) ?>" class="text-decoration-none text-reset"><?= htmlspecialchars($name) ?></a>
                                        <?php else: ?>
                                            <?= htmlspecialchars($name) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($l['contains_unit_price'], 2, ',', ' ') ?> €</td>
                                    <td><?= (int) $l['contains_quantity'] ?></td>
                                    <td class="order-total"><?= number_format($subtotal, 2, ',', ' ') ?> €</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Total</td>
                                <td class="order-total fw-bold"><?= number_format($total, 2, ',', ' ') ?> €</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <?php if ($bill): ?>
            <div class="modal fade" id="invoice-modal" tabindex="-1" aria-labelledby="invoice-title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content client-modal">

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="invoice-title">Facture <?= htmlspecialchars($bill['bill_number']) ?></h5>
                                <span class="client-modal-mail">Émise le <?= htmlspecialchars($invoice_date_fr) ?> — Commande #<?= htmlspecialchars($order['order_number']) ?></span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>

                        <div class="modal-body">
                            <div class="client-section">
                                <div class="address-list">
                                    <div class="address-block">
                                        <span class="address-tag">Vendeur</span>
                                        <?php if ($company): ?>
                                        <p>
                                            <strong><?= htmlspecialchars($company['company_name']) ?></strong><br>
                                            <?= htmlspecialchars($company['company_adress_1']) ?><br>
                                            <?= htmlspecialchars($company['company_postcode'] . ' ' . $company['company_city']) ?><br>
                                            <?= htmlspecialchars($company['company_country']) ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="address-block">
                                        <span class="address-tag">Client</span>
                                        <p>
                                            <strong><?= htmlspecialchars($client_name) ?></strong><br>
                                            <?= htmlspecialchars($client_mail) ?>
                                            <?php if ($delivery_address): ?>
                                                <br><?= htmlspecialchars($delivery_address['address_adress_1']) ?>
                                                <br><?= htmlspecialchars($delivery_address['address_postcode'] . ' ' . $delivery_address['address_city']) ?>
                                                <br><?= htmlspecialchars($delivery_address['address_country']) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="client-section">
                                <h6 class="client-section-title">Détail</h6>
                                <div class="table-scroll">
                                    <table class="orders-table">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Prix unitaire</th>
                                                <th>Quantité</th>
                                                <th>Sous-total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lines as $l):
                                                $prod     = $products[$l['product_id']] ?? null;
                                                $name     = $prod ? $prod['name'] : ('Produit #' . (int) $l['product_id']);
                                                $subtotal = $l['contains_quantity'] * $l['contains_unit_price'];
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($name) ?></td>
                                                <td><?= number_format($l['contains_unit_price'], 2, ',', ' ') ?> €</td>
                                                <td><?= (int) $l['contains_quantity'] ?></td>
                                                <td class="order-total"><?= number_format($subtotal, 2, ',', ' ') ?> €</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end fw-semibold">Total</td>
                                                <td class="order-total fw-bold"><?= number_format($total, 2, ',', ' ') ?> €</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn-draft" data-bs-dismiss="modal">Fermer</button>
                            <button type="button" class="btn-admin-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimer</button>
                        </div>

                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>