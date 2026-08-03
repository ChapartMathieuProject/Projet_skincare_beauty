<?php
$menu_actif = $menu_actif ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création produit ADMIN</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="public/css/style.css">
</head>

<body class="admin">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <div class="brand">SkinCareBeauty</div>
                <div class="sub">Espace Administrateur</div>
            </div>
            <nav class="admin-nav"> 
                <a href="admin_dashboard.php" class="<?= $menu_actif === 'dashboard'  ? 'active' : '' ?>">Tableau de bord</a>
                <a href="admin_products.php" class="<?= $menu_actif === 'produits'   ? 'active' : '' ?>">Produits</a>
                <a href="admin_add_product.php" class="<?= $menu_actif === 'ajouter un produits'   ? 'active' : '' ?>">Ajouter un produit</a>
                <a href="admin_orders.php" class="<?= $menu_actif === 'commandes'  ? 'active' : '' ?>">Commandes</a>
                <a href="admin_tickets.php" class="<?= $menu_actif === 'sav' ? 'active' : '' ?>">SAV — Retours</a>
                <a href="admin_users.php" class="<?= $menu_actif === 'clients'    ? 'active' : '' ?>">Clients</a>
                <a href="admin_promotions.php" class="<?= $menu_actif === 'promotions' ? 'active' : '' ?>">Promotions</a>
                <a href="admin_setting.php" class="<?= $menu_actif === 'reglages'   ? 'active' : '' ?>">Réglages</a>
            </nav>
            <div class="admin-user">
                <span class="avatar">AL</span>
                <div>
                    <div class="name-admin">Camil CR7</div>
                    <div class="role">Gérant</div>
                </div>
            </div>
        </aside>