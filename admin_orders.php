
<?php 
require_once "public/includes/db.php";

$customers = [];
foreach ($pdo->query("SELECT customer_id_account, customer_name, customer_firstname, user_id FROM customers") as $row) {
    $customers[$row["customer_id_account"]] = [
        "name"      => $row["customer_name"],
        "firstname" => $row["customer_firstname"],
        "user_id"   => $row["user_id"],
    ];
}

$users = [];
foreach ($pdo->query("SELECT user_id, user_mail FROM users") as $row) {
    $users[$row["user_id"]] = $row["user_mail"];
}

$statuses = [];
foreach ($pdo->query("SELECT order_type_id, order_type_name FROM order_status") as $row) {
    $statuses[$row["order_type_id"]] = $row["order_type_name"];
}

$order_stats = [];
foreach ($pdo->query("SELECT order_id, quantity, unit_price FROM contains") as $row) {
    $oid = $row['order_id'];
    if (!isset($order_stats[$oid])) {
        $order_stats[$oid] = ['nb' => 0, 'total' => 0];
    }
    $order_stats[$oid]['nb']    += (int) $row['quantity'];
    $order_stats[$oid]['total'] += $row['quantity'] * $row['unit_price'];
}

$orders = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC")->fetchAll();


$status_meta = [
    'En attente'     => ['class' => 'statut-attente',     'key' => 'attente'],
    'En préparation' => ['class' => 'statut-preparation', 'key' => 'preparation'],
    'Expédiée'       => ['class' => 'statut-expediee',    'key' => 'expediee'],
    'Livrée'         => ['class' => 'statut-livree',      'key' => 'livree'],
];

$mois_fr = [1=>'janvier',2=>'février',3=>'mars',4=>'avril',5=>'mai',6=>'juin',
            7=>'juillet',8=>'août',9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'];


$menu_actif = 'commandes'; //<!-- Changer le 'commandes' en fonction de la page -->
include "public/includes/header_admin.php"; 
 ?>


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
        <!-- Filtrage (JS ou ?statut) à brancher plus tard ; chaque ligne porte data-statut -->
        <div class="filter-pills" id="orders-filters">
            <button class="filter-chip active" data-statut="toutes">Toutes</button>
            <button class="filter-chip" data-statut="attente">En attente</button>
            <button class="filter-chip" data-statut="preparation">En préparation</button>
            <button class="filter-chip" data-statut="expediee">Expédiée</button>
            <button class="filter-chip" data-statut="livree">Livrée</button>
        </div>
    </div>
 
    <p class="results-count" id="orders-count"><?= count($orders) ?> commande<?= count($orders) > 1 ? 's' : '' ?></p>
 
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
                    <tr><td colspan="7" class="text-center text-secondary py-4">Aucune commande pour le moment.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o):
                         $cust          = $customers[$o['customer_id_account']] ?? null;
                        $full_name      = $cust ? ($cust['firstname'] . ' ' . $cust['name']) : 'Client inconnu';
                        $mail           = ($cust && isset($users[$cust['user_id']])) ? $users[$cust['user_id']] : '';
                        $stats          = $order_stats[$o['order_id']] ?? ['nb' => 0, 'total' => 0];
                        $status_name    = $statuses[$o['order_type_id']] ?? '—';
                        $meta           = $status_meta[$status_name] ?? ['class' => '', 'key' => ''];
                        $d              = new DateTime($o['order_date']);
                        $date_fr        = $d->format('j') . ' ' . $mois_fr[(int) $d->format('n')] . ' ' . $d->format('Y');
                    ?>
                    <tr data-statut="<?= htmlspecialchars($meta['key']) ?>">
                        <td class="order-num">#<?= htmlspecialchars($o['order_number']) ?></td>
                        <td class="client-cell">
                            <span class="nom"><?= htmlspecialchars($full_name) ?></span>
                            <span class="mail"><?= htmlspecialchars($mail) ?></span>
                        </td>
                        <td><?= htmlspecialchars($date_fr) ?></td>
                        <td><?= (int) $stats['nb'] ?></td>
                        <td class="order-total"><?= number_format($stats['total'], 2, ',', ' ') ?> €</td>
                        <td>
                            <span class="statut-badge <?= htmlspecialchars($meta['class']) ?>"><span class="point"></span><?= htmlspecialchars($status_name) ?></span>
                        </td>
                        <td class="col-actions">
                            <a class="btn-row-action" href="commande_detail.php?id=<?= (int) $o['order_id'] ?>" aria-label="Voir la commande">
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
</div>
 
</body>
</html>
 