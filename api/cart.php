<?php

// Fichier : api/cart.php
// Endpoint JSON pour consulter et modifier le panier stocké en session.
// Le prix n'est jamais fait confiance depuis le front : on le recalcule
// à chaque fois depuis la base (product_buy_price + marge).

require_once __DIR__ . '/../public/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // structure : [product_id => quantite]
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get':
        sendCartResponse($pdo);
        break;

    case 'add':
        addToCart($pdo);
        break;

    case 'update':
        updateCartQuantity($pdo);
        break;

    case 'remove':
        removeFromCart();
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action inconnue']);
}

/**
 * Ajoute un produit au panier, ou incrémente sa quantité s'il y est déjà.
 */
function addToCart(PDO $pdo): void
{
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = (int) $data['id'];
    $quantity = (int) ($data['quantity'] ?? 1);

    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }

    $_SESSION['cart'][$productId] += $quantity;

    sendCartResponse($pdo);
}

/**
 * Modifie la quantité d'un produit du panier (suppression si elle tombe à 0).
 */
function updateCartQuantity(PDO $pdo): void
{
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = (int) $data['id'];
    $delta = (int) $data['delta'];

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $delta;

        if ($_SESSION['cart'][$productId] <= 0) {
            unset($_SESSION['cart'][$productId]);
        }
    }

    sendCartResponse($pdo);
}

/**
 * Retire un produit du panier.
 */
function removeFromCart(): void
{
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = (int) $data['id'];

    unset($_SESSION['cart'][$productId]);

    global $pdo;
    sendCartResponse($pdo);
}

/**
 * Envoie le contenu actuel du panier au format JSON (items, total, count).
 * Marge appliquée ici, sous forme de variable claire.
 */
function sendCartResponse(PDO $pdo): void
{
    $defaultImage = 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg';

    $items = [];
    $total = 0;
    $count = 0;

    $productStatement = $pdo->prepare('SELECT product_id, product_name, product_buy_price, product_margin FROM products WHERE product_id = :id');
    $pictureStatement = $pdo->prepare('SELECT picture_path FROM pictures WHERE product_id = :id LIMIT 1');

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $productStatement->execute(['id' => $productId]);
        $product = $productStatement->fetch();

        if (!$product) {
            continue;
        }

        $pictureStatement->execute(['id' => $productId]);
        $picture = $pictureStatement->fetch();
        $image = $picture !== false ? $picture['picture_path'] : $defaultImage;

        $marginRate = (float) $product['product_margin'] / 100;
        $price = (float) $product['product_buy_price'] * (1 + $marginRate);

        $items[] = [
            'id' => (int) $product['product_id'],
            'name' => $product['product_name'],
            'price' => $price,
            'image' => $image,
            'quantity' => (int) $quantity,
        ];

        $total += $price * $quantity;
        $count += $quantity;
    }

    echo json_encode(['items' => $items, 'total' => $total, 'count' => $count]);
}