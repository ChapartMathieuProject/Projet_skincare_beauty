<?php $menu_actif = 'clients'; ?> <!-- Changer le 'clients' en fonction de la page -->
<?php include "public/includes/header_admin.php"; ?>

<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Tableau de bord <span class="sep">›</span> <span class="current">Clients</span>
        </nav>
        <h1>Clients</h1>
    </header>
    <div class="admin-content">
        <section class="admin-card">
            <div class="orders-toolbar">
                <div class="search-admin">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input class="input-admin" id="clients-search" type="search"
                        placeholder="Rechercher un client (nom, email, téléphone...)">
                </div>
            </div>
            <p class="results-count" id="clients-count">6 clients</p>

            <div class="table-scroll">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Téléphone</th>
                            <th>Commandes</th>
                            <th>Inscription</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- *** généré par PHP, juste présent en HTML pour tester.
                             Le bouton œil ouvre la modale unique #client-modal et
                             porte les infos du client en data-… (lus par le JS). -->
                        <tr>
                            <td>
                                <div class="client-identity">
                                    <span class="client-avatar">CD</span>
                                    <div class="client-cell">
                                        <span class="nom">Camille Durand</span>
                                        <span class="mail">camille.durand@email.fr</span>
                                    </div>
                                </div>
                            </td>
                            <td>06 12 34 56 78</td>
                            <td><span class="count-pill">5</span></td>
                            <td>12 janv. 2025</td>
                            <td class="col-actions">
                                <button type="button" class="btn-row-action"
                                    data-bs-toggle="modal" data-bs-target="#client-modal"
                                    data-id="1" data-avatar="CD" data-nom="Camille Durand"
                                    data-email="camille.durand@email.fr" data-tel="06 12 34 56 78"
                                    data-inscription="12 janv. 2025" data-commandes="5"
                                    aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="client-identity">
                                    <span class="client-avatar">SM</span>
                                    <div class="client-cell">
                                        <span class="nom">Sophie Martin</span>
                                        <span class="mail">s.martin@email.fr</span>
                                    </div>
                                </div>
                            </td>
                            <td>06 98 76 54 32</td>
                            <td><span class="count-pill">2</span></td>
                            <td>03 mars 2025</td>
                            <td class="col-actions">
                                <button type="button" class="btn-row-action"
                                    data-bs-toggle="modal" data-bs-target="#client-modal"
                                    data-id="2" data-avatar="SM" data-nom="Sophie Martin"
                                    data-email="s.martin@email.fr" data-tel="06 98 76 54 32"
                                    data-inscription="03 mars 2025" data-commandes="2"
                                    aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="client-identity">
                                    <span class="client-avatar">LR</span>
                                    <div class="client-cell">
                                        <span class="nom">Léa Robert</span>
                                        <span class="mail">lea.robert@email.fr</span>
                                    </div>
                                </div>
                            </td>
                            <td>07 45 12 89 33</td>
                            <td><span class="count-pill">8</span></td>
                            <td>21 nov. 2024</td>
                            <td class="col-actions">
                                <button type="button" class="btn-row-action"
                                    data-bs-toggle="modal" data-bs-target="#client-modal"
                                    data-id="3" data-avatar="LR" data-nom="Léa Robert"
                                    data-email="lea.robert@email.fr" data-tel="07 45 12 89 33"
                                    data-inscription="21 nov. 2024" data-commandes="8"
                                    aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="client-identity">
                                    <span class="client-avatar">HB</span>
                                    <div class="client-cell">
                                        <span class="nom">Hugo Bernard</span>
                                        <span class="mail">hugo.bernard@email.fr</span>
                                    </div>
                                </div>
                            </td>
                            <td>06 22 41 78 90</td>
                            <td><span class="count-pill">1</span></td>
                            <td>09 juin 2026</td>
                            <td class="col-actions">
                                <button type="button" class="btn-row-action"
                                    data-bs-toggle="modal" data-bs-target="#client-modal"
                                    data-id="4" data-avatar="HB" data-nom="Hugo Bernard"
                                    data-email="hugo.bernard@email.fr" data-tel="06 22 41 78 90"
                                    data-inscription="09 juin 2026" data-commandes="1"
                                    aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="client-identity">
                                    <span class="client-avatar">IP</span>
                                    <div class="client-cell">
                                        <span class="nom">Inès Petit</span>
                                        <span class="mail">ines.petit@email.fr</span>
                                    </div>
                                </div>
                            </td>
                            <td>07 88 23 45 11</td>
                            <td><span class="count-pill">3</span></td>
                            <td>17 sept. 2025</td>
                            <td class="col-actions">
                                <button type="button" class="btn-row-action"
                                    data-bs-toggle="modal" data-bs-target="#client-modal"
                                    data-id="5" data-avatar="IP" data-nom="Inès Petit"
                                    data-email="ines.petit@email.fr" data-tel="07 88 23 45 11"
                                    data-inscription="17 sept. 2025" data-commandes="3"
                                    aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="client-identity">
                                    <span class="client-avatar">LM</span>
                                    <div class="client-cell">
                                        <span class="nom">Lucas Moreau</span>
                                        <span class="mail">l.moreau@email.fr</span>
                                    </div>
                                </div>
                            </td>
                            <td>06 55 67 12 04</td>
                            <td><span class="count-pill">4</span></td>
                            <td>28 févr. 2025</td>
                            <td class="col-actions">
                                <button type="button" class="btn-row-action"
                                    data-bs-toggle="modal" data-bs-target="#client-modal"
                                    data-id="6" data-avatar="LM" data-nom="Lucas Moreau"
                                    data-email="l.moreau@email.fr" data-tel="06 55 67 12 04"
                                    data-inscription="28 févr. 2025" data-commandes="4"
                                    aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===================================================================
             MODALE FICHE CLIENT (une seule, partagée par toutes les lignes).
             Vide pour l'instant : le JS lira les data-… du bouton cliqué
             (event.relatedTarget) et remplira les emplacements #cm-… ci-dessous.
             =================================================================== -->
        <div class="modal fade" id="client-modal" tabindex="-1" aria-labelledby="cm-name" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content client-modal">

                    <!-- En-tête : avatar + nom + email -->
                    <div class="modal-header">
                        <div class="client-modal-head">
                            <span class="client-avatar lg" id="cm-avatar"></span>
                            <div>
                                <h5 class="modal-title" id="cm-name"></h5>
                                <span class="client-modal-mail" id="cm-email"></span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>

                    <div class="modal-body">

                        <!-- Coordonnées -->
                        <div class="client-section">
                            <h6 class="client-section-title">Coordonnées</h6>
                            <div class="client-info-grid">
                                <div class="info-item">
                                    <span class="info-label">Téléphone</span>
                                    <span class="info-value" id="cm-phone"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Inscription</span>
                                    <span class="info-value" id="cm-registered"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Commandes</span>
                                    <span class="info-value" id="cm-orders-count"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total dépensé</span>
                                    <span class="info-value" id="cm-total"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Rôle (modifiable) -->
                        <div class="client-section">
                            <h6 class="client-section-title">Rôle</h6>
                            <div class="role-row">
                                <select class="input-admin" id="cm-role" name="role" aria-label="Rôle du client">
                                    <option value="client">Client</option>
                                    <option value="vip">Client VIP</option>
                                    <option value="admin">Administrateur</option>
                                </select>
                                <button type="button" class="btn-admin-primary" id="cm-role-save">Mettre à jour</button>
                            </div>
                        </div>

                        <!-- Adresses -->
                        <div class="client-section">
                            <h6 class="client-section-title">Adresses</h6>
                            <div class="address-list">
                                <div class="address-block">
                                    <span class="address-tag">Livraison</span>
                                    <p id="cm-address-shipping"></p>
                                </div>
                                <div class="address-block">
                                    <span class="address-tag">Facturation</span>
                                    <p id="cm-address-billing"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Dernières commandes -->
                        <div class="client-section">
                            <h6 class="client-section-title">Dernières commandes</h6>
                            <div class="table-scroll">
                                <table class="orders-table">
                                    <thead>
                                        <tr>
                                            <th>Commande</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <!-- Le JS injectera les <tr> ici. Modèle d'une ligne :
                                         <tr>
                                           <td class="order-num">#CMD-0000</td>
                                           <td>00 mois 0000</td>
                                           <td class="order-total">0,00 €</td>
                                           <td><span class="statut-badge statut-livree"><span class="point"></span>Livrée</span></td>
                                         </tr>
                                    -->
                                    <tbody id="cm-orders"></tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Pied : fermeture + lien fiche complète (href posé par le JS) -->
                    <div class="modal-footer">
                        <button type="button" class="btn-draft" data-bs-dismiss="modal">Fermer</button>
                        <a class="btn-admin-primary" id="cm-full-link" href="#">Voir la fiche complète</a>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>

</html>