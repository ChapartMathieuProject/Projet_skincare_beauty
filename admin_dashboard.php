<?php
require_once "public/includes/auth.php";
require_sav();

$menu_actif = 'dashboard';
include "public/includes/header_admin.php";
?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            <span class="current">Tableau de bord</span>
        </nav>
        <h1>Vue d'ensemble</h1>
        <div class="topbar-status">
            <i class="fa-regular fa-calendar"></i> Vendredi 26 juin 2026
        </div>
    </header>
    <div class="admin-content">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-rose"><i class="fa-solid fa-euro-sign"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Ventes du mois</span>
                    <span class="kpi-value">2 480 €</span>
                    <span class="kpi-trend up">
                        <i class="fa-solid fa-arrow-up"></i> +8,2%
                        <span class="kpi-trend-note">vs mois dernier</span>
                    </span>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-blue"><i class="fa-solid fa-bag-shopping"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Commandes du mois</span>
                    <span class="kpi-value">184</span>
                    <span class="kpi-trend up">
                        <i class="fa-solid fa-arrow-up"></i> +5,1%
                        <span class="kpi-trend-note">vs mois dernier</span>
                    </span>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-amber"><i class="fa-solid fa-box"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Produits en stock</span>
                    <span class="kpi-value">326</span>
                    <span class="kpi-note">12 en rupture</span>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-green"><i class="fa-solid fa-tag"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Promos actives</span>
                    <span class="kpi-value">5</span>
                    <span class="kpi-note">2 programmées</span>
                </div>
            </div>


        </div>
        <section class="admin-card">
            <div class="dash-card-head">
                <h2>Evolution des ventes</h2>
                <span class="dash-card-sub">6 derniers mois</span>
            </div>
            <div class="chart">
                <div class="chart-bars">
                    <div class="chart-col">
                        <span class="chart-val">8,1k</span>
                        <span class="bar" style="--h: 65%;"></span>
                        <span class="chart-lbl">Janv.</span>
                    </div>
                    <div class="chart-col">
                        <span class="chart-val">7,4k</span>
                        <span class="bar" style="--h: 59%;"></span>
                        <span class="chart-lbl">Févr.</span>
                    </div>
                    <div class="chart-col">
                        <span class="chart-val">9,2k</span>
                        <span class="bar" style="--h: 74%;"></span>
                        <span class="chart-lbl">Mars</span>
                    </div>
                    <div class="chart-col">
                        <span class="chart-val">10,5k</span>
                        <span class="bar" style="--h: 84%;"></span>
                        <span class="chart-lbl">Avr.</span>
                    </div>
                    <div class="chart-col">
                        <span class="chart-val">9,8k</span>
                        <span class="bar" style="--h: 78%;"></span>
                        <span class="chart-lbl">Mai</span>
                    </div>
                    <div class="chart-col">
                        <span class="chart-val">12,5k</span>
                        <span class="bar is-current" style="--h: 100%;"></span>
                        <span class="chart-lbl">Juin</span>
                    </div>
                </div>
            </div>
        </section>
        <section class="admin-card">
            <div class="dash-card-head">
                <h2>Dernières commandes</h2>
                <a class="dash-link" href="admin_commandes.php">
                    Voir toutes les commandes <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="table-scroll">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Commande</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="order-num">#CMD-1042</td>
                            <td class="client-cell">
                                <span class="nom">Camille Durand</span>
                                <span class="mail">camille.durand@email.fr</span>
                            </td>
                            <td>24 juin 2026</td>
                            <td class="order-total">129,70 €</td>
                            <td><span class="statut-badge statut-attente"><span class="point"></span>En attente</span></td>
                        </tr>
                        <tr>
                            <td class="order-num">#CMD-1041</td>
                            <td class="client-cell">
                                <span class="nom">Sophie Martin</span>
                                <span class="mail">s.martin@email.fr</span>
                            </td>
                            <td>23 juin 2026</td>
                            <td class="order-total">39,90 €</td>
                            <td><span class="statut-badge statut-preparation"><span class="point"></span>En préparation</span></td>
                        </tr>
                        <tr>
                            <td class="order-num">#CMD-1040</td>
                            <td class="client-cell">
                                <span class="nom">Léa Robert</span>
                                <span class="mail">lea.robert@email.fr</span>
                            </td>
                            <td>23 juin 2026</td>
                            <td class="order-total">214,50 €</td>
                            <td><span class="statut-badge statut-expediee"><span class="point"></span>Expédiée</span></td>
                        </tr>
                        <tr>
                            <td class="order-num">#CMD-1039</td>
                            <td class="client-cell">
                                <span class="nom">Hugo Bernard</span>
                                <span class="mail">hugo.bernard@email.fr</span>
                            </td>
                            <td>22 juin 2026</td>
                            <td class="order-total">84,90 €</td>
                            <td><span class="statut-badge statut-livree"><span class="point"></span>Livrée</span></td>
                        </tr>
                        <tr>
                            <td class="order-num">#CMD-1038</td>
                            <td class="client-cell">
                                <span class="nom">Inès Petit</span>
                                <span class="mail">ines.petit@email.fr</span>
                            </td>
                            <td>21 juin 2026</td>
                            <td class="order-total">176,00 €</td>
                            <td><span class="statut-badge statut-expediee"><span class="point"></span>Expédiée</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>