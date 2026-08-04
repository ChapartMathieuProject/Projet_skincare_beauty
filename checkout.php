<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'public/includes/db.php';

// Coût de livraison fixe pour l'instant (pas de colonne prix dans delivery_types).
// TODO (CB) : ajouter une colonne delivery_type_cost dans delivery_types si besoin de tarifs variables.
const DELIVERY_COST = 4.90;
const FREE_DELIVERY_THRESHOLD = 50;

// On récupère la fiche client liée à l'utilisateur connecté.
$customerStatement = $pdo->prepare('SELECT * FROM customers WHERE user_id = :userId');
$customerStatement->execute(['userId' => $_SESSION['user_id']]);
$customer = $customerStatement->fetch();

if ($customer === false) {
    // Un admin (sans fiche customer) ne peut pas commander.
    header('Location: index.php');
    exit;
}

$customerId = $customer['customer_id_account'];

// Adresse par défaut du client.
$addressStatement = $pdo->prepare(
    'SELECT * FROM addresses WHERE customer_id_account = :customerId
     ORDER BY address_is_default DESC, address_id DESC
     LIMIT 1'
);
$addressStatement->execute(['customerId' => $customerId]);
$address = $addressStatement->fetch();

if ($address === false) {
    // Vraiment aucune adresse en base : redirection vers la page de création.
    header('Location: addresses.php?error=no_address');
    exit;
}

$deliveryTypes = $pdo->query('SELECT * FROM delivery_types')->fetchAll();
$paymentTypes = $pdo->query('SELECT * FROM payement_types')->fetchAll();

// Contenu du panier (session), avec prix recalculé (achat + marge).
$cartItems = [];
$cartTotal = 0;

if (!empty($_SESSION['cart'])) {
    $productStatement = $pdo->prepare(
        'SELECT product_id, product_name, product_buy_price, product_margin FROM products WHERE product_id = :id'
    );
    $promotionStatement = $pdo->prepare(
        'SELECT promotion_percent FROM promotions
         WHERE product_id = :id AND promotion_is_active = 1'
    );

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $productStatement->execute(['id' => $productId]);
        $product = $productStatement->fetch();

        if ($product === false) {
            continue;
        }

        $price = $product['product_buy_price'] * (1 + $product['product_margin'] / 100);

        // On applique la promotion active au prix unitaire, si elle existe.
        $promotionStatement->execute(['id' => $productId]);
        $promotion = $promotionStatement->fetch();

        if ($promotion !== false) {
            $price = $price * (1 - $promotion['promotion_percent'] / 100);
        }

        $cartItems[] = [
            'id' => $product['product_id'],
            'name' => $product['product_name'],
            'price' => $price,
            'quantity' => $quantity,
        ];

        $cartTotal += $price * $quantity;
        $deliveryCost = ($cartTotal >= FREE_DELIVERY_THRESHOLD) ? 0 : DELIVERY_COST;
    }
}

