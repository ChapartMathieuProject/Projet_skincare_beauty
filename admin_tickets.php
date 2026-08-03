<?php

require_once "public/includes/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"]) || (int) ($_SESSION["user_type_id"] ?? 0) !== 2) {
    header("Location: login.php");
    exit;
}

$menu_actif = "sav";

$ticketDAO  = new TicketDAO($pdo);
$historyDAO = new TicketHistoryDAO($pdo);

$errorMessage = "";

$ticket_statuses = [];
foreach ($pdo->query("SELECT ticket_status_id, ticket_status_name FROM ticket_status") as $s) {
    $ticket_statuses[$s["ticket_status_id"]] = $s["ticket_status_name"];
}

$return_types = [];
foreach ($pdo->query("SELECT return_type_id, return_type_name FROM return_types") as $t) {
    $return_types[$t["return_type_id"]] = $t["return_type_name"];
}

$customers = [];
foreach ($pdo->query("SELECT customer_id_account, customer_name, customer_firstname FROM customers") as $c) {
    $customers[$c["customer_id_account"]] = $c["customer_firstname"] . " " . $c["customer_name"];
}

$order_statuses = [];
foreach ($pdo->query("SELECT order_type_id, order_type_name FROM order_status") as $s) {
    $order_statuses[$s["order_type_id"]] = $s["order_type_name"];
}

$orders_map = [];
foreach ($pdo->query("SELECT order_id, order_number, order_type_id, customer_id_account FROM orders") as $o) {
    $orders_map[$o["order_id"]] = $o;
}

$eligible_orders = [];
foreach ($orders_map as $oid => $o) {
    if (($order_statuses[$o["order_type_id"]] ?? "") === "Expédié") {
        $eligible_orders[$oid] = $o;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "create_ticket") {
    $order_id       = (int) ($_POST["order_id"] ?? 0);
    $return_type_id = (int) ($_POST["return_type_id"] ?? 0);
    $comment        = trim($_POST["ticket_comment"] ?? "");

    if (!isset($eligible_orders[$order_id])) {
        $errorMessage = "Commande invalide ou non expédié.";
    } elseif (!isset($return_types[$return_type_id])) {
        $errorMessage = "Type de retour invalide.";
    } elseif ($comment === "" || mb_strlen($comment) > 500) {
        $errorMessage = "Le commentaire est obligatoire (500 caractères maximum).";
    } else {
        $agent_id   = (int) $_SESSION["user_id"];
        $agent_name = $_SESSION["user_name"] ?? $_SESSION["user_mail"] ?? "Agent SAV";

        try {
            $pdo->beginTransaction();

            $numero     = $ticketDAO->generateReturnNumber($ticketDAO->getNextSequence());
            $ticket_id  = $ticketDAO->create(new Ticket([
                "ticket_return_number"  => $numero,
                "ticket_comment"        => $comment,
                "order_id"              => $order_id,
                "return_type_id"        => $return_type_id,
                "ticket_status_id"      => Ticket::STATUS_OUVERT,
                "user_id"               => $agent_id,
            ]));

            $ticketDAO->updateStatus($ticket_id, Ticket::STATUS_EN_COURS);
            $historyDAO->log(
                $ticket_id,
                $agent_id,
                "Numéro de retour $numero généré - ticket passé « En cours » par $agent_name"
            );

            $pdo->commit();

            // Peut-être mettre l'envoi de l'émail PHPMailer ici, à voir

            header("Location: admin_tickets.php?created=" . urlencode($numero));
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMessage = "Erreur lors de la création du ticket : " . $e->getMessage();
        }
    }
}

$tickets = $ticketDAO->findAll();
$status_badges = [
    Ticket::STATUS_OUVERT   => "status-attente",
    Ticket::STATUS_EN_COURS => "statut-preparation",
    Ticket::STATUS_CLOTURE  => "statut-livrée",
];

?>

<?php include "public/includes/header_admin.php"; ?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">›</span> <span class="current">SAV — Retours</span>
        </nav>
        <h1>Tickets de retour</h1>
    </header>

    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success" role="alert">
            Ticket <strong><?= htmlspecialchars($_GET['created'], ENT_QUOTES, 'UTF-8') ?></strong> créé
            — le client a été notifié par e-mail.
        </div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- ── Formulaire de création (Étape 1 : l'autorisation) ── -->
    <section class="admin-card">
        <h2>Ouvrir un ticket de retour</h2>
        <form method="POST" action="admin_tickets.php">
            <input type="hidden" name="action" value="create_ticket">

            <label class="form-label-admin" for="order_id">Commande concernée (expédiées uniquement)</label>
            <select class="input-admin" id="order_id" name="order_id" required>
                <option value="">— Choisir une commande —</option>
                <?php foreach ($eligible_orders as $oid => $o): ?>
                    <option value="<?= (int) $oid ?>">
                        <?= htmlspecialchars($o['order_number'], ENT_QUOTES, 'UTF-8') ?>
                        — <?= htmlspecialchars($customers[$o['customer_id_account']] ?? 'Client inconnu', ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="form-label-admin" for="return_type_id">Type de retour</label>
            <select class="input-admin" id="return_type_id" name="return_type_id" required>
                <option value="">— Choisir un type —</option>
                <?php foreach ($return_types as $tid => $tname): ?>
                    <option value="<?= (int) $tid ?>"><?= htmlspecialchars($tname, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>

            <label class="form-label-admin" for="ticket_comment">Commentaire (motif du retour)</label>
            <textarea class="input-admin" id="ticket_comment" name="ticket_comment"
                      rows="3" maxlength="500" required
                      placeholder="Ex : colis revenu NPAI, client injoignable par téléphone."></textarea>

            <button type="submit" class="btn-publish">Créer le ticket</button>
        </form>
    </section>

    <!-- ── Liste des tickets ── -->
    <p class="results-count">
        <?= count($tickets) ?> ticket<?= count($tickets) > 1 ? 's' : '' ?>
    </p>
    <div class="table-scroll">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>N° retour</th>
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Créé le</th>
                    <th>Statut</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="7">Aucun ticket de retour pour le moment.</td></tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <?php
                            $order  = $orders_map[$t->getOrderId()] ?? null;
                            $client = $order ? ($customers[$order['customer_id_account']] ?? '—') : '—';
                            $statut = $ticket_statuses[$t->getStatusId()] ?? '—';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($t->getReturnNumber(), ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($order['order_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($client, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($return_types[$t->getReturnTypeId()] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($t->getCreatedAtFormatted(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="statut-badge <?= $status_badges[$t->getStatusId()] ?? '' ?>">
                                    <span class="point"></span><?= htmlspecialchars($statut, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="col-actions">
                                <a class="btn-row-action" href="admin_ticket_detail.php?id=<?= $t->getId() ?>" aria-label="Voir le ticket">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
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