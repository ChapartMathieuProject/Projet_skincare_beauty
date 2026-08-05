<?php
require_once 'public/includes/auth.php';

if (!is_logged()) {
    header('Location: login.php');
    exit;
}

require_once 'public/includes/db.php';

$customerStatement = $pdo->prepare(
    'SELECT customer_id_account FROM customers WHERE user_id = :userId'
);
$customerStatement->execute(['userId' => $_SESSION['user_id']]);
$customer = $customerStatement->fetch();

$tickets = [];

if ($customer !== false) {
    // Dictionnaires (sans JOIN)
    $returnTypes = [];
    foreach ($pdo->query('SELECT return_type_id, return_type_name FROM return_types') as $row) {
        $returnTypes[$row['return_type_id']] = $row['return_type_name'];
    }

    $ticketStatuses = [];
    foreach ($pdo->query('SELECT ticket_status_id, ticket_status_name FROM ticket_status') as $row) {
        $ticketStatuses[$row['ticket_status_id']] = $row['ticket_status_name'];
    }

    // Commandes du client : order_id => order_number
    $orderNumbers = [];
    $ordersStatement = $pdo->prepare(
        'SELECT order_id, order_number FROM orders WHERE customer_id_account = :customerIdAccount'
    );
    $ordersStatement->execute(['customerIdAccount' => $customer['customer_id_account']]);
    foreach ($ordersStatement->fetchAll() as $row) {
        $orderNumbers[$row['order_id']] = $row['order_number'];
    }

    // Tickets rattachés aux commandes du client
    if (!empty($orderNumbers)) {
        $placeholders = implode(',', array_fill(0, count($orderNumbers), '?'));
        $ticketsStatement = $pdo->prepare(
            "SELECT * FROM tickets WHERE order_id IN ($placeholders) ORDER BY ticket_id DESC"
        );
        $ticketsStatement->execute(array_keys($orderNumbers));
        $tickets = $ticketsStatement->fetchAll();
    }
}

// Réutilisation des badges commandes existants
$statusBadges = [
    1 => 'transit',    // Ouvert
    2 => 'transit',    // En cours
    3 => 'delivered',  // Clôturé
    4 => 'cancelled',  // Refusé
];

require_once 'public/includes/header.php';
?>

<main class="profile-main">
    <div class="container">

        <nav aria-label="Fil d'Ariane" class="profile-breadcrumb">
            <a href="users.php">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Mon profil
            </a>
        </nav>

        <h1 class="profile-page-title">
            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
            Mes retours
        </h1>

        <?php if (isset($_GET['created'])): ?>
            <div class="profile-alert profile-alert--success" role="alert">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                Votre demande de retour <strong><?= htmlspecialchars($_GET['created'], ENT_QUOTES, 'UTF-8') ?></strong>
                a bien été envoyée. Notre service SAV va l'examiner.
            </div>
        <?php endif; ?>

        <?php if (empty($tickets)): ?>
            <div class="profile-empty">
                <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                <p>Vous n'avez aucune demande de retour.</p>
                <a href="orders.php" class="btn-rose">Voir mes commandes</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="order-card">
                        <div class="order-card__header">
                            <div>
                                <span class="order-card__id">
                                    Retour <?= htmlspecialchars($ticket['ticket_return_number'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="order-card__date">
                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($ticket['ticket_created_at'])), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <span class="order-badge order-badge--<?= $statusBadges[$ticket['ticket_status_id']] ?? 'transit' ?>">
                                <?= htmlspecialchars($ticketStatuses[$ticket['ticket_status_id']] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        <div class="order-card__body">
                            <span>
                                <i class="fa-solid fa-box" aria-hidden="true"></i>
                                Commande #<?= htmlspecialchars($orderNumbers[$ticket['order_id']] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span>
                                <?= htmlspecialchars($returnTypes[$ticket['return_type_id']] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include 'public/includes/footer.php'; ?>