<?php $menu_actif = 'promotions'; ?> <!-- Changer le 'promotions' en fonction de la page -->
<?php include "public/includes/header_admin.php"; ?>


<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">,</span><span class="current">Promotions</span>
        </nav>
        <h1>Promotions</h1>
        <div class="topbar-actions">
            <!-- *** Rajouter le PHP pour créer la promotion -->
            <a class="btn-admin-primary" href="admin_promo_creer.php">
                <i class="fa-solid fa-plus"></i> Créer une promo
            </a>
        </div>
    </header>
    <div class="admin-content">
        <div class="orders-toolbar">
            <div class="filter-pills" id="promos-filters">
                <button class="filter-chip active" data-statut="toutes">Toutes</button>
                <button class="filter-chip" data-statut="active">Active</button>
                <button class="filter-chip" data-statut="programmee">Programmée</button>
                <button class="filter-chip" data-statut="expiree">Expirée</button>
            </div>
        </div>
        <!-- *** Rajouter le PHP pour filtrer -->
        <div class="promo-grid">
            <article class="promo-card" data-statut="active">
                <div class="promo-visual">
                    <span class="badge badge-reduction">-24%</span>
                    <span class="statut-badge statut-active"><span class="point"></span>Active</span>
                </div>
                <div class="promo-body">
                    <span class="promo-brand">PureBeauty</span>
                    <h3 class="promo-name">Crème Hydratante Rose</h3>
                    <div class="promo-prices">
                        <span class="promo-new">45,00 €</span>
                        <span class="strike-price">59,00 €</span>
                    </div>
                    <div class="promo-period">
                        <i class="fa-regular fa-calendar"></i> Du 15 juin au 15 juil. 2026
                    </div>
                </div>
                <div class="promo-footer">
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                    <span class="promo-toggle-label">Activée</span>
                    <div class="promo-actions">
                        <a class="btn-row-action" href="admin_promo_creer.php?id=2" aria-label="Modifier"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="btn-row-action" aria-label="Supprimer"><i class="fa-regular fa-trash-can"></i></button>
                    </div>
                </div>
            </article>
            <article class="promo-card" data-statut="active">
                <div class="promo-visual">

                    <span class="badge badge-reduction">-24%</span>
                    <span class="statut-badge statut-active"><span class="point"></span>Active</span>
                </div>
                <div class="promo-body">
                    <span class="promo-brand">PureBeauty</span>
                    <h3 class="promo-name">Crème Hydratante Rose</h3>
                    <div class="promo-prices">
                        <span class="promo-new">45,00 €</span>
                        <span class="strike-price">59,00 €</span>
                    </div>
                    <div class="promo-period">
                        <i class="fa-regular fa-calendar"></i> Du 15 juin au 15 juil. 2026
                    </div>
                </div>
                <div class="promo-footer">
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                    <span class="promo-toggle-label">Activée</span>
                    <div class="promo-actions">
                        <a class="btn-row-action" href="admin_promo_creer.php?id=2" aria-label="Modifier"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="btn-row-action" aria-label="Supprimer"><i class="fa-regular fa-trash-can"></i></button>
                    </div>
                </div>
            </article>
            <article class="promo-card" data-statut="programmee">
                <div class="promo-visual">
                    <span class="badge badge-reduction">-25%</span>
                    <span class="statut-badge statut-programmee"><span class="point"></span>Programmée</span>
                </div>
                <div class="promo-body">
                    <span class="promo-brand">Essence</span>
                    <h3 class="promo-name">Parfum Fleur de Cerisier</h3>
                    <div class="promo-prices">
                        <span class="promo-new">89,90 €</span>
                        <span class="strike-price">119,00 €</span>
                    </div>
                    <div class="promo-period">
                        <i class="fa-regular fa-calendar"></i> Du 1 juil. au 31 juil. 2026
                    </div>
                </div>
                <div class="promo-footer">
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                    <span class="promo-toggle-label">Activée</span>
                    <div class="promo-actions">
                        <a class="btn-row-action" href="admin_promo_creer.php?id=3" aria-label="Modifier"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="btn-row-action" aria-label="Supprimer"><i class="fa-regular fa-trash-can"></i></button>
                    </div>
                </div>
            </article>
            <article class="promo-card" data-statut="programmee">
                <div class="promo-visual">
                    <span class="badge badge-reduction">-24%</span>
                    <span class="statut-badge statut-programmee"><span class="point"></span>Programmée</span>
                </div>
                <div class="promo-body">
                    <span class="promo-brand">Velvet</span>
                    <h3 class="promo-name">Parfum Mystique</h3>
                    <div class="promo-prices">
                        <span class="promo-new">95,00 €</span>
                        <span class="strike-price">125,00 €</span>
                    </div>
                    <div class="promo-period">
                        <i class="fa-regular fa-calendar"></i> Du 10 juil. au 20 juil. 2026
                    </div>
                </div>
                <div class="promo-footer">
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                    <span class="promo-toggle-label">Activée</span>
                    <div class="promo-actions">
                        <a class="btn-row-action" href="admin_promo_creer.php?id=4" aria-label="Modifier"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="btn-row-action" aria-label="Supprimer"><i class="fa-regular fa-trash-can"></i></button>
                    </div>
                </div>
            </article>
            <article class="promo-card" data-statut="expiree">
                <div class="promo-visual">
                    <span class="badge badge-reduction">-22%</span>
                    <span class="statut-badge statut-expiree"><span class="point"></span>Expirée</span>
                </div>
                <div class="promo-body">
                    <span class="promo-brand">TimeLess</span>
                    <h3 class="promo-name">Sérum Anti-Âge Premium</h3>
                    <div class="promo-prices">
                        <span class="promo-new">69,90 €</span>
                        <span class="strike-price">89,90 €</span>
                    </div>
                    <div class="promo-period">
                        <i class="fa-regular fa-calendar"></i> Du 1 mai au 31 mai 2026
                    </div>
                </div>
                <div class="promo-footer">
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                    <span class="promo-toggle-label">Désactivée</span>
                    <div class="promo-actions">
                        <a class="btn-row-action" href="admin_promo_creer.php?id=5" aria-label="Modifier"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="btn-row-action" aria-label="Supprimer"><i class="fa-regular fa-trash-can"></i></button>
                    </div>
                </div>
            </article>
            <article class="promo-card" data-statut="expiree">
                <div class="promo-visual">
                    <span class="badge badge-reduction">-20%</span>
                    <span class="statut-badge statut-expiree"><span class="point"></span>Expirée</span>
                </div>
                <div class="promo-body">
                    <span class="promo-brand">NightGlow</span>
                    <h3 class="promo-name">Crème Nuit Réparatrice</h3>
                    <div class="promo-prices">
                        <span class="promo-new">41,60 €</span>
                        <span class="strike-price">52,00 €</span>
                    </div>
                    <div class="promo-period">
                        <i class="fa-regular fa-calendar"></i> Du 1 avr. au 30 avr. 2026
                    </div>
                </div>
                <div class="promo-footer">
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                    <span class="promo-toggle-label">Désactivée</span>
                    <div class="promo-actions">
                        <a class="btn-row-action" href="admin_promo_creer.php?id=6" aria-label="Modifier"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="btn-row-action" aria-label="Supprimer"><i class="fa-regular fa-trash-can"></i></button>
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>
</div>
</body>

</html>