<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? '';
$productId = isset($_POST['id']) ? (int) $_POST['id'] : 0;

switch ($action) {
    case 'add':
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
        if ($productId > 0) {
            $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;
        }
        break;

    case 'update':
        $delta = isset($_POST['delta']) ? (int) $_POST['delta'] : 0;
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $delta;
            if ($_SESSION['cart'][$productId] <= 0) {
                unset($_SESSION['cart'][$productId]);
            }
        }
        break;

    case 'remove':
        unset($_SESSION['cart'][$productId]);
        break;
}

// On revient sur la page d'où venait la requête (produit, catalogue, etc.)
$redirectTo = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $redirectTo);
exit;