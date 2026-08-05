<?php
require_once "public/includes/db.php";
require_once "public/includes/auth.php";
require_sav();

$menu_actif = 'sav';

$ticketDAO  = new TicketDAO($pdo);
$historyDAO = new TicketHistoryDAO($pdo);

$errorMessage = '';

$ticket_id = (int) ($_GET['id'] ?? $_POST['ticket_id'] ?? 0);
$ticket    = $ticket_id > 0 ? $ticketDAO->find($ticket_id) : null;

if ($ticket === null) {
    header('Location: admin_tickets.php');
    exit;
}

$stmt = $pdo->prepare("SELECT order_id, order_number, order_date, customer_id_account FROM orders WHERE order_id = :oid");
$stmt->execute([':oid' => $ticket->getOrderId()]);
$order = $stmt->fetch() ?: null;

$client_name = '—';
$client_mail = '';
if ($order) {
    $stmt = $pdo->prepare("SELECT customer_name, customer_firstname, user_id FROM customers WHERE customer_id_account = :cid");
    $stmt->execute([':cid' => $order['customer_id_account']]);
    if ($c = $stmt->fetch()) {
        $client_name = $c['customer_firstname'] . ' ' . $c['customer_name'];
        $stmt = $pdo->prepare("SELECT user_mail FROM users WHERE user_id = :uid");
        $stmt->execute([':uid' => $c['user_id']]);
        $client_mail = $stmt->fetchColumn() ?: '';
    }
}

