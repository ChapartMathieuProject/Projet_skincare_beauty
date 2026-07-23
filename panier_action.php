<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "public/includes/db.php"; // adapte le chemin selon ton arborescence réelle
require_once __DIR__ . '/public/includes/classes/DAO/DAO.php';
require_once __DIR__ . '/public/includes/classes/DAO/ProductDAO.php';

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
                    $_SESSION['cart_message'] = 'Stock limité : vous ne pouvez pas ajouter plus de ' . $stock . ' exemplaire(s) de "' . $product->getName() . '".';
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
                $_SESSION['cart_message'] = 'Stock limité : maximum ' . $stock . ' exemplaire(s) de "' . $product->getName() . '" disponible(s).';
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

$_SESSION['open_cart'] = true;

$redirectTo = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $redirectTo);
exit;