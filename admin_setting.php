<?php
require_once "public/includes/auth.php";
require_sav();

$menu_actif = 'reglages';
include "public/includes/header_admin.php";
?>

<div class="admin-main">

    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">›</span> <span class="current">Réglages</span>
        </nav>
        <h1>Réglages</h1>

        <div class="topbar-status">
            <span class="dot"></span> Modifications enregistrées
        </div>

        <div class="topbar-actions">

            <button type="submit" form="settings-form" class="btn-admin-primary">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
        </div>
    </header>

    <form id="settings-form" class="admin-content" method="post" action="">
        <section class="admin-card">
            <div class="card-title">
                <span class="num">1</span>
                <h2>Informations de la boutique</h2>
            </div>
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label-admin" for="shop-name">Nom de la boutique</label>
                    <input class="input-admin" type="text" id="shop-name" name="nom_boutique" value="SkinCareBeauty">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label-admin" for="shop-email">Email de contact</label>
                    <input class="input-admin" type="email" id="shop-email" name="email_boutique" value="contact@skincarebeauty.fr">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label-admin" for="shop-phone">Téléphone</label>
                    <input class="input-admin" type="tel" id="shop-phone" name="tel_boutique" value="01 23 45 67 89">
                </div>
            </div>
        </section>

        <section class="admin-card">
            <div class="card-title">
                <span class="num">2</span>
                <h2>Livraison</h2>
            </div>

            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <label class="form-label-admin" for="shipping-fee">Frais de livraison (€)</label>
                    <input class="input-admin" type="text" id="shipping-fee" name="frais_port" value="4,90">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label-admin" for="free-shipping-threshold">Seuil livraison gratuite (€)</label>
                    <input class="input-admin" type="text" id="free-shipping-threshold" name="seuil_gratuit" value="50">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label-admin" for="delivery-time">Délai estimé</label>
                    <input class="input-admin" type="text" id="delivery-time" name="delai_livraison" value="48h">
                </div>
            </div>
        </section>
        <section class="admin-card">
            <div class="card-title">
                <span class="num">3</span>
                <h2>Moyens de paiement <span class="hint">activez ceux que vous acceptez</span></h2>
            </div>
            <div class="toggle-row">
                <span class="label"><i class="fa-solid fa-credit-card"></i> Carte bancaire</span>
                <label class="switch">
                    <input type="checkbox" name="pay_carte" value="1" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="toggle-row">
                <span class="label"><i class="fa-brands fa-paypal"></i> PayPal</span>
                <label class="switch">
                    <input type="checkbox" name="pay_paypal" value="1" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="toggle-row">
                <span class="label"><i class="fa-brands fa-apple-pay"></i> Apple Pay</span>
                <label class="switch">
                    <input type="checkbox" name="pay_applepay" value="1">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="toggle-row">
                <span class="label"><i class="fa-solid fa-building-columns"></i> Virement bancaire</span>
                <label class="switch">
                    <input type="checkbox" name="pay_virement" value="1">
                    <span class="slider"></span>
                </label>
            </div>
        </section>
        <section class="admin-card">
            <div class="card-title">
                <span class="num">4</span>
                <h2>Notifications <span class="hint">emails reçus par l'administration</span></h2>
            </div>
            <div class="toggle-row">
                <span class="label">Nouvelle commande <span class="muted"> un email à chaque commande</span></span>
                <label class="switch">
                    <input type="checkbox" name="notif_commande" value="1" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="toggle-row">
                <span class="label-stock">Stock faible <span class="muted">alerte sous 5 unités</span></span>
                <label class="switch">
                    <input type="checkbox" name="notif_stock" value="1" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="toggle-row">
                <span class="label">Récapitulatif hebdomadaire <span class="muted">- chaque lundi matin</span></span>
                <label class="switch">
                    <input type="checkbox" name="notif_recap" value="1">
                    <span class="slider"></span>
                </label>
            </div>
        </section>
    </form>
</div>
</body>

</html>