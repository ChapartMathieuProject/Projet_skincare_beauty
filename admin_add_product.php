
Admin add product · PHP
<?php
require_once "public/includes/db.php";
 
// Instanciation des DAO
$productDAO     = new ProductDAO($pdo);
$brandDAO       = new BrandDAO($pdo);
$productTypeDAO = new ProductTypeDAO($pdo);
 
$company_id = 1; //TODO : à recup depuis la session admin plus tard
 
$edit_id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int) $_GET['id'] : null;
 
$errors = [];
 
/* ---------- TRAITEMENT DU FORMULAIRE (uniquement en POST) ---------- */
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    // On NETTOIE et on VALIDE ici. On n'échappe PAS (htmlspecialchars) :
    // l'échappement se fait à l'affichage, via la fonction e() en bas de page.
    $product_id  = !empty($_POST['product_id']) ? (int) $_POST['product_id'] : null;
    $name        = trim($_POST['nom'] ?? '');
    $ean         = trim($_POST['ean'] ?? '');
    $composition = trim($_POST['composition'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $buy_price   = str_replace(',', '.', trim($_POST['prix_achat'] ?? ''));
    $margin      = (int) ($_POST['marge'] ?? 0);
    $quantity    = (int) ($_POST['stock'] ?? 0);
    $alert       = ($_POST['alerte'] ?? '') === '' ? null : (int) $_POST['alerte'];
    $brand_id    = (int) ($_POST['brand_id'] ?? 0);
    $type_id     = (int) ($_POST['product_type_id'] ?? 0);
    $is_status   = isset($_POST['actif']) ? 1 : 0;
 
    if ($name === '')                      $errors[] = "Le nom du produit est obligatoire.";
    if (!preg_match('/^\d{13}$/', $ean))   $errors[] = "L'EAN doit comporter exactement 13 chiffres.";
    if ($brand_id <= 0)                    $errors[] = "Choisis une marque.";
    if ($type_id <= 0)                     $errors[] = "Choisis une catégorie.";
    if (!is_numeric($buy_price))           $errors[] = "Le prix d'achat est invalide.";
 
    if (!$errors) {
 
        // On déduit le fabricant depuis la marque, via le DAO
        $brand = $brandDAO->find($brand_id);
 
        if ($brand === null) {
            $errors[] = "Marque introuvable.";
        } else {
            try {
                // On fabrique l'objet Product à partir du POST.
                // Les CLÉS sont les noms de colonnes SQL : c'est ce que Product::hydrate() attend.
                // product_id et product_slug sont absents : auto-increment + trigger s'en chargent.
                $product = new Product([
                    'product_name'        => $name,
                    'product_ean'         => $ean,
                    'product_composition' => $composition,
                    'product_description' => $description,
                    'product_is_status'   => $is_status,
                    'product_buy_price'   => $buy_price,
                    'product_margin'      => $margin,
                    'product_quantity'    => $quantity,
                    'product_alert'       => $alert,
                    'producer_id'         => $brand->getProducerId(),
                    'brand_id'            => $brand_id,
                    'company_id_account'  => $company_id,
                ]);
 
                if ($product_id) {
                    // UPDATE : le DAO construit le SQL depuis dehydrate()
                    $productDAO->update($product_id, $product);
                    $saved_id = $product_id;
                } else {
                    // INSERT : create() renvoie le nouvel id
                    $saved_id = $productDAO->create($product);
                }
 
                // lien_product_type : table de liaison simple, requête directe
                $pdo->prepare(
                    "INSERT INTO lien_product_type (product_id, product_type_id)
                     VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE product_type_id = VALUES(product_type_id)"
                )->execute([$saved_id, $type_id]);
 
                // Le slug est généré par le trigger : on relit le produit pour l'obtenir
                $saved = $productDAO->find($saved_id);
 
                header("Location: product.php?slug=" . urlencode($saved->getSlug()));
                exit;
 
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') { //Le 23000 est un code d'erreur SQL standard : il signale une violation de contrainte
                    $errors[] = "Cet EAN existe déjà : il doit être unique.";
                } else {
                    $errors[] = "Erreur d'enregistrement : " . $e->getMessage();
                }
            }
        }
    }
}
 
/* ---------- CHARGEMENT POUR L'AFFICHAGE ---------- */
 
$menu_actif = "ajouter un produits";
 
// Dictionnaires d'entités pour les listes déroulantes
$brands = $brandDAO->findAllKeyedById();  
$types  = $productTypeDAO->findAllKeyedById(); 
 