// Traitement de la validation de commande.
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($cartItems)) {
    $deliveryTypeId = (int) $_POST['delivery_type_id'];
    $paymentTypeId = (int) $_POST['payment_type_id'];

    try {
        $pdo->beginTransaction();

        // 1. Création de la livraison.
        $deliveryNumber = 'LIV-' . strtoupper(uniqid());
        $trackingNumber = 'TRACK-' . strtoupper(uniqid());
        $estimatedDeliveryDate = date('Y-m-d H:i:s', strtotime('+3 days'));

        $insertDelivery = $pdo->prepare(
            'INSERT INTO deliveries
                (delivery_number, delivery_cost, delivery_tracking_number, delivery_date, customer_id_account, address_id, delivery_type_id)
             VALUES
                (:number, :cost, :tracking, :date, :customerId, :addressId, :deliveryTypeId)'
        );
        $insertDelivery->execute([
            'number' => $deliveryNumber,
            'cost' => $deliveryCost,
            'tracking' => $trackingNumber,
            'date' => $estimatedDeliveryDate,
            'customerId' => $customerId,
            'addressId' => $address['address_id'],
            'deliveryTypeId' => $deliveryTypeId,
        ]);
        $deliveryId = (int) $pdo->lastInsertId();

        // 2. Statut initial de commande ("En cours").
        $statusStatement = $pdo->prepare("SELECT order_type_id FROM order_status WHERE order_type_name = 'En cours' LIMIT 1");
        $statusStatement->execute();
        $status = $statusStatement->fetch();
        $orderTypeId = $status !== false ? $status['order_type_id'] : 1;

        // 3. Entreprise (une seule entreprise gère la boutique).
        $company = $pdo->query('SELECT company_id_account FROM companies LIMIT 1')->fetch();
        $companyId = $company['company_id_account'];

        // 4. Création de la commande.
        $orderNumber = 'CMD-' . strtoupper(uniqid());

        $insertOrder = $pdo->prepare(
            'INSERT INTO orders
                (order_number, order_date, order_type_id, payment_type_id, company_id_account, customer_id_account, delivery_type_id, deliveries_id)
             VALUES
                (:number, NOW(), :orderTypeId, :paymentTypeId, :companyId, :customerId, :deliveryTypeId, :deliveryId)'
        );
        $insertOrder->execute([
            'number' => $orderNumber,
            'orderTypeId' => $orderTypeId,
            'paymentTypeId' => $paymentTypeId,
            'companyId' => $companyId,
            'customerId' => $customerId,
            'deliveryTypeId' => $deliveryTypeId,
            'deliveryId' => $deliveryId,
        ]);
        $orderId = (int) $pdo->lastInsertId();

        // 5. Lignes de commande (un produit + sa quantité).
        // 5. Lignes de commande (un produit + sa quantité + le prix unitaire payé).
        $insertLine = $pdo->prepare(
            'INSERT INTO contains (order_id, product_id, contains_quantity, contains_unit_price)
     VALUES (:orderId, :productId, :quantity, :unitPrice)'
        );
        foreach ($cartItems as $item) {

            $insertLine->execute([
                'orderId' => $orderId,
                'productId' => $item['id'],
                'quantity' => $item['quantity'],
                'unitPrice' => $item['price'],
            ]);
        }

        $pdo->commit();


        $orderTotal = 0.0;
        foreach ($cartItems as $item) {
            $orderTotal += $item['price'] * $item['quantity'];
        }

        try {
            require_once 'public/includes/Mailer.php';

         $loyaltyService = new LoyaltyService(
                new LoyaltyPointDAO($pdo),
                new LoyaltyTierDAO($pdo),
                new LoyaltyVoucherDAO($pdo),
                $mailer,
                $pdo
            );

            $pointsEarned = $loyaltyService->addPointsForOrder(
                $customerId,
                $orderId,
                $orderTotal,
                $_SESSION['user_mail'] ?? null
            );

            $_SESSION['loyalty_points_earned'] = $pointsEarned;
        } catch (Throwable $e) {
            error_log('Fidelite : echec attribution points commande ' . $orderId . ' - ' . $e->getMessage());
        }

        $_SESSION['cart'] = [];

        header('Location: orders.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $errorMessage = 'Erreur : ' . $e->getMessage();
    }
}

require_once 'public/includes/header.php';
?>

<main class="container py-5">
    <h1 class="h3 mb-4">Valider ma commande</h1>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">Votre panier est vide.</div>
        <a href="index.php" class="btn-rose">Continuer mes achats</a>

    <?php else: ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <h2 class="h5 mb-3">Récapitulatif</h2>
                <?php foreach ($cartItems as $item): ?>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                        <span><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> €</span>
                    </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between fw-bold pt-3">
                    <span>Total produits</span>
                    <span><?= number_format($cartTotal, 2, ',', ' ') ?> €</span>
                </div>

                <h2 class="h5 mt-4 mb-3">Adresse de livraison</h2>
                <p>
                    <?= htmlspecialchars($address['address_firstname'] . ' ' . $address['address_name']) ?><br>
                    <?= htmlspecialchars($address['address_adress_1']) ?><br>
                    <?= htmlspecialchars($address['address_postcode'] . ' ' . $address['address_city']) ?><br>
                    <?= htmlspecialchars($address['address_country']) ?>
                </p>
            </div>

            <div class="col-lg-5">
                <form method="post" class="border rounded p-4">
                    <div class="mb-3">
                        <label for="delivery_type_id" class="form-label">Mode de livraison</label>
                        <select name="delivery_type_id" id="delivery_type_id" class="form-select" required>
                            <?php foreach ($deliveryTypes as $type): ?>
                                <option value="<?= $type['delivery_type_id'] ?>">
                                    <?= htmlspecialchars($type['delivery_type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="payment_type_id" class="form-label">Mode de paiement</label>
                        <select name="payment_type_id" id="payment_type_id" class="form-select" required>
                            <?php foreach ($paymentTypes as $type): ?>
                                <option value="<?= $type['payement_type_id'] ?>">
                                    <?= htmlspecialchars($type['payement_type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Frais de livraison</span>
                        <span>
                            <?php if ($deliveryCost === 0): ?>
                                <span class="text-success fw-semibold">Offerte</span>
                            <?php else: ?>
                                <?= number_format($deliveryCost, 2, ',', ' ') ?> €
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($deliveryCost > 0): ?>
                        <p class="small text-muted mb-3">
                            Plus que <?= number_format(FREE_DELIVERY_THRESHOLD - $cartTotal, 2, ',', ' ') ?> € d'achat pour la livraison offerte !
                        </p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between fw-bold mb-4">
                        <span>Total à payer</span>
                        <span><?= number_format($cartTotal + $deliveryCost, 2, ',', ' ') ?> €</span>
                    </div>

                    <button type="submit" class="btn-rose w-100">Valider ma commande</button>
                </form>
            </div>
        </div>

    <?php endif; ?>
</main>

<?php require_once 'public/includes/footer.php'; ?>