<?php
// Protection de la page : accès réservé aux utilisateurs connectés
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'public/includes/db.php';

// Les commandes sont rattachées à un client (customers), pas directement à un
// utilisateur (users). On récupère donc d'abord la fiche client liée.
$customerStatement = $pdo->prepare(
    'SELECT customer_id_account FROM customers WHERE user_id = :userId'
);
$customerStatement->execute(['userId' => $_SESSION['user_id']]);
$customer = $customerStatement->fetch();

$orders = [];

// Un compte administrateur n'a pas de fiche customer : dans ce cas, pas de commandes.
if ($customer !== false) {
    $customerIdAccount = $customer['customer_id_account'];

    // 1. On charge les statuts de commande en dictionnaire (comme dans index.php/product.php).
    $orderStatuses = [];
    foreach ($pdo->query('SELECT order_type_id, order_type_name FROM order_status') as $row) {
        $orderStatuses[$row['order_type_id']] = $row['order_type_name'];
    }

    // 2. On récupère les commandes du client, sans JOIN sur order_status.
    $ordersStatement = $pdo->prepare(
        'SELECT order_id, order_number, order_date, order_type_id
         FROM orders
         WHERE customer_id_account = :customerIdAccount
         ORDER BY order_date DESC'
    );
    $ordersStatement->execute(['customerIdAccount' => $customerIdAccount]);
    $ordersFromDatabase = $ordersStatement->fetchAll();

    // 3. Requêtes préparées réutilisées pour chaque commande (lignes + produit).
    $linesStatement = $pdo->prepare(
        'SELECT product_id, contains_quantity FROM contains WHERE order_id = :orderId'
    );
    $productStatement = $pdo->prepare(
        'SELECT product_buy_price, product_margin FROM products WHERE product_id = :productId'
    );

    foreach ($ordersFromDatabase as $orderRow) {
        $linesStatement->execute(['orderId' => $orderRow['order_id']]);
        $lines = $linesStatement->fetchAll();

        // TODO (CB) : le prix de vente réel au moment de la commande n'est pas
        // stocké en base. On le recalcule ici depuis le prix d'achat + la marge,
        // ce qui n'est pas fiable si le prix ou la marge changent après coup.
        $total = 0;
        $itemsCount = 0;

        foreach ($lines as $line) {
            $productStatement->execute(['productId' => $line['product_id']]);
            $product = $productStatement->fetch();

            if ($product === false) {
                continue; // produit supprimé entre-temps
            }

            $sellingPrice = $product['product_buy_price'] * (1 + $product['product_margin'] / 100);
            $total += $sellingPrice * $line['contains_quantity'];
            $itemsCount += $line['contains_quantity'];
        }

        $orders[] = [
            'id'     => $orderRow['order_number'],
            'date'   => date('d F Y', strtotime($orderRow['order_date'])),
            'status' => $orderStatuses[$orderRow['order_type_id']] ?? 'Statut inconnu',
            'total'  => number_format($total, 2, ',', ' ') . ' €',
            'items'  => $itemsCount,
        ];
    }
}


require_once 'public/includes/header.php';
?>

<main class="profile-main">
    <div class="container">

        <nav aria-label="Fil d'Ariane" class="profile-breadcrumb">
            <a href="profile.php">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Mon profil
            </a>
        </nav>

        <h1 class="profile-page-title">
            <i class="fa-solid fa-box" aria-hidden="true"></i>
            Vos Commandes
        </h1>

        <?php if (empty($orders)): ?>
            <div class="profile-empty">
                <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                <p>Vous n'avez pas encore passé de commande.</p>
                <a href="index.php" class="btn-rose">Découvrir la collection</a>
            </div>

        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
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
                            <a href="#" class="order-card__link">
                                Voir le détail
                                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'public/includes/footer.php'; ?>