<?php 

require_once "public/includes/db.php";
require_once "public/includes/auth.php";
require_sav();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_type_id'], $_POST['customer_id'])) {
    $cid      = (int) $_POST['customer_id'];
    $new_type = (int) $_POST['user_type_id'];

    if ($cid > 0 && in_array($new_type, [1, 2], true)) {
        $stmt = $pdo->prepare("SELECT user_id FROM customers WHERE customer_id_account = ?");
        $stmt->execute([$cid]);
        $uid = $stmt->fetchColumn();

        if ($uid) {
            $pdo->prepare("UPDATE users SET user_type_id = ? WHERE user_id = ?")
                ->execute([$new_type, $uid]);
        }
    }
    header("Location: admin_users.php?updated=1");
    exit;
}


$users      = []; 
$user_roles = []; 
foreach ($pdo->query("SELECT user_id, user_mail, user_type_id FROM users") as $row) {
    $users[$row["user_id"]]      = $row["user_mail"];
    $user_roles[$row["user_id"]] = $row["user_type_id"];
}

$statuses = []; 
foreach ($pdo->query("SELECT order_type_id, order_type_name FROM order_status") as $s) {
    $statuses[$s["order_type_id"]] = $s["order_type_name"];
}

$status_class_map = [
    'En attente'     => 'statut-attente',
    'En préparation' => 'statut-preparation',
    'Expédiée'       => 'statut-expediee',
    'Livrée'         => 'statut-livree',
];

