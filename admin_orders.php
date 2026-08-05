<?php
require_once "public/includes/db.php";
require_once "public/includes/auth.php";
require_sav();

$menu_actif = 'commandes';

$customers = []; 
foreach ($pdo->query("SELECT customer_id_account, customer_name, customer_firstname, user_id FROM customers") as $c) {
    $customers[$c['customer_id_account']] = $c;
}

$user_mails = []; 
foreach ($pdo->query("SELECT user_id, user_mail FROM users") as $u) {
    $user_mails[$u['user_id']] = $u['user_mail'];
}

$statuses = [];   
foreach ($pdo->query("SELECT order_type_id, order_type_name FROM order_status") as $s) {
    $statuses[$s['order_type_id']] = $s['order_type_name'];
}

$order_totals   = [];  
$order_articles = []; 
foreach ($pdo->query("SELECT order_id, contains_quantity, contains_unit_price FROM contains") as $line) {
    $oid = $line['order_id'];
    $order_totals[$oid]   = ($order_totals[$oid]   ?? 0) + $line['contains_quantity'] * $line['contains_unit_price'];
    $order_articles[$oid] = ($order_articles[$oid] ?? 0) + $line['contains_quantity'];
}

$orders = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC")->fetchAll();

$mois_fr = [1=>'janvier',2=>'février',3=>'mars',4=>'avril',5=>'mai',6=>'juin',
            7=>'juillet',8=>'août',9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'];

$status_meta = [
    'En attente'     => ['class' => 'statut-attente',     'data' => 'attente'],
    'En préparation' => ['class' => 'statut-preparation', 'data' => 'preparation'],
    'Expédiée'       => ['class' => 'statut-expediee',    'data' => 'expediee'],
    'Livrée'         => ['class' => 'statut-livree',        'data' => 'livree'],
];
?>
<?php include "public/includes/header_admin.php"; ?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">›</span> <span class="current">Commandes</span>
        </nav>
        <h1>Commandes</h1>
    </header>
    <div class="orders-toolbar">
        <div class="search-admin">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input class="input-admin" id="orders-search" type="search" placeholder="Rechercher une commande, un client...">
        </div>
        <div class="filter-pills" id="orders-filters">
            <button class="filter-chip active" data-statut="toutes">Toutes</button>
            <button class="filter-chip" data-statut="attente">En attente</button>
            <button class="filter-chip" data-statut="preparation">En préparation</button>
            <button class="filter-chip" data-statut="expediee">Expédiée</button>
            <button class="filter-chip" data-statut="livree">Livrée</button>
        </div>
    </div>
    <p class="results-count" id="orders-count">
        <?= count($orders) ?> commande<?= count($orders) > 1 ? 's' : '' ?>
    </p>
    <div class="table-scroll">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Articles</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-4">Aucune commande pour l'instant.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o):
                        $cust    = $customers[$o['customer_id_account']] ?? null;
                        $cname   = $cust ? ($cust['customer_firstname'] . ' ' . $cust['customer_name']) : 'Client inconnu';
                        $cmail   = ($cust && isset($user_mails[$cust['user_id']])) ? $user_mails[$cust['user_id']] : '';

                        $sname   = $statuses[$o['order_type_id']] ?? '—';
                        $meta    = $status_meta[$sname] ?? ['class' => '', 'data' => ''];

                        $d       = new DateTime($o['order_date']);
                        $date_fr = $d->format('j') . ' ' . $mois_fr[(int) $d->format('n')] . ' ' . $d->format('Y');

                        $nb      = $order_articles[$o['order_id']] ?? 0;
                        $total   = $order_totals[$o['order_id']]   ?? 0;
                    ?>
                    <tr data-statut="<?= htmlspecialchars($meta['data']) ?>">
                        <td class="order-num">#<?= htmlspecialchars($o['order_number']) ?></td>
                        <td class="client-cell">
                            <span class="nom"><?= htmlspecialchars($cname) ?></span>
                            <span class="mail"><?= htmlspecialchars($cmail) ?></span>
                        </td>
                        <td><?= htmlspecialchars($date_fr) ?></td>
                        <td><?= (int) $nb ?></td>
                        <td class="order-total"><?= number_format($total, 2, ',', ' ') ?> €</td>
                        <td>
                            <span class="statut-badge <?= htmlspecialchars($meta['class']) ?>"><span class="point"></span><?= htmlspecialchars($sname) ?></span>
                        </td>
                        <td class="col-actions">
                            <a class="btn-row-action" href="admin_order_detail.php?id=<?= (int) $o['order_id'] ?>" aria-label="Voir la commande">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>

    </div>
</div>

</body>
</html>