// $p reste un TABLEAU : c'est l'état du formulaire, pas une entité.
// Il doit pouvoir contenir une saisie invalide (ex. prix "abc"),
// ce qu'un objet Product typé refuserait.
$p = [
    'product_name' => '', 'product_ean' => '', 'product_composition' => '', 'product_description' => '',
    'product_is_status' => 1, 'product_buy_price' => '', 'product_margin' => '', 'product_quantity' => '',
    'product_alert' => '', 'brand_id' => 0,
];
$current_type = 0;
 
if ($edit_id) {
    $product_edit = $productDAO->find($edit_id);
 
    if ($product_edit !== null) {
        // On remplit le tableau du formulaire depuis les getters de l'entité
        $p = [
            'product_name'        => $product_edit->getName(),
            'product_ean'         => $product_edit->getEan(),
            'product_composition' => $product_edit->getComposition(),
            'product_description' => $product_edit->getDescription(),
            'product_is_status'   => $product_edit->isStatus() ? 1 : 0,
            'product_buy_price'   => $product_edit->getBuyPrice(),
            'product_margin'      => $product_edit->getMargin(),
            'product_quantity'    => $product_edit->getQuantity(),
            'product_alert'       => $product_edit->getAlert(),
            'brand_id'            => $product_edit->getBrandId(),
        ];
 
        // Catégorie courante : table de liaison, requête directe
        $t = $pdo->prepare("SELECT product_type_id FROM lien_product_type WHERE product_id = ?");
        $t->execute([$edit_id]);
        $current_type = (int) $t->fetchColumn();
    } else {
        $edit_id = null; // Si id inexistant on retourne sur la création
    }
}
 
$mode_edition = $edit_id !== null;
 
// En cas d'erreur, on réaffiche ce que l'utilisateur avait saisi (valeurs brutes du POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errors) {
    $p['product_name']        = $_POST['nom'] ?? '';
    $p['product_ean']         = $_POST['ean'] ?? '';
    $p['product_composition'] = $_POST['composition'] ?? '';
    $p['product_description'] = $_POST['description'] ?? '';
    $p['product_buy_price']   = $_POST['prix_achat'] ?? '';
    $p['product_margin']      = $_POST['marge'] ?? '';
    $p['product_quantity']    = $_POST['stock'] ?? '';
    $p['product_alert']       = $_POST['alerte'] ?? '';
    $p['product_is_status']   = isset($_POST['actif']) ? 1 : 0;
    $p['brand_id']            = (int) ($_POST['brand_id'] ?? 0);
    $current_type             = (int) ($_POST['product_type_id'] ?? 0);
}
 
