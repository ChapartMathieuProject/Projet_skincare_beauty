<?php

declare(strict_types=1);

require_once __DIR__ . '/public/includes/db.php';

if (!defined('BASE_PATH')) {
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    define('BASE_PATH', $base === '/' ? '' : $base);
}
require_once __DIR__ . '/app/core/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function is_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

function cart_payload(PDO $pdo): array
{
    $cart  = $_SESSION['cart'] ?? [];
    $items = [];
    $total = 0.0;

    if (!empty($cart)) {
        $productDAO = new ProductDAO($pdo);
        $products   = $productDAO->findByIds(array_keys($cart));

        $promotions = [];
        foreach ($pdo->query("SELECT product_id, promotion_percent FROM promotions WHERE promotion_is_active = 1") as $row) {
            $promotions[(int) $row['product_id']] = (int) $row['promotion_percent'];
        }

        $pictures = [];
        foreach ($pdo->query("SELECT product_id, picture_path FROM pictures") as $row) {
            if (!isset($pictures[(int) $row['product_id']])) {
                $pictures[(int) $row['product_id']] = $row['picture_path'];
            }
        }

        foreach ($products as $p) {
            $pid = $p->getId();
            $qty = (int) ($cart[$pid] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $base    = $p->getSellPrice();
            $percent = $promotions[$pid] ?? null;
            $unit    = $percent !== null ? $base - ($base * ($percent / 100)) : $base;
            $sub     = $unit * $qty;
            $total  += $sub;

            $items[] = [
                'id'       => $pid,
                'name'     => $p->getName(),
                'quantity' => $qty,
                'unit'     => round($unit, 2),
                'subtotal' => round($sub, 2),
                'image'    => url('/' . ($pictures[$pid] ?? 'images/_C-E-Ferulic-30ml_SkinCeuticals.jpg')),
                'url'      => url('/produit/' . $p->getSlug()),
            ];
        }
    }

    return [
        'items' => $items,
        'count' => array_sum($cart),
        'total' => round($total, 2),
    ];
}

function redirect_to(string $path): never
{
    if ($path === '' || $path[0] !== '/' || str_starts_with($path, '//')) {
        $path = '/cart.php';
    }
    header('Location: ' . url($path));
    exit;
}

function respond(string $redirect, PDO $pdo): never
{
    if (is_ajax()) {
        $message = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $payload = cart_payload($pdo);
        $payload['ok']      = true;
        $payload['message'] = $message;

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
    redirect_to($redirect);
}


if (!isset($_SESSION['user_id'])) {
    if (is_ajax()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'      => false,
            'error'   => 'auth',
            'message' => "Connectez-vous pour utiliser le panier.",
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $_SESSION['flash'] = "Connectez-vous pour utiliser le panier.";
    redirect_to('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/cart.php');
}


$action   = $_POST['action']   ?? '';
$id       = (int) ($_POST['id'] ?? 0);
$quantity = (int) ($_POST['quantity'] ?? 1);
$redirect = $_POST['redirect'] ?? '/cart.php';


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$productDAO = new ProductDAO($pdo);

switch ($action) {

    case 'add':
        if ($id <= 0 || $quantity <= 0) {
            break;
        }

        $product = $productDAO->find($id);
        if ($product === null || !$product->isStatus()) {
            $_SESSION['flash'] = "Produit indisponible.";
            break;
        }

        // ↓ les trois lignes qui manquaient
        $stock   = (int) $product->getQuantity();
        $current = $_SESSION['cart'][$id] ?? 0;
        $wanted  = $current + $quantity;

        if ($stock <= 0) {
            $_SESSION['flash'] = "Produit en rupture de stock.";
            break;
        }
        if ($wanted > $stock) {
            $wanted = $stock;
            $_SESSION['flash'] = "Quantité limitée au stock disponible ({$stock}).";
        } else {
            $_SESSION['flash'] = "Produit ajouté au panier.";
        }

        $_SESSION['cart'][$id] = $wanted;
        break;

    case 'update':
        if ($id <= 0) {
            break;
        }
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$id]);
            $_SESSION['flash'] = "Produit retiré du panier.";
            break;
        }
        $product = $productDAO->find($id);
        if ($product !== null) {
            $_SESSION['cart'][$id] = min($quantity, (int) $product->getQuantity());
            $_SESSION['flash'] = "Quantité mise à jour.";
        }
        break;

    case 'remove':
        unset($_SESSION['cart'][$id]);
        $_SESSION['flash'] = "Produit retiré du panier.";
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        $_SESSION['flash'] = "Panier vidé.";
        break;
}

respond($redirect, $pdo);