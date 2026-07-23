<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "public/includes/db.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? '';
$productId = isset($_POST['id']) ? (int) $_POST['id'] : 0;

$productDAO = new ProductDAO($pdo);

switch ($action) {
    case 'add':
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

        if ($productId > 0 && $quantity > 0) {
            $product = $productDAO->find($productId);

            if ($product !== null && $product->isStatus()) {
                $stock = $product->getQuantity();
                $currentQty = $_SESSION['cart'][$productId] ?? 0;
                $newQty = $currentQty + $quantity;

                if ($stock <= 0) {
                    $_SESSION['cart_message'] = 'Ce produit n\'est plus en stock.';
                } elseif ($newQty > $stock) {
                    $_SESSION['cart'][$productId] = $stock;
                    $_SESSION['cart_message'] = 'Stock limité : vous ne pouvez pas ajouter plus de ' . $stock . ' exemplaire(s) de "' . htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8') . '".';
                } else {
                    $_SESSION['cart'][$productId] = $newQty;
                }
            }
        }
        break;

    case 'update':
        $delta = isset($_POST['delta']) ? (int) $_POST['delta'] : 0;

        if (isset($_SESSION['cart'][$productId])) {
            $product = $productDAO->find($productId);

            if ($product === null || !$product->isStatus()) {
                unset($_SESSION['cart'][$productId]);
                break;
            }

            $stock = $product->getQuantity();
            $wantedQty = $_SESSION['cart'][$productId] + $delta;

            if ($delta > 0 && $wantedQty > $stock) {
                $_SESSION['cart'][$productId] = $stock;
                $_SESSION['cart_message'] = 'Stock limité : maximum ' . $stock . ' exemplaire(s) de "' . htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8') . '" disponible(s).';
            } else {
                $_SESSION['cart'][$productId] = $wantedQty;
            }

            if ($_SESSION['cart'][$productId] <= 0) {
                unset($_SESSION['cart'][$productId]);
            }
        }
        break;

    case 'remove':
        unset($_SESSION['cart'][$productId]);
        break;
}



$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    // Reconstruction du panier (même logique que dans header.php)
    $cartItems = [];
    $cartTotal = 0;
    $cartCount = 0;

    if (!empty($_SESSION['cart'])) {
        $cartProducts = $productDAO->findByIds(array_keys($_SESSION['cart']));

        $pictureStatement = $pdo->prepare('SELECT picture_path FROM pictures WHERE product_id = :id LIMIT 1');
        $defaultImage = 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg';

        $promotions = [];
        foreach ($pdo->query("SELECT product_id, promotion_percent FROM promotions WHERE promotion_is_active = 1") as $row) {
            $promotions[(int) $row['product_id']] = (int) $row['promotion_percent'];
        }

        foreach ($cartProducts as $cartProduct) {
            $pId = $cartProduct->getId();
            $qty = $_SESSION['cart'][$pId] ?? 0;
            if ($qty <= 0) {
                continue;
            }

            $pictureStatement->execute(['id' => $pId]);
            $picture = $pictureStatement->fetch();
            $image = $picture !== false ? $picture['picture_path'] : $defaultImage;

            $basePrice = $cartProduct->getSellPrice();
            $unitPrice = $basePrice;
            if (isset($promotions[$pId])) {
                $unitPrice = $basePrice - ($basePrice * ($promotions[$pId] / 100));
            }

            $lineTotal = $unitPrice * $qty;

            $cartItems[] = [
                'id' => $pId,
                'name' => $cartProduct->getName(),
                'price' => $unitPrice,
                'basePrice' => $basePrice,
                'hasPromo' => isset($promotions[$pId]),
                'image' => $image,
                'quantity' => $qty,
                'lineTotal' => $lineTotal,
            ];
            $cartTotal += $lineTotal;
            $cartCount += $qty;
        }
    }

    // Génération du HTML du panier (même rendu que dans header.php)
    $itemsHtml = '';
    if (empty($cartItems)) {
        $itemsHtml = '<p class="text-center text-muted py-4" id="cart-empty-message">Votre panier est vide.</p>';
    } else {
        foreach ($cartItems as $item) {
            $itemsHtml .= '<div class="cart-line d-flex align-items-center mb-3">'
                . '<img src="' . htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') . '" style="width:60px;height:60px;object-fit:cover;" class="me-2">'
                . '<div class="flex-grow-1"><div>' . htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') . '</div>'
                . '<div class="text-muted">' . ($item['hasPromo']
                    ? '<span class="text-decoration-line-through me-1">' . number_format($item['basePrice'], 2, ',', ' ') . ' €</span><span class="fw-semibold text-danger">' . number_format($item['price'], 2, ',', ' ') . ' €</span>'
                    : number_format($item['price'], 2, ',', ' ') . ' €') . '</div></div>'
                . '<form method="post" action="panier_action.php" class="d-inline cart-form">'
                . '<input type="hidden" name="action" value="update"><input type="hidden" name="id" value="' . (int) $item['id'] . '"><input type="hidden" name="delta" value="-1">'
                . '<button type="submit" class="btn btn-sm btn-outline-secondary">-</button></form>'
                . '<span class="mx-2">' . (int) $item['quantity'] . '</span>'
                . '<form method="post" action="panier_action.php" class="d-inline cart-form">'
                . '<input type="hidden" name="action" value="update"><input type="hidden" name="id" value="' . (int) $item['id'] . '"><input type="hidden" name="delta" value="1">'
                . '<button type="submit" class="btn btn-sm btn-outline-secondary">+</button></form>'
                . '<span class="ms-3" style="min-width:70px;text-align:right;">' . number_format($item['lineTotal'], 2, ',', ' ') . ' €</span>'
                . '<form method="post" action="panier_action.php" class="d-inline ms-2 cart-form">'
                . '<input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="' . (int) $item['id'] . '">'
                . '<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form>'
                . '</div>';
        }
    }

    $message = $_SESSION['cart_message'] ?? null;
    unset($_SESSION['cart_message']);

    header('Content-Type: application/json');
    echo json_encode([
        'itemsHtml' => $itemsHtml,
        'total'     => number_format($cartTotal, 2, ',', ' ') . ' €',
        'count'     => $cartCount,
        'message'   => $message,
    ]);
    exit;
}

$redirectTo = $_SERVER['HTTP_REFERER'] ?? 'index.php';
$_SESSION['open_cart'] = true;
header('Location: ' . $redirectTo);
exit;