$mois_fr = [1=>'janvier',2=>'février',3=>'mars',4=>'avril',5=>'mai',6=>'juin',
            7=>'juillet',8=>'août',9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'];


$order_totals = []; 
foreach ($pdo->query("SELECT order_id, contains_quantity, contains_unit_price FROM contains") as $l) {
    $oid = $l["order_id"];
    $order_totals[$oid] = ($order_totals[$oid] ?? 0) + $l["contains_quantity"] * $l["contains_unit_price"];
}

$customer_orders = [];  
$customer_spent  = []; 

foreach ($pdo->query("SELECT order_id, order_number, order_date, order_type_id, customer_id_account FROM orders ORDER BY order_date DESC") as $o) {
    $cid    = $o["customer_id_account"];
    $ototal = $order_totals[$o["order_id"]] ?? 0;
    $sname  = $statuses[$o["order_type_id"]] ?? '—';

    $d       = new DateTime($o["order_date"]);
    $date_fr = $d->format('j') . ' ' . $mois_fr[(int) $d->format('n')] . ' ' . $d->format('Y');

    $customer_orders[$cid][] = [
        'number' => $o["order_number"],
        'date'   => $date_fr,
        'total'  => number_format($ototal, 2, ',', ' ') . ' €',
        'status' => $sname,
        'class'  => $status_class_map[$sname] ?? '',
    ];
    $customer_spent[$cid] = ($customer_spent[$cid] ?? 0) + $ototal;
}

$customers = $pdo->query("SELECT * FROM customers ORDER BY customer_name")->fetchAll();

$rows = [];
foreach ($customers as $c) {
    $cid    = $c['customer_id_account'];
    $orders = $customer_orders[$cid] ?? [];
    $rows[] = [
        'id'        => (int) $cid,
        'full_name' => $c['customer_firstname'] . ' ' . $c['customer_name'],
        'initials'  => mb_strtoupper(mb_substr($c['customer_firstname'], 0, 1) . mb_substr($c['customer_name'], 0, 1)),
        'mail'      => $users[$c['user_id']] ?? '',
        'phone'     => $c['customer_phone'],
        'role'      => (int) ($user_roles[$c['user_id']] ?? 1),
        'nb_orders' => count($orders),
        'spent'     => number_format($customer_spent[$cid] ?? 0, 2, ',', ' ') . ' €',
        'orders'    => $orders,
    ];
}

$clients = [];
$admins  = [];
foreach ($rows as $r) {
    if ($r['role'] === 2) {
        $admins[] = $r;
    } else {
        $clients[] = $r;
    }
}

function render_client_rows(array $rows): void {
    if (empty($rows)) {
        echo '<tr><td colspan="4" class="text-center text-secondary py-3">Aucun résultat.</td></tr>';
        return;
    }
    foreach ($rows as $r):
?>

                        <tr>
                            <td>
                                <div class="client-identity">
                                    <span class="client-avatar"><?= htmlspecialchars($r['initials']) ?></span>
                                    <div class="client-cell">
                                        <span class="nom"><?= htmlspecialchars($r['full_name']) ?></span>
                                        <span class="mail"><?= htmlspecialchars($r['mail']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($r['phone']) ?></td>
                            <td><span class="count-pill"><?= (int) $r['nb_orders'] ?></span></td>
                            <td class="col-actions">
                                <button type="button" class="btn-row-action"
                                    data-bs-toggle="modal" data-bs-target="#client-modal"
                                    data-id="<?= (int) $r['id'] ?>"
                                    data-avatar="<?= htmlspecialchars($r['initials']) ?>"
                                    data-nom="<?= htmlspecialchars($r['full_name']) ?>"
                                    data-email="<?= htmlspecialchars($r['mail']) ?>"
                                    data-tel="<?= htmlspecialchars($r['phone']) ?>"
                                    data-commandes="<?= (int) $r['nb_orders'] ?>"
                                    data-total="<?= htmlspecialchars($r['spent']) ?>"
                                    data-role="<?= (int) $r['role'] ?>"
                                    data-orders='<?= htmlspecialchars(json_encode($r['orders'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'
                                    aria-label="Voir la fiche">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
<?php
    endforeach;
}

$menu_actif = 'clients';
include "public/includes/header_admin.php"; 
?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">›</span> <span class="current">Clients</span>
        </nav>
        <h1>Clients &amp; administrateurs</h1>
    </header>
    <div class="admin-content">

        <?php if (!empty($_GET['updated'])): ?>
            <div class="profile-alert profile-alert--success">
                <i class="fa-solid fa-check"></i> Rôle mis à jour.
            </div>
        <?php endif; ?>

        <div class="orders-toolbar">
            <div class="search-admin">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input class="input-admin" id="clients-search" type="search"
                    placeholder="Rechercher (nom, email, téléphone...)">
            </div>
        </div>

        <section class="admin-card">
            <div class="card-title">
                <span class="num"><i class="fa-solid fa-users"></i></span>
                <h2>Clients</h2>
            </div>
            <p class="results-count" id="clients-count"><?= count($clients) ?> client<?= count($clients) > 1 ? 's' : '' ?></p>

            <div class="table-scroll">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Téléphone</th>
                            <th>Commandes</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php render_client_rows($clients); ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card">
            <div class="card-title">
                <span class="num"><i class="fa-solid fa-user-shield"></i></span>
                <h2>Administrateurs</h2>
            </div>
            <p class="results-count" id="admins-count"><?= count($admins) ?> administrateur<?= count($admins) > 1 ? 's' : '' ?></p>

            <div class="table-scroll">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Administrateur</th>
                            <th>Téléphone</th>
                            <th>Commandes</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php render_client_rows($admins); ?>
                    </tbody>
                </table>
            </div>
        </section>
 
        <div class="modal fade" id="client-modal" tabindex="-1" aria-labelledby="cm-name" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content client-modal">
 
                    <div class="modal-header">
                        <div class="client-modal-head">
                            <span class="client-avatar lg" id="cm-avatar"></span>
                            <div>
                                <h5 class="modal-title" id="cm-name"></h5>
                                <span class="client-modal-mail" id="cm-email"></span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
 
                    <div class="modal-body">
 
                        <div class="client-section">
                            <h6 class="client-section-title">Coordonnées</h6>
                            <div class="client-info-grid">
                                <div class="info-item">
                                    <span class="info-label">Téléphone</span>
                                    <span class="info-value" id="cm-phone"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Inscription</span>
                                    <span class="info-value" id="cm-registered"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Commandes</span>
                                    <span class="info-value" id="cm-orders-count"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total dépensé</span>
                                    <span class="info-value" id="cm-total"></span>
                                </div>
                            </div>
                        </div>
 
                        <div class="client-section">
                            <h6 class="client-section-title">Rôle</h6>
                            <form class="role-row" method="post" action="">
                                <input type="hidden" name="customer_id" id="cm-customer-id" value="">
                                <select class="input-admin" id="cm-role" name="user_type_id" aria-label="Rôle du client">
                                    <option value="1">Client</option>
                                    <option value="2">Administrateur</option>
                                </select>
                                <button type="submit" class="btn-admin-primary" id="cm-role-save">Mettre à jour</button>
                            </form>
                        </div>
 
                        <div class="client-section">
                            <h6 class="client-section-title">Adresses</h6>
                            <div class="address-list">
                                <div class="address-block">
                                    <span class="address-tag">Livraison</span>
                                    <p id="cm-address-shipping"></p>
                                </div>
                                <div class="address-block">
                                    <span class="address-tag">Facturation</span>
                                    <p id="cm-address-billing"></p>
                                </div>
                            </div>
                        </div>
 
                        <div class="client-section">
                            <h6 class="client-section-title">Dernières commandes</h6>
                            <div class="table-scroll">
                                <table class="orders-table">
                                    <thead>
                                        <tr>
                                            <th>Commande</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cm-orders"></tbody>
                                </table>
                            </div>
                        </div>
 
                    </div>
 
                    <div class="modal-footer">
                        <button type="button" class="btn-draft" data-bs-dismiss="modal">Fermer</button>
                        <a class="btn-admin-primary" id="cm-full-link" href="#">Voir la fiche complète</a>
                    </div>
 
                </div>
            </div>
        </div>
 
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="public/scripts/admin_users.js"></script>





