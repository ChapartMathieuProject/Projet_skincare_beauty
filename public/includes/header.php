<?php
$current_page   = basename($_SERVER['PHP_SELF']);   
$current_cat    = isset($_GET['cat'])    ? trim($_GET['cat'])    : '';
$current_filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

$menu_actif = '';   // par défaut : aucun lien surligné (ex. page d'accueil)

if ($current_page === 'products_categories.php') {
    if ($current_cat !== '') {
        $menu_actif = $current_cat;   
    } elseif ($current_filter !== '') {
        $menu_actif = $current_filter;   
    } else {
        $menu_actif = 'tous';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <title>SkinCareBeauty</title>
    <link rel="apple-touch-icon" href="favicon.png">
     <link rel="stylesheet" href="public/css/banner.css">
     <link rel="stylesheet" href="public/css/style.css">
   
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
                <button class="btn-icon" type="button" aria-label="Mon compte">
                    <i class="fa-regular fa-user"></i>
                </button>
                <button class="btn-icon position-relative" type="button" aria-label="Panier">
                    <i class="fa fa-shopping-bag"></i>
                </button>
                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false"
                    aria-label="Ouvrir le menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-lg-4">
                    <li class="nav-item"><a class="nav-link<?= $menu_actif === 'tous' ? ' active' : '' ?>" href="products_categories.php">Tous les produits</a></li>
                    <li class="nav-item"><a class="nav-link<?= $menu_actif === 'nouveautes' ? ' active' : '' ?>" href="products_categories.php?filter=nouveautes">Nouveautés</a></li>
                    <li class="nav-item"><a class="nav-link<?= $menu_actif === 'serum' ? ' active' : '' ?>" href="products_categories.php?cat=serum">Sérums</a></li>
                    <li class="nav-item"><a class="nav-link<?= $menu_actif === 'creme' ? ' active' : '' ?>" href="products_categories.php?cat=creme">Crèmes</a></li>
                    <li class="nav-item"><a class="nav-link<?= $menu_actif === 'parfum' ? ' active' : '' ?>" href="products_categories.php?cat=parfum">Parfums</a></li>
                    <li class="nav-item"><a class="nav-link<?= $menu_actif === 'promotions' ? ' active' : '' ?>" href="products_categories.php?filter=promotions">Promotions</a></li>
                </ul>
            </div>
        </div>
    </nav>