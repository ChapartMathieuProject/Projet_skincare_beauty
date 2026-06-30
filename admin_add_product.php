<?php $menu_actif = 'produits'; ?> <!-- Changer le 'produits' en fonction de la page -->
<?php include "public/includes/header_admin.php"; ?>






        <div class="admin-main">
            <header class="admin-topbar">
                <nav class="breadcrumb-admin">
                    Produits <span class="sep">›</span> <span class="current">Nouveau produit</span>
                </nav>
                <h1>Créer un produit</h1>
                <div class="topbar-status">
                    <span class="dot"></span>Modifications enregistrées
                </div>
                <div class="topbar-actions">
                    <button type="submit" form="form-produit" name="action" value="brouillon" class="btn-draft">
                        Enregistrer brouillon
                    </button>
                </div>
            </header>
            <form id="form-product" class="admin-content" action="" enctype="multipart/form-data">
                <section class="admin-card">
                    <div class="card-title">
                        <span class="num">1</span>
                        <h2>Informations générales</h2>
                    </div>
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label-admin" for="nom">Nom du produit <span class="req">*</span>
                            </label>
                            <input class="input-admin" type="text" id="nom" name="nom" value="Sérum Vitamin C Eclat" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-admin" for="marque">Marque</label>
                            <input class="input-admin" type="text" id="marque" name="marque" value="Glowlab">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-admin" for="categorie">Catégorie</label>
                            <select class="input-admin" id="categorie" name="categorie">
                                <option selected>Sérums</option>
                                <option>Crèmes</option>
                                <option>Parfums</option>
                                <option>Nettoyants</option>
                                <option>Masques</option>
                                <option>Soins ciblés</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-admin" for="ean">Référence (EAN)</label>
                            <input class="input-admin" id="ean" type="text" name="ean" value="GL-SVC-50">
                        </div>
                        <div class="col-12">
                            <label class="form-label-admin" for="benefices">
                                Bénéfices clés <span class="hint">- une ligne par puce, affichées sous le titre</span>
                            </label>
                            <textarea class="input-admin mono" id="benefices" name="benefices" rows="3">Lorem ipsum dolor sit</textarea>
                        </div>
                    </div>
                </section>
                <section class="admin-card">
                    <div class="card-title">
                        <span class="num">2</span>
                        <h2>Images du produit</h2>
                    </div>
                    <div class="upload-grid">
                        <label class="upload-zone is-primary">
                            <!-- *** Penser à rajouter du JS -->
                            <input type="file" name="images[]" accept="image/*" hidden>
                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                        </label>
                        <label class="upload-zone">
                            <input type="file" name="images[]" accept="image/*" hidden>
                            <i class="fa-solid fa-plus"></i>
                        </label>
                        <label class="upload-zone">
                            <input type="file" name="images[]" accept="image/*" hidden>
                            <i class="fa-solid fa-plus"></i>
                        </label>
                        <label class="upload-zone">
                            <input type="file" name="images[]" accept="image/*" hidden>
                            <i class="fa-solid fa-plus"></i>
                        </label>
                    </div>
                </section>
                <section class="admin-card">
                    <div class="card-title">
                        <span class="num">3</span>
                        <h2>Prix &amp; variantes</h2>
                    </div>
                    <div class="variants">
                        <div class="variant-head">
                            <span>Contenance</span>
                            <span>Prix (€)</span>
                            <span>Prix barré</span>
                            <span>Stock</span>
                            <span></span>
                        </div>
                        <div class="variant-row">
                            <!-- *** Penser à rajouter du JS -->
                            <input class="input-admin" type="text" name="contenance[]" value="30 ml">
                            <input class="input-admin" type="text" name="prix[]" value="39,90">
                            <input class="input-admin" type="text" name="prix_barre[]" value="54,90">
                            <input class="input-admin" type="number" name="stock[]" value="64">
                            <button type="button" class="btn-trash" aria-label="Supprimer la variante">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                        <div class="variant-row">
                            <input class="input-admin" type="text" name="contenance[]" value="50 ml">
                            <input class="input-admin" type="text" name="prix[]" value="59,90">
                            <input class="input-admin" type="text" name="prix_barre[]" value="79,90">
                            <input class="input-admin" type="number" name="stock[]" value="120">
                            <button type="button" class="btn-trash" aria-label="Supprimer la variante">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                        <!-- *** Penser à rajouter du JS -->
                        <button type="button" class="btn-add-variant" id="btn-add-variant">
                            <i class="fa-solid fa-plus"></i> Ajouter une variante
                        </button>
                    </div>
                </section>
                <section class="admin-card">
                    <div class="card-title">
                        <span class="num">4</span>
                        <h2>Contenu détaillé <span class="hint">- onglets de la fiche</span></h2>
                    </div>
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label-admin" for="description">Description</label>
                            <textarea class="input-admin mono" id="description" name="description" rows="5"
                                placeholder="Présenter le produit, sa promesse, sa texture"></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-admin" for="ingredients">Ingrédients (INCI)</label>
                            <textarea class="input-admin mono" id="ingredients" name="ingredients" rows="5"
                                placeholder="Aqua, Ascorbic Acid, Glycerin…"></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-admin" for="mode-emploi">Mode d'emploi</label>
                            <textarea class="input-admin mono" id="mode-emploi" name="mode_emploi" rows="5"
                                placeholder="Appliquer 3 à 4 gouttes matin et soir…"></textarea>
                        </div>
                    </div>
                </section>
                <section class="admin-card">
                    <div class="card-title">
                        <span class="num">5</span>
                        <h2>Disponibilité &amp; badges</h2>
                    </div>
                    <label class="form-label-admin">Statut du stock</label>
                    <div class="pill-group">
                        <input type="radio" class="pill-input" name="statut_stock" id="st-instock" value="en stock" checked>
                        <label class="pill" for="st-instock">En stock</label>

                        <input type="radio" class="pill-input" id="st-limite" name="statut_stock" value="limite">
                        <label class="pill" for="st-limite">Stock limité</label>

                        <input type="radio" class="pill-input" id="st-precommande" name="statut_stock" value="precommande">
                        <label class="pill" for="st-precommande">Précommande</label>
                    </div>
                    <div class="toggle-row">
                        <span class="label">
                            Afficher le badge promotion
                            <span class="muted">(remise calculée automatiquement)</span>
                        </span>
                        <label class="switch">
                            <input type="checkbox" name="badge_promo" value="1" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </section>

            </form>
        </div>
    </div>


</body>
</html>