<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('BASE_PATH')) {
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    define('BASE_PATH', $base === '/' ? '' : $base);
}
if (!function_exists('url')) {
    require_once __DIR__ . '/../../app/core/helpers.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $_SESSION['user_name'] ?? 'Invité';
$userEmail  = $_SESSION['user_mail'] ?? '';
$isAdmin    = ($_SESSION['user_type_id'] ?? null) === 2;
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <title>Document</title>
    <link rel="apple-touch-icon" href="favicon.png">
    <link rel="stylesheet" href="<?= url('/public/css/banner.css') ?>">
    <link rel="stylesheet" href="<?= url('/public/css/style.css') ?>">
</head>

<body>
    <?php include __DIR__ . '/banner.php'; ?>

    <nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand brand-logo" href="<?= url('/') ?>">SkinCareBeauty</a>
            <div class="d-flex align-items-center order-lg-last">
                <button class="btn-icon" type="button" aria-label="Rechercher">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button class="btn-icon" type="button" aria-label="Mon compte" data-bs-toggle="modal" data-bs-target="#userModal">
                    <i class="fa-regular fa-user"></i>
                </button>
                <button class="btn-icon position-relative" type="button" aria-label="Panier" data-bs-toggle="modal" data-bs-target="#cart-modal">
                    <i class="fa fa-shopping-bag"></i>
                    <span class="badge rounded-pill position-absolute top-0 start-100 translate-middle cart-count-badge" id="cart-count"></span>
                </button>
                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false"
                    aria-label="Ouvrir le menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="<?= url('/produits?filter=nouveautes') ?>">Nouveautés</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/produits?cat=serum') ?>">Sérums</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/produits?cat=creme') ?>">Crèmes</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/produits?cat=parfum') ?>">Parfums</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/produits?filter=promotions') ?>">Promotions</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Modale infos utilisateur -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mon compte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if ($isLoggedIn): ?>
                        <p><strong>Nom :</strong> <?= htmlspecialchars($userName) ?></p>
                        <p><strong>Email :</strong> <?= htmlspecialchars($userEmail) ?></p>
                    <?php else: ?>
                        <p>Connectez-vous pour accéder à votre compte.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <?php if ($isLoggedIn): ?>
                        <?php if ($isAdmin): ?>
                            <a href="<?= url('/admin_dashboard.php') ?>" class="btn-rose">Tableau de bord admin</a>
                        <?php else: ?>
                            <a href="<?= url('/users.php') ?>" class="btn-rose">Mon profil</a>
                        <?php endif; ?>
                        <a href="<?= url('/logout.php') ?>" class="btn-rose">Déconnexion</a>
                    <?php else: ?>
                        <a href="<?= url('/login.php') ?>" class="btn-rose">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale panier -->
    <div class="modal fade" id="cart-modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mon panier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($_SESSION['cart_message'])): ?>
    <div class="alert alert-warning" role="alert">
        <?= htmlspecialchars($_SESSION['cart_message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['cart_message']); ?>
<?php endif; ?>
                    <div id="cart-items">
                        <?php if (empty($cartItems)): ?>
                            <p class="text-center text-muted py-4" id="cart-empty-message">Votre panier est vide.</p>
                        <?php else: ?>
                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-line d-flex align-items-center mb-3">
                                    <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>"
                                        style="width:60px;height:60px;object-fit:cover;" class="me-2">

                                    <div class="flex-grow-1">
                                        <div><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted"><?= number_format($item['price'], 2, ',', ' ') ?> €</div>
                                    </div>

                                    <!-- Diminuer -->
                                    <form method="post" action="panier_action.php" class="d-inline">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <input type="hidden" name="delta" value="-1">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">-</button>
                                    </form>

                                    <span class="mx-2"><?= (int) $item['quantity'] ?></span>

                                    <!-- Augmenter -->
                                    <form method="post" action="panier_action.php" class="d-inline">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <input type="hidden" name="delta" value="1">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                                    </form>

                                    <span class="ms-3" style="min-width:70px;text-align:right;">
                                        <?= number_format($item['lineTotal'], 2, ',', ' ') ?> €
                                    </span>

                                    <!-- Supprimer -->
                                    <form method="post" action="panier_action.php" class="d-inline ms-2">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div>
                        <strong>Total :</strong>
                        <span id="cart-total"><?= number_format($cartTotal, 2, ',', ' ') ?> €</span>
                    </div>
                    <div>
                        <button type="button" class="btn-rose" data-bs-dismiss="modal">Continuer mes achats</button>
                        <a href="<?= url('/checkout.php') ?>" class="btn-rose" id="cart-checkout-btn">Passer commande</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php if (!empty($_SESSION['open_cart'])): ?>
        <?php unset($_SESSION['open_cart']); // On efface la consigne pour les pages suivantes 
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var cartModalElement = document.getElementById('cart-modal');
                if (cartModalElement) {
                    var cartModal = new bootstrap.Modal(cartModalElement);
                    cartModal.show();
                }
            });
        </script>
    <?php endif; ?>
    </div>