<?php
// Activer l'affichage des erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Si tu utilises PDO pour te connecter à la base de données, active les erreurs SQL :
// $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Invité';
$userEmail = $_SESSION['user_mail'] ?? '';
$isAdmin = ($_SESSION['user_type_id'] ?? null) === 2;
?>

<!DOCTYPE html>
<html lang="en">

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
    <?php
    include "banner.php";

    ?>

    <nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand brand-logo" href="index.php">SkinCareBeauty</a>
            <div class="d-flex align-items-center order-lg-last">
                <button class="btn-icon" type="button" aria-label="Rechercher">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button class="btn-icon" type="button" aria-label="Mon compte" data-bs-toggle="modal" data-bs-target="#userModal">
                    <i class="fa-regular fa-user"></i>
                </button>
                <button class="btn-icon position-relative" type="button" aria-label="Panier" data-bs-toggle="modal" data-bs-target="#cart-modal">
                    <i class="fa fa-shopping-bag"></i>
                    <span class="badge rounded-pill  position-absolute top-0 start-100 translate-middle cart-count-badge" id="cart-count">

                    </span>
                </button>
                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false"
                    aria-label="Ouvrir le menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="products_categories.php?filter=nouveautes">Nouveautés</a></li>
                    <li class="nav-item"><a class="nav-link" href="products_categories.php?cat=serum">Sérums</a></li>
                    <li class="nav-item"><a class="nav-link" href="products_categories.php?cat=creme">Crèmes</a></li>
                    <li class="nav-item"><a class="nav-link" href="products_categories.php?cat=parfum">Parfums</a></li>
                    <li class="nav-item"><a class="nav-link" href="products_categories.php?filter=promotions">Promotions</a></li>
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
                            <a href="admin_dashboard.php" class="btn-rose">Tableau de bord admin</a>
                        <?php else: ?>
                            <a href="users.php" class="btn-rose">Mon profil</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn-rose">Déconnexion</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-rose">Connexion</a>
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
                    <div id="cart-items">
                        <p class="text-center text-muted py-4" id="cart-empty-message">Votre panier est vide.</p>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div>
                        <strong>Total :</strong>
                        <span id="cart-total">0,00 €</span>
                    </div>
                    <div>
                        <button type="button" class="btn-rose" data-bs-dismiss="modal">Continuer mes achats</button>
                        <a href="/checkout.php" class="btn-rose" id="cart-checkout-btn">Passer commande</a>
                    </div>
                </div>
            </div>
        </div>
    </div>