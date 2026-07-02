<?php
require_once "public/includes/db.php";

$user_types = [];
foreach ($pdo->query("SELECT user_type_id, user_type_name FROM user_types ORDER BY user_type_id") as $row) {
    $user_types[$row['user_type_id']] = $row['user_type_name'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_role') {

    $user_id = isset($_POST['user_id'])      ? (int) $_POST['user_id']      : 0;
    $role_id = isset($_POST['user_type_id']) ? (int) $_POST['user_type_id'] : 0;

    if ($user_id > 0 && array_key_exists($role_id, $user_types)) {
        $stmt = $pdo->prepare("UPDATE users SET user_type_id = :type WHERE user_id = :id");
        $stmt->execute(['type' => $role_id, 'id' => $user_id]);
    }

    header('Location: admin_users.php?role_updated=1');
    exit;
}



$users = [];
foreach ($pdo->query("SELECT user_id, user_mail, user_type_id FROM users") as $row) {
    $users[$row['user_id']] = [
        'mail' => $row['user_mail'],
        'type' => $row['user_type_id'],
    ];
}

$orders_count = [];
foreach ($pdo->query("SELECT customer_id_account, COUNT(*) AS nb FROM orders GROUP BY customer_id_account") as $row) {
    $orders_count[$row['customer_id_account']] = $row['nb'];
}

$customers = $pdo->query("SELECT * FROM customers ORDER BY customer_name")->fetchAll();

$menu_actif = 'clients';   // Changer selon la page
include "public/includes/header_admin.php";
?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">›</span> <span class="current">Clients</span>
        </nav>
        <h1>Clients</h1>
    </header>
    <div class="admin-content">

        <?php if (isset($_GET['role_updated'])): ?>
            <div class="alert alert-success" role="alert">Le rôle du client a été mis à jour.</div>
        <?php endif; ?>

        <section class="admin-card">
            <div class="orders-toolbar">
                <div class="search-admin">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input class="input-admin" id="clients-search" type="search"
                        placeholder="Rechercher un client (nom, email, téléphone...)">
                </div>
            </div>
            <p class="results-count" id="clients-count"><?= count($customers) ?> client<?= count($customers) > 1 ? 's' : '' ?></p>

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
                        <?php foreach ($customers as $c):
                            $full_name = $c['customer_firstname'] . ' ' . $c['customer_name'];
                            $initials  = mb_strtoupper(mb_substr($c['customer_firstname'], 0, 1) . mb_substr($c['customer_name'], 0, 1));
                            $mail      = $users[$c['user_id']]['mail'] ?? '';
                            $role_id   = $users[$c['user_id']]['type'] ?? '';
                            $nb_orders = $orders_count[$c['customer_id_account']] ?? 0;
                        ?>
                        <tr>
                            <td>
                                <div class="client-identity">
                                    <span class="client-avatar"><?= htmlspecialchars($initials) ?></span>
                                    <div class="client-cell">
                                        <span class="nom"><?= htmlspecialchars($full_name) ?></span>
                                        <span class="mail"><?= htmlspecialchars($mail) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($c['customer_phone']) ?></td>
                            <td><span class="count-pill"><?= (int) $nb_orders ?></span></td>
                            <td class="col-actions">
                                <button type="button" class="btn-row-action"
                                    data-bs-toggle="modal" data-bs-target="#client-modal"
                                    data-id="<?= (int) $c['customer_id_account'] ?>"
                                    data-user-id="<?= (int) $c['user_id'] ?>"
                                    data-role="<?= (int) $role_id ?>"
                                    data-avatar="<?= htmlspecialchars($initials) ?>"
                                    data-nom="<?= htmlspecialchars($full_name) ?>"
                                    data-email="<?= htmlspecialchars($mail) ?>"
                                    data-tel="<?= htmlspecialchars($c['customer_phone']) ?>"
                                    data-commandes="<?= (int) $nb_orders ?>"
                                    aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===================================================================
             MODALE FICHE CLIENT (une seule, partagée par toutes les lignes).
             Le JS (admin_clients.js) lit les data-… du bouton cliqué
             (event.relatedTarget) et remplit les emplacements #cm-… ci-dessous.
             =================================================================== -->
        <div class="modal fade" id="client-modal" tabindex="-1" aria-labelledby="cm-name" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content client-modal">

                    <!-- En-tête : avatar + nom + email -->
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

                        <!-- Coordonnées -->
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
                            <form method="post" action="admin_users.php" class="role-row">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" id="cm-user-id" value="">
                                <select class="input-admin" id="cm-role" name="user_type_id" aria-label="Rôle du client">
                                    <?php foreach ($user_types as $type_id => $type_name): ?>
                                        <option value="<?= (int) $type_id ?>"><?= htmlspecialchars($type_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-admin-primary" id="cm-role-save">Mettre à jour</button>
                            </form>
                        </div>

                        <!-- Adresses -->
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

                        <!-- Dernières commandes -->
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
                                    <!-- Le JS injectera les <tr> ici. Modèle d'une ligne :
                                         <tr>
                                           <td class="order-num">#CMD-0000</td>
                                           <td>00 mois 0000</td>
                                           <td class="order-total">0,00 €</td>
                                           <td><span class="statut-badge statut-livree"><span class="point"></span>Livrée</span></td>
                                         </tr>
                                    -->
                                    <tbody id="cm-orders"></tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-draft" data-bs-dismiss="modal">Fermer</button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="public/scripts/admin_users.js"></script>

</body>

</html>