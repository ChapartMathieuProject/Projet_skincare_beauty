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
            <div class="orders-tollbar">
                <div class="search-admin">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input class="input-admin" id="clients-search" type="search"
                        placeholder="Rechercher un client (nom, email, téléphone...">
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
                        <!-- *** généré par PHP juste présente en html pour tester -->
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
                                <a class="btn-row-action" href="admin_client.php?id=1" aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
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
                                <a class="btn-row-action" href="admin_client.php?id=2" aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
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
                                <a class="btn-row-action" href="admin_client.php?id=3" aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
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
                                <a class="btn-row-action" href="admin_client.php?id=4" aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
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
                                <a class="btn-row-action" href="admin_client.php?id=5" aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
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
                                <a class="btn-row-action" href="admin_client.php?id=6" aria-label="Voir la fiche client">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </section>

    </div>


</div>
</body>

</html>