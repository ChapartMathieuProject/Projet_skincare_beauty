<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <title>Document</title>
    <link rel="apple-touch-icon" href="favicon.png">
     <link rel="stylesheet" href="public/css/banner.css">
     <link rel="stylesheet" href="public/css/header.css">
     <link rel="stylesheet" href="public/css/footer.css"> 
   
</head>

<body>
    <?php
    include "banner.php";
    
    ?>

    <nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand brand-logo" href="">SkinCareBeauty</a>
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
                    <li class="nav-item"><a class="nav-link" href="#">Nouveautés</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Sérums</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Crèmes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Parfums</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Promotions</a></li>
                </ul>
            </div>
        </div>
    </nav>


