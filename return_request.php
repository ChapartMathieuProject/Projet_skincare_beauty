<?php
require_once 'public/includes/auth.php';

if (!is_logged()) {
    header('Location: login.php');
    exit;
}

require_once 'public/includes/db.php';

// Fiche client du compte connecté (un admin/agent n'en a pas forcément)
$customerStatement = $pdo->prepare(
    'SELECT customer_id_account, customer_name, customer_firstname FROM customers WHERE user_id = :userId'
);
$customerStatement->execute(['userId' => $_SESSION['user_id']]);
$customer = $customerStatement->fetch();

if ($customer === false) {
    header('Location: users.php');
    exit;
}

// Dictionnaires (sans JOIN)
$orderStatuses = [];
foreach ($pdo->query('SELECT order_type_id, order_type_name FROM order_status') as $row) {
    $orderStatuses[$row['order_type_id']] = $row['order_type_name'];
}

$returnTypes = [];
foreach ($pdo->query('SELECT return_type_id, return_type_name FROM return_types') as $row) {
    $returnTypes[$row['return_type_id']] = $row['return_type_name'];
}

// Libellés grand public mappés sur les 3 types imposés par le cahier des charges
$publicLabels = [
    'NPAI'               => "Mon colis est revenu à l'expéditeur (adresse inconnue)",
    'Adresse incomplète' => "Mon adresse de livraison était incomplète ou erronée",
    'Colis non réclamé'  => "Je n'ai pas pu récupérer mon colis dans les délais",
];

// Commande concernée : doit appartenir au client connecté et être Expédié
$orderId = (int) ($_GET['order'] ?? $_POST['order_id'] ?? 0);

$orderStatement = $pdo->prepare(
    'SELECT order_id, order_number, order_type_id
     FROM orders
     WHERE order_id = :orderId AND customer_id_account = :customerIdAccount'
);
$orderStatement->execute([
    'orderId'           => $orderId,
    'customerIdAccount' => $customer['customer_id_account'],
]);
$order = $orderStatement->fetch();

if ($order === false || ($orderStatuses[$order['order_type_id']] ?? '') !== 'Expédié') {
    header('Location: orders.php');
    exit;
}

// Une seule demande active par commande (seul un refus permet de redemander)
$lastTicketStatement = $pdo->prepare(
    'SELECT ticket_status_id FROM tickets WHERE order_id = :orderId ORDER BY ticket_id DESC LIMIT 1'
);
$lastTicketStatement->execute(['orderId' => $orderId]);
$lastStatus = $lastTicketStatement->fetchColumn();

if ($lastStatus !== false && (int) $lastStatus !== Ticket::STATUS_REFUSE) {
    header('Location: return.php');
    exit;
}

$errorMessage = '';

// ── PRG : dépôt de la demande de retour ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_return') {
    $returnTypeId = (int) ($_POST['return_type_id'] ?? 0);
    $comment      = trim($_POST['ticket_comment'] ?? '');

    if (!isset($returnTypes[$returnTypeId])) {
        $errorMessage = 'Veuillez choisir un motif de retour.';
    } elseif ($comment === '' || mb_strlen($comment) > 500) {
        $errorMessage = 'Veuillez décrire votre problème (500 caractères maximum).';
    } else {
        $ticketDAO  = new TicketDAO($pdo);
        $historyDAO = new TicketHistoryDAO($pdo);

        $clientName = $_SESSION['user_name'] ?? ($customer['customer_firstname'] . ' ' . $customer['customer_name']);

        try {
            $pdo->beginTransaction();

            $numero   = $ticketDAO->generateReturnNumber($ticketDAO->getNextSequence());
            $ticketId = $ticketDAO->create(new Ticket([
                'ticket_return_number' => $numero,
                'ticket_comment'       => $comment,
                'order_id'             => $orderId,
                'return_type_id'       => $returnTypeId,
                'ticket_status_id'     => Ticket::STATUS_OUVERT,
                'user_id'              => (int) $_SESSION['user_id'],
            ]));
            $historyDAO->log($ticketId, (int) $_SESSION['user_id'], "Demande de retour créée par $clientName");

            $pdo->commit();

            header('Location: return.php?created=' . urlencode($numero));
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMessage = 'Erreur lors de la création de la demande : ' . $e->getMessage();
        }
    }
}

require_once 'public/includes/header.php';
?>

<main class="profile-main">
    <div class="container">

        <nav aria-label="Fil d'Ariane" class="profile-breadcrumb">
            <a href="orders.php">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Mes commandes
            </a>
        </nav>

        <h1 class="profile-page-title">
            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
            Demander un retour
        </h1>

        <?php if ($errorMessage !== ''): ?>
            <div class="profile-alert profile-alert--error" role="alert">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="security-card">
            <h2 class="security-card__title">
                <i class="fa-solid fa-box" aria-hidden="true"></i>
                Commande #<?= htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8') ?>
            </h2>

            <p>Expliquez-nous ce qui s'est passé avec votre colis : notre service SAV examinera votre demande
               et vous recevrez un e-mail avec les instructions de renvoi si elle est acceptée.</p>

            <form method="POST" action="return_request.php" novalidate>
                <input type="hidden" name="action" value="request_return">
                <input type="hidden" name="order_id" value="<?= (int) $orderId ?>">

                <div class="form-group-profile">
                    <label for="return_type_id" class="form-label-profile">Que s'est-il passé ?</label>
                    <select id="return_type_id" name="return_type_id" class="form-input-profile" required>
                        <option value="">— Choisir un motif —</option>
                        <?php foreach ($returnTypes as $typeId => $typeName): ?>
                            <option value="<?= (int) $typeId ?>">
                                <?= htmlspecialchars($publicLabels[$typeName] ?? $typeName, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group-profile">
                    <label for="ticket_comment" class="form-label-profile">Décrivez votre problème</label>
                    <textarea id="ticket_comment" name="ticket_comment" class="form-input-profile"
                              rows="4" maxlength="500" required
                              placeholder="Ex : le transporteur n'a pas trouvé mon adresse, le colis est reparti."></textarea>
                    <span class="form-hint">500 caractères maximum.</span>
                </div>

                <button type="submit" class="btn-rose-sm">Envoyer ma demande</button>
            </form>
        </div>

    </div>
</main>

<?php include 'public/includes/footer.php'; ?>