$agent_id   = (int) $_SESSION['user_id'];
$agent_name = $_SESSION['user_name'] ?? $_SESSION['user_mail'] ?? 'Agent SAV';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'validate_ticket') {

    if ($ticket->getStatusId() !== Ticket::STATUS_OUVERT) {
        $errorMessage = 'Cette demande ne peut pas être validée (statut actuel invalide).';
    } else {
        try {
            $pdo->beginTransaction();

            $ticketDAO->updateStatus($ticket->getId(), Ticket::STATUS_EN_COURS);
            $historyDAO->log($ticket->getId(), $agent_id,
                "Demande de retour validée par $agent_name — ticket passé « En cours »");

            $pdo->commit();

            if ($client_mail !== '' && $order) {
                $mailer = new MailService();
                $sent   = $mailer->sendReturnInstructions(
                    $client_mail, $client_name,
                    $ticket->getReturnNumber(), $order['order_number']
                );
                $historyDAO->log($ticket->getId(), $agent_id, $sent
                    ? "E-mail d'instructions envoyé à $client_mail"
                    : "Échec de l'envoi de l'e-mail à $client_mail (" . $mailer->getLastError() . ")");
            }

            header('Location: admin_ticket_detail.php?id=' . $ticket->getId() . '&validated=1');
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMessage = 'Erreur lors de la validation : ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'refuse_ticket') {

    if ($ticket->getStatusId() !== Ticket::STATUS_OUVERT) {
        $errorMessage = 'Cette demande ne peut pas être refusée (statut actuel invalide).';
    } else {
        try {
            $pdo->beginTransaction();

            $ticketDAO->updateStatus($ticket->getId(), Ticket::STATUS_REFUSE);
            $historyDAO->log($ticket->getId(), $agent_id,
                "Demande de retour refusée par $agent_name");

            $pdo->commit();

            if ($client_mail !== '' && $order) {
                $mailer = new MailService();
                $sent   = $mailer->sendReturnRefused(
                    $client_mail, $client_name,
                    $ticket->getReturnNumber(), $order['order_number']
                );
                $historyDAO->log($ticket->getId(), $agent_id, $sent
                    ? "E-mail de refus envoyé à $client_mail"
                    : "Échec de l'envoi de l'e-mail à $client_mail (" . $mailer->getLastError() . ")");
            }

            header('Location: admin_ticket_detail.php?id=' . $ticket->getId() . '&refused=1');
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMessage = 'Erreur lors du refus : ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm_reception') {

    if ($ticket->getStatusId() !== Ticket::STATUS_EN_COURS) {
        $errorMessage = 'Ce ticket ne peut pas être clôturé (statut actuel invalide).';
    } else {
        try {
            $pdo->beginTransaction();

            $ticketDAO->updateStatus($ticket->getId(), Ticket::STATUS_CLOTURE);
            $historyDAO->log($ticket->getId(), $agent_id,
                "Réception du colis confirmée — modification effectuée sur l'expédition par $agent_name — ticket clôturé");

            $pdo->commit();

            header('Location: admin_ticket_detail.php?id=' . $ticket->getId() . '&closed=1');
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMessage = 'Erreur lors de la clôture : ' . $e->getMessage();
        }
    }
}

$ticket_statuses = [];
foreach ($pdo->query("SELECT ticket_status_id, ticket_status_name FROM ticket_status") as $s) {
    $ticket_statuses[$s['ticket_status_id']] = $s['ticket_status_name'];
}

$return_types = [];
foreach ($pdo->query("SELECT return_type_id, return_type_name FROM return_types") as $t) {
    $return_types[$t['return_type_id']] = $t['return_type_name'];
}

$history = $historyDAO->findByTicketId($ticket->getId());

$status_badges = [
    Ticket::STATUS_OUVERT   => 'statut-attente',
    Ticket::STATUS_EN_COURS => 'statut-preparation',
    Ticket::STATUS_CLOTURE  => 'statut-livree',
    Ticket::STATUS_REFUSE   => 'statut-expiree',
];
$statut_name = $ticket_statuses[$ticket->getStatusId()] ?? '—';
?>
<?php include "public/includes/header_admin.php"; ?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">›</span>
            <a href="admin_tickets.php">SAV — Retours</a> <span class="sep">›</span>
            <span class="current"><?= htmlspecialchars($ticket->getReturnNumber(), ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <h1>Ticket <?= htmlspecialchars($ticket->getReturnNumber(), ENT_QUOTES, 'UTF-8') ?></h1>
    </header>

    <?php if (isset($_GET['validated'])): ?>
        <div class="alert alert-success" role="alert">
            Demande <strong>validée</strong> — le client a reçu les instructions de renvoi par e-mail.
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['refused'])): ?>
        <div class="alert alert-success" role="alert">
            Demande <strong>refusée</strong> — le client a été informé par e-mail.
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['closed'])): ?>
        <div class="alert alert-success" role="alert">
            Réception confirmée — le ticket est <strong>clôturé</strong>.
        </div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <h2>Informations du retour</h2>
        <p>
            <strong>Statut :</strong>
            <span class="statut-badge <?= $status_badges[$ticket->getStatusId()] ?? '' ?>">
                <span class="point"></span><?= htmlspecialchars($statut_name, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </p>
        <p><strong>Type de retour :</strong>
            <?= htmlspecialchars($return_types[$ticket->getReturnTypeId()] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Créé le :</strong>
            <?= htmlspecialchars($ticket->getCreatedAtFormatted(), ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Commentaire :</strong>
            <?= nl2br(htmlspecialchars($ticket->getComment(), ENT_QUOTES, 'UTF-8')) ?></p>
    </section>

    <section class="admin-card">
        <h2>Commande liée</h2>
        <?php if ($order): ?>
            <p><strong>Numéro :</strong>
                <a href="admin_order_detail.php?id=<?= (int) $order['order_id'] ?>">
                    <?= htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8') ?>
                </a></p>
            <p><strong>Client :</strong> <?= htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') ?>
                (<?= htmlspecialchars($client_mail !== '' ? $client_mail : '—', ENT_QUOTES, 'UTF-8') ?>)</p>
            <p><strong>Passée le :</strong>
                <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($order['order_date'])), ENT_QUOTES, 'UTF-8') ?></p>
        <?php else: ?>
            <p>Commande introuvable.</p>
        <?php endif; ?>
    </section>

    <?php if ($ticket->getStatusId() === Ticket::STATUS_OUVERT): ?>
        <section class="admin-card">
            <h2>Décision sur la demande</h2>
            <p>Examinez la demande du client, puis validez (le client recevra les instructions de renvoi)
               ou refusez (le client sera informé par e-mail).</p>
            <div style="display: flex; gap: .75rem;">
                <form method="POST" action="admin_ticket_detail.php?id=<?= $ticket->getId() ?>">
                    <input type="hidden" name="action" value="validate_ticket">
                    <input type="hidden" name="ticket_id" value="<?= $ticket->getId() ?>">
                    <button type="submit" class="btn-publish">Valider la demande</button>
                </form>
                <form method="POST" action="admin_ticket_detail.php?id=<?= $ticket->getId() ?>"
                      onsubmit="return confirm('Refuser définitivement cette demande de retour ?');">
                    <input type="hidden" name="action" value="refuse_ticket">
                    <input type="hidden" name="ticket_id" value="<?= $ticket->getId() ?>">
                    <button type="submit" class="btn-draft">Refuser la demande</button>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($ticket->getStatusId() === Ticket::STATUS_EN_COURS): ?>
        <section class="admin-card">
            <h2>Contrôle du colis</h2>
            <p>Après contrôle (pas de détérioration du produit, adresse correcte), confirmez la réception pour clôturer le ticket.</p>
            <form method="POST" action="admin_ticket_detail.php?id=<?= $ticket->getId() ?>">
                <input type="hidden" name="action" value="confirm_reception">
                <input type="hidden" name="ticket_id" value="<?= $ticket->getId() ?>">
                <button type="submit" class="btn-publish">Confirmer la réception</button>
            </form>
        </section>
    <?php endif; ?>

    <section class="admin-card">
        <h2>Historique du ticket</h2>
        <?php if (empty($history)): ?>
            <p>Aucune action enregistrée.</p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?= htmlspecialchars($h->getCreatedAtFormatted(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($h->getAction(), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>

</body>
</html>