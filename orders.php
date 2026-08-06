<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'public/includes/db.php';


$customerStatement = $pdo->prepare(
    'SELECT customer_id_account FROM customers WHERE user_id = :userId'
);
$customerStatement->execute(['userId' => $_SESSION['user_id']]);
$customer = $customerStatement->fetch();

$orders = [];


if ($customer !== false) {
    $customerIdAccount = $customer['customer_id_account'];

    
    $orderStatuses = [];
    foreach ($pdo->query('SELECT order_type_id, order_type_name FROM order_status') as $row) {
        $orderStatuses[$row['order_type_id']] = $row['order_type_name'];
    }

    // Module SAV : dernier ticket de retour par commande
    $ticketStatuses = [];
    foreach ($pdo->query('SELECT ticket_status_id, ticket_status_name FROM ticket_status') as $row) {
        $ticketStatuses[$row['ticket_status_id']] = $row['ticket_status_name'];
    }

    $ticketsByOrder = [];
    foreach ($pdo->query('SELECT order_id, ticket_return_number, ticket_status_id FROM tickets ORDER BY ticket_id') as $row) {
        $ticketsByOrder[$row['order_id']] = $row; // le plus récent écrase le précédent
    }

    // 1bis. Dictionnaire des images produits (même logique que product.php).
    $pictures = [];
    foreach ($pdo->query('SELECT product_id, picture_path FROM pictures') as $row) {
        if (!isset($pictures[$row['product_id']])) {
            $pictures[$row['product_id']] = $row['picture_path'];
        }
    }
    $defaultImage = 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg';

    
    $deliveryCosts = [];
    foreach ($pdo->query('SELECT delivery_id, delivery_cost FROM deliveries') as $row) {
        $deliveryCosts[$row['delivery_id']] = $row['delivery_cost'];
    }

    
    $ordersStatement = $pdo->prepare(
        'SELECT o.order_id, o.order_number, o.order_date, o.order_type_id,
                o.order_discount, o.deliveries_id
         FROM orders o
         WHERE o.customer_id_account = :customerIdAccount
         ORDER BY o.order_date DESC'
    );
    $ordersStatement->execute(['customerIdAccount' => $customerIdAccount]);
    $ordersFromDatabase = $ordersStatement->fetchAll();

    
    $linesStatement = $pdo->prepare(
        'SELECT product_id, contains_quantity, contains_unit_price FROM contains WHERE order_id = :orderId'
    );
    $productStatement = $pdo->prepare(
        'SELECT product_name FROM products WHERE product_id = :productId'
    );

    foreach ($ordersFromDatabase as $orderRow) {
        $linesStatement->execute(['orderId' => $orderRow['order_id']]);
        $lines = $linesStatement->fetchAll();


        $total = 0; 
        $itemsCount = 0;
        $orderLines = []; 

        foreach ($lines as $line) {
            $productStatement->execute(['productId' => $line['product_id']]);
            $product = $productStatement->fetch();

            if ($product === false) {
                continue; 
            }

            
            $lineTotal = $line['contains_unit_price'] * $line['contains_quantity'];

            $total += $lineTotal;
            $itemsCount += $line['contains_quantity'];

            $orderLines[] = [
                'quantity'  => $line['contains_quantity'],
                'name'      => $product['product_name'],
                'lineTotal' => number_format($lineTotal, 2, ',', ' ') . ' €',
                'image'     => $pictures[$line['product_id']] ?? $defaultImage,
            ];
        }

        
        $shippingCost = (float) ($deliveryCosts[$orderRow['deliveries_id']] ?? 0.0);

        $voucherDiscount = (float) $orderRow['order_discount'];

        
        $grandTotal = max(0.0, $total + $shippingCost - $voucherDiscount);

    $orders[] = [
        'id'             => $orderRow['order_number'],
        'orderId'        => $orderRow['order_id'],
        'date'           => date('d F Y', strtotime($orderRow['order_date'])),
        'status'         => $orderStatuses[$orderRow['order_type_id']] ?? 'Statut inconnu',
        'total'          => number_format($grandTotal, 2, ',', ' ') . ' €', // <-- inclut les FDP
        'items'          => $itemsCount,
        'lines'          => $orderLines,
        'shippingCost'   => $shippingCost,
        'ticket'         => $ticketsByOrder[$orderRow['order_id']] ?? null,
        'voucherDiscount' => $voucherDiscount,
    ];
    }
}


