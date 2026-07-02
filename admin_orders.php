<?php $menu_actif = 'commandes'; ?> <!-- Changer le 'commandes' en fonction de la page -->
<?php include "public/includes/header_admin.php"; ?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">›</span> <span class="current">Commandes</span>
        </nav>
        <h1>Commandes</h1>
    </header>
    <div class="orders-toolbar">
        <div class="search-admin">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input class="input-admin" id="orders-search" type="search" placeholder="Rechercher une commande, un client...">
        </div>
        <!-- *** JS ou PHP (pour le ?statut) manquant pour filtrer pour l'instant "Toutes" est actif -->
        <div class="filter-pills" id="orders-filters">
            <button class="filter-chip active" data-statut="toutes">Toutes</button>
            <button class="filter-chip" data-statut="attente">En attente</button>
            <button class="filter-chip" data-statut="preparation">En préparation</button>
            <button class="filter-chip" data-statut="expediee">Expédiée</button>
            <button class="filter-chip" data-statut="livree">Livrée</button>
        </div>
    </div>
    <p class="results-count" id="orders-count">6 commandes</p>
    <div class="table-scroll">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Articles</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- *** Manque requête PHP pour générations des commandes -->
                <tr data-statut="attente">
                    <td class="order-num">#CMD-1042</td>
                    <td class="client-cell">
                        <span class="nom">Camille Durand</span>
                        <span class="mail">camille.durand@email.fr</span>
                    </td>
                    <td>24 juin 2026</td>
                    <td>3</td>
                    <td class="order-total">129,70 €</td>
                    <td>
                        <span class="statut-badge statut-attente"><span class="point"></span>En attente</span>
                    </td>
                    <td class="col-actions">
                        <a class="btn-row-action" href="" aria-label="Voir la commande">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                    </td>
                </tr>
                <tr data-statut="preparation">
                    <td class="order-num">#CMD-1041</td>
                    <td class="client-cell">
                        <span class="nom">Sophie Martin</span>
                        <span class="mail">s.martin@email.fr</span>
                    </td>
                    <td>23 juin 2026</td>
                    <td>1</td>
                    <td class="order-total">39,90 €</td>
                    <td>
                        <span class="statut-badge statut-preparation"><span class="point"></span>En préparation</span>
                    </td>
                    <td class="col-actions">
                        <a class="btn-row-action" href="admin_order_detail.php?id=1041" aria-label="Voir la commande">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>

                <tr data-statut="expediee">
                    <td class="order-num">#CMD-1040</td>
                    <td class="client-cell">
                        <span class="nom">Léa Robert</span>
                        <span class="mail">lea.robert@email.fr</span>
                    </td>
                    <td>23 juin 2026</td>
                    <td>5</td>
                    <td class="order-total">214,50 €</td>
                    <td>
                        <span class="statut-badge statut-expediee"><span class="point"></span>Expédiée</span>
                    </td>
                    <td class="col-actions">
                        <a class="btn-row-action" href="admin_order_detail.php?id=1040" aria-label="Voir la commande">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>

                <tr data-statut="livree">
                    <td class="order-num">#CMD-1039</td>
                    <td class="client-cell">
                        <span class="nom">Hugo Bernard</span>
                        <span class="mail">hugo.bernard@email.fr</span>
                    </td>
                    <td>22 juin 2026</td>
                    <td>2</td>
                    <td class="order-total">84,90 €</td>
                    <td>
                        <span class="statut-badge statut-livree"><span class="point"></span>Livrée</span>
                    </td>
                    <td class="col-actions">
                        <a class="btn-row-action" href="admin_order_detail.php?id=1039" aria-label="Voir la commande">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>

                <tr data-statut="expediee">
                    <td class="order-num">#CMD-1038</td>
                    <td class="client-cell">
                        <span class="nom">Inès Petit</span>
                        <span class="mail">ines.petit@email.fr</span>
                    </td>
                    <td>21 juin 2026</td>
                    <td>4</td>
                    <td class="order-total">176,00 €</td>
                    <td>
                        <span class="statut-badge statut-expediee"><span class="point"></span>Expédiée</span>
                    </td>
                    <td class="col-actions">
                        <a class="btn-row-action" href="admin_order_detail.php?id=1038" aria-label="Voir la commande">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>

                <tr data-statut="livree">
                    <td class="order-num">#CMD-1037</td>
                    <td class="client-cell">
                        <span class="nom">Lucas Moreau</span>
                        <span class="mail">l.moreau@email.fr</span>
                    </td>
                    <td>20 juin 2026</td>
                    <td>1</td>
                    <td class="order-total">52,00 €</td>
                    <td>
                        <span class="statut-badge statut-livree"><span class="point"></span>Livrée</span>
                    </td>
                    <td class="col-actions">
                        <a class="btn-row-action" href="admin_order_detail.php?id=1037" aria-label="Voir la commande">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>


            </tbody>

        </table>

    </div>
</div>

</body>
</html>