function e($v) { return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
 
 
<?php include "public/includes/header_admin.php"; ?>
<div class="admin-main">
    <header class="admin-topbar">
        <nav class="breadcrumb-admin">
            Produits <span class="sep">›</span>
            <span class="current"><?= $mode_edition ? 'Modifier' : 'Nouveau produit' ?></span>
        </nav>
        <h1><?= $mode_edition ? 'Modifier le produit' : 'Créer un produit' ?></h1>
 
        <div class="topbar-actions">
            <button type="submit" form="form-product" class="btn-admin-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                <?= $mode_edition ? 'Enregistrer les modifications' : 'Publier le produit' ?>
            </button>
        </div>
    </header>
 
    <form id="form-product" class="admin-content" method="post" action="" enctype="multipart/form-data">
 
        <?php if ($mode_edition): ?>
            <input type="hidden" name="product_id" value="<?= (int) $edit_id ?>">
        <?php endif; ?>
 
        <!-- Messages -->
        <?php if (!empty($_GET['saved'])): ?>
            <div class="profile-alert profile-alert--success">
                <i class="fa-solid fa-check"></i> Produit enregistré avec succès.
            </div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="profile-alert profile-alert--error">
                <ul class="m-0 ps-3">
                    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
 
        <!-- ====== 1. Informations générales ====== -->
        <section class="admin-card">
            <div class="card-title">
                <span class="num">1</span>
                <h2>Informations générales</h2>
            </div>
 
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <label class="form-label-admin" for="nom">Nom du produit <span class="req">*</span></label>
                    <input class="input-admin" type="text" id="nom" name="nom"
                           value="<?= e($p['product_name']) ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label-admin" for="ean">EAN (13 chiffres) <span class="req">*</span></label>
                    <input class="input-admin" type="text" id="ean" name="ean"
                           value="<?= e($p['product_ean']) ?>"
                           pattern="\d{13}" maxlength="13" inputmode="numeric" required>
                </div>
 
                <div class="col-12 col-md-6">
                    <label class="form-label-admin" for="brand_id">Marque <span class="req">*</span></label>
                    <select class="input-admin" id="brand_id" name="brand_id">
                        <option value="">— Choisir —</option>
                        <?php foreach ($brands as $b): ?>
                            <!-- $brands contient des objets Brand -->
                            <option value="<?= $b->getId() ?>"
                                <?= ((int) $p['brand_id'] === $b->getId()) ? 'selected' : '' ?>>
                                <?= e($b->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label-admin" for="product_type_id">Catégorie <span class="req">*</span></label>
                    <select class="input-admin" id="product_type_id" name="product_type_id">
                        <option value="">— Choisir —</option>
                        <?php foreach ($types as $t): ?>
                            <!-- $types contient des objets ProductType -->
                            <option value="<?= $t->getId() ?>"
                                <?= ($current_type === $t->getId()) ? 'selected' : '' ?>>
                                <?= e($t->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
 
                <div class="col-12">
                    <label class="form-label-admin" for="description">Description <span class="hint">— 500 caractères max</span></label>
                    <textarea class="input-admin mono" id="description" name="description" rows="4"
                              maxlength="500" placeholder="Présentez le produit…"><?= e($p['product_description']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label-admin" for="composition">Composition (INCI) <span class="hint">— 200 caractères max</span></label>
                    <textarea class="input-admin mono" id="composition" name="composition" rows="3"
                              maxlength="200" placeholder="Aqua, Glycerin…"><?= e($p['product_composition']) ?></textarea>
                </div>
            </div>
        </section>
 
        <!-- ====== 2. Prix & stock (1 seul jeu, pas de variantes) ====== -->
        <section class="admin-card">
            <div class="card-title">
                <span class="num">2</span>
                <h2>Prix &amp; stock</h2>
            </div>
 
            <div class="row g-4">
                <div class="col-12 col-md-3">
                    <label class="form-label-admin" for="prix_achat">Prix d'achat (€) <span class="req">*</span></label>
                    <input class="input-admin" type="text" id="prix_achat" name="prix_achat"
                           value="<?= e($p['product_buy_price']) ?>" placeholder="15,00">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label-admin" for="marge">Marge (%)</label>
                    <input class="input-admin" type="number" id="marge" name="marge"
                           value="<?= e($p['product_margin']) ?>" min="0">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label-admin" for="stock">Stock</label>
                    <input class="input-admin" type="number" id="stock" name="stock"
                           value="<?= e($p['product_quantity']) ?>" min="0">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label-admin" for="alerte">Seuil d'alerte</label>
                    <input class="input-admin" type="number" id="alerte" name="alerte"
                           value="<?= e($p['product_alert']) ?>" min="0">
                </div>
            </div>
        </section>
 
        <!-- ====== 3. Disponibilité ====== -->
        <section class="admin-card">
            <div class="card-title">
                <span class="num">3</span>
                <h2>Disponibilité</h2>
            </div>
 
            <div class="toggle-row">
                <span class="label">
                    Produit actif
                    <span class="muted">(visible sur la boutique)</span>
                </span>
                <label class="switch">
                    <input type="checkbox" name="actif" value="1"
                        <?= ((int) $p['product_is_status'] === 1) ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
            </div>
        </section>
 
        <!-- ====== 4. Images (affichage seulement — traitement à venir) ====== -->
        <section class="admin-card">
            <div class="card-title">
                <span class="num">4</span>
                <h2>Images du produit <span class="hint">— upload non enregistré pour l'instant</span></h2>
            </div>
            <div class="upload-grid">
                <label class="upload-zone is-primary">
                    <input type="file" name="images[]" accept="image/*" hidden>
                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                </label>
                <label class="upload-zone"><input type="file" name="images[]" accept="image/*" hidden><i class="fa-solid fa-plus"></i></label>
                <label class="upload-zone"><input type="file" name="images[]" accept="image/*" hidden><i class="fa-solid fa-plus"></i></label>
                <label class="upload-zone"><input type="file" name="images[]" accept="image/*" hidden><i class="fa-solid fa-plus"></i></label>
            </div>
        </section>
 
    </form>
</div>
</div>
 
</body>
</html>
 
