require_once 'public/includes/header.php';
?>

<main class="profile-main">
    <div class="container">

        <nav aria-label="Fil d'Ariane" class="profile-breadcrumb">
            <a href="/users.php">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Mon profil
            </a>
        </nav>

        <h1 class="profile-page-title">
            <i class="fa-solid fa-box" aria-hidden="true"></i>
            Vos Commandes
        </h1>

        <?php if (!empty($_SESSION['loyalty_points_earned'])): ?>
            <div class="alert alert-success">
                Vous avez gagné <?= (int) $_SESSION['loyalty_points_earned'] ?> points de fidélité.
            </div>
            <?php unset($_SESSION['loyalty_points_earned']); ?>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="profile-empty">
                <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                <p>Vous n'avez pas encore passé de commande.</p>
                <a href="index.php" class="btn-rose">Découvrir la collection</a>
            </div>

        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <?php $detailId = 'order-detail-' . $order['orderId']; ?>
                    <div class="order-card">
                        <div class="order-card__header">
                            <div>
                                <span class="order-card__id">Commande #<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="order-card__date">
                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    <?= htmlspecialchars($order['date'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <?php
                            $badgeClass = match ($order['status']) {
                                'Expédié' => 'delivered',
                                'Annulé'  => 'cancelled',
                                default   => 'transit', // 'En cours'
                            };
                            ?>
                            <span class="order-badge order-badge--<?= $badgeClass ?>">
                                <?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        <div class="order-card__body">
                            <span>
                                <i class="fa-solid fa-cubes-stacked" aria-hidden="true"></i>
                                <?= (int) $order['items'] ?> article<?= $order['items'] > 1 ? 's' : '' ?>
                            </span>
                            <span class="order-card__total">
                                <?= htmlspecialchars($order['total'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <button
                                type="button"
                                class="order-card__link"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= $detailId ?>"
                                aria-expanded="false"
                                aria-controls="<?= $detailId ?>">
                                Voir le détail
                                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>

                        <!-- Zone détaillée, masquée par défaut, ouverte via Bootstrap collapse -->
                        <div class="collapse order-card__detail" id="<?= $detailId ?>">
                            <ul class="order-detail__lines">
                                <?php foreach ($order['lines'] as $line): ?>
                                    <li class="order-detail__line">
                                        <img
                                            src="<?= htmlspecialchars($line['image'], ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($line['name'], ENT_QUOTES, 'UTF-8') ?>"
                                            class="order-detail__line-image"
                                            loading="lazy">
                                        <span class="order-detail__line-info">
                                            <?= (int) $line['quantity'] ?> x
                                            <?= htmlspecialchars($line['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span class="order-detail__line-total">
                                            <?= htmlspecialchars($line['lineTotal'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="order-detail__shipping">
                                <?php if ($order['status'] === 'Expédié'): ?>
                                    <div class="order-detail__return" style="margin-top: .75rem;">
                                        <?php $ticket = $order['ticket']; ?>
                                        <?php if ($ticket === null || (int) $ticket['ticket_status_id'] === Ticket::STATUS_REFUSE): ?>
                                            <a class="btn-rose-sm" href="return_request.php?order=<?= (int) $order['orderId'] ?>">
                                                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                                                Demander un retour
                                            </a>
                                        <?php else: ?>
                                            <span>
                                                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                                                Retour <?= htmlspecialchars($ticket['ticket_return_number'], ENT_QUOTES, 'UTF-8') ?>
                                                — <?= htmlspecialchars($ticketStatuses[$ticket['ticket_status_id']] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                                (<a href="return.php">suivre</a>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($order['shippingCost'] > 0): ?>
                                    Frais de port :
                                    <?= number_format($order['shippingCost'], 2, ',', ' ') ?> €
                                <?php else: ?>
                                    Frais de port : <strong>Offerts</strong>
                                <?php endif; ?>
                            </div>

                            <?php if ($order['voucherDiscount'] > 0): ?>
                                <div class="order-detail__shipping">
                                    Remise (palier / bon) :
                                    -<?= number_format($order['voucherDiscount'], 2, ',', ' ') ?> €
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'public/includes/footer.php'; ?>