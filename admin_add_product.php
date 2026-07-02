<?php 
require_once "public/includes/db.php";

$company_id = 1; //TODO : à recup depuis la session admin plus tard

$edit_id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int) $_GET['id'] : null;

$errors = [];
$cat_errors = [];

/* ---------- TRAITEMENT DU FORMULAIRE (uniquement en POST) ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    // --- CAS 1 : AJOUT D'UNE CATÉGORIE ---
    if (isset($_POST['category_name'])) {
        $cat_name = htmlspecialchars(trim($_POST['category_name']));

        if ($cat_name === '') {
            $cat_errors[] = "Le nom de la catégorie ne peut pas être vide.";
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_types WHERE LOWER(product_type_name) = LOWER(?)");
            $stmt->execute([$cat_name]);
            if ($stmt->fetchColumn() > 0) {
                $cat_errors[] = "Cette catégorie existe déjà.";
            }
        }

        if (!$cat_errors) {
            try {
                $stmt = $pdo->prepare("INSERT INTO product_types (product_type_name) VALUES (?)");
                $stmt->execute([$cat_name]);

                $redirect_url = "admin_add_product.php" . ($edit_id ? "?id=" . $edit_id . "&cat_added=1" : "?cat_added=1");
                header("Location: " . $redirect_url);
                exit;
            } catch (PDOException $e) {
                $cat_errors[] = "Erreur lors de la création de la catégorie : " . $e->getMessage();
            }
        }
    }

    // --- CAS 2 : ENREGISTREMENT DU PRODUIT (Ajout ou Modification) ---
    if (isset($_POST['nom'])) {
        $product_id     = !empty($_POST['product_id']) ? (int) $_POST['product_id'] : null;
        $name           = htmlspecialchars(trim($_POST['nom'] ?? ''));
        $ean            = htmlspecialchars(trim($_POST['ean'] ?? ''));
        $composition    = htmlspecialchars(trim($_POST['composition'] ?? ''));
        $description    = htmlspecialchars(trim($_POST['description'] ?? '')); 
        $buy_price      = htmlspecialchars(str_replace(',', '.', trim($_POST['prix_achat'] ?? '')));
        $margin         = htmlspecialchars((int) ($_POST['marge'] ?? 0));
        $quantity       = htmlspecialchars((int) ($_POST['stock'] ?? 0));
        $alert          = ($_POST['alerte'] ?? '') === '' ? null : (int) $_POST['alerte'];
        $brand_id       = (int) ($_POST['brand_id'] ?? 0);
        $type_id        = (int) ($_POST['product_type_id'] ?? 0);
        $is_status      = isset($_POST['actif']) ? 1 : 0;

        if($name === '')                        $errors[] = "Le nom du produit est obligatoire.";
        if (!preg_match('/^\d{13}$/', $ean))    $errors[] = "L'EAN doit comporter exactement 13 chiffres.";
        if ($brand_id <= 0)                     $errors[] = "Choisis une marque.";
        if ($type_id <= 0)                      $errors[] = "Choisis une catégorie.";
        if (!is_numeric($buy_price))            $errors[] = "Le prix d'achat est invalide.";

        if (!$errors) {
            $stmt = $pdo->prepare("SELECT producer_id FROM brands WHERE brand_id = ?"); 
            $stmt->execute([$brand_id]);
            $producer_id = $stmt->fetchColumn();

            if(!$producer_id) {
                $errors[] = "Marque introuvable.";
            } else {
                try {
                    if ($product_id){
                        $sql = "UPDATE products SET
                                    product_name = ?, product_ean = ?, product_composition = ?,
                                    product_description = ?, product_is_status = ?, product_buy_price = ?,
                                    product_margin = ?, product_quantity = ?, product_alert = ?,
                                    producer_id = ?, brand_id = ?
                                WHERE product_id = ?";
                        
                        $pdo->prepare($sql)->execute([
                            $name, $ean, $composition, $description, $is_status, $buy_price,
                            $margin, $quantity, $alert, $producer_id, $brand_id, $product_id,
                        ]);
                        $saved_id =$product_id;
                    } else {
                        $sql = "INSERT INTO products
                                    (product_name, product_ean, product_composition, product_description,
                                    product_is_status, product_buy_price, product_margin, product_quantity,
                                    product_alert, producer_id, brand_id, company_id_account)
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
                        $pdo->prepare($sql)->execute([
                            $name, $ean, $composition, $description, $is_status, $buy_price,
                            $margin, $quantity, $alert, $producer_id, $brand_id, $company_id,
                        ]);
                        $saved_id = (int) $pdo->lastInsertId();
                    }
                    
                    $pdo->prepare(
                        "INSERT INTO lien_product_type (product_id, product_type_id)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE product_type_id = VALUES(product_type_id)"
                    )->execute([$saved_id, $type_id]);

                    $stmt = $pdo->prepare("SELECT product_slug FROM products WHERE product_id = ?");
                    $stmt->execute([$saved_id]);
                    $slug = $stmt->fetchColumn();

                    header("Location: product.php?slug=" . urlencode($slug));
                    exit;

                } catch (PDOException $e) {
                    if ($e->getCode() === '23000'){ 
                        $errors[] = "Cet EAN existe déjà : il doit être unique.";
                    } else {
                        $errors[] = "Erreur d'enregistrement :" . $e->getMessage();
                    }
                }
            } 
        }
    }
}

/* ---------- CHARGEMENT POUR L'AFFICHAGE ---------- */

$menu_actif = "ajouter un produit";

$brands = $pdo->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name")->fetchAll();
$types = $pdo->query("SELECT product_type_id, product_type_name FROM product_types ORDER BY product_type_name")->fetchAll();

$p = [
    'product_name' => '', 'product_ean' => '', 'product_composition' => '', 'product_description' => '',
    'product_is_status' => 1, 'product_buy_price' => '', 'product_margin' => '', 'product_quantity' => '',
    'product_alert' => '', 'brand_id' => 0,
];
$current_type = 0;

if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$edit_id]);
    $row = $stmt->fetch();
    if ($row) {
        $p = $row;
        $t = $pdo->prepare("SELECT product_type_id FROM lien_product_type WHERE product_id = ?");
        $t->execute([$edit_id]);
        $current_type = (int) $t->fetchColumn();
    } else {
        $edit_id = null; 
    }
}
 
$mode_edition = $edit_id !== null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom']) && $errors) {
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
                            <option value="<?= (int) $b['brand_id'] ?>"
                                <?= ((int) $p['brand_id'] === (int) $b['brand_id']) ? 'selected' : '' ?>>
                                <?= e($b['brand_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label-admin" for="product_type_id">Catégorie <span class="req">*</span></label>
                    <select class="input-admin" id="product_type_id" name="product_type_id">
                        <option value="">— Choisir —</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= (int) $t['product_type_id'] ?>"
                                <?= ($current_type === (int) $t['product_type_id']) ? 'selected' : '' ?>>
                                <?= e($t['product_type_name']) ?>
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

    <section class="admin-card" style="margin-top: 2rem;">
        <div class="card-title">
            <i class="fa-solid fa-tags" style="font-size: 1.25rem; margin-right: 10px; color: var(--admin-primary, #4f46e5);"></i>
            <h2>Créer une nouvelle catégorie</h2>
        </div>

        <?php if (isset($_GET['cat_added'])): ?>
            <div class="profile-alert profile-alert--success" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-check"></i> Catégorie ajoutée avec succès ! Elle est désormais disponible dans la liste ci-dessus.
            </div>
        <?php endif; ?>

        <?php if ($cat_errors): ?>
            <div class="profile-alert profile-alert--error" style="margin-bottom: 1.5rem;">
                <ul class="m-0 ps-3">
                    <?php foreach ($cat_errors as $c_err): ?><li><?= e($c_err) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-8">
                    <label class="form-label-admin" for="category_name">Nom de la catégorie</label>
                    <input class="input-admin" type="text" id="category_name" name="category_name" placeholder="Ex: Crème de nuit, Sérum..." required>
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn-admin-primary" style="width: 100%; justify-content: center; height: 42px;">
                        <i class="fa-solid fa-plus"></i> Ajouter la catégorie
                    </button>
                </div>
            </div>
        </form>

        <div style="margin-top: 1.5rem;">
            <p class="form-label-admin" style="margin-bottom: 0.5rem;">Catégories existantes (<?= count($types) ?>) :</p>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                <?php foreach ($types as $t): ?>
                    <span style="background: #f3f4f6; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; color: #374151; border: 1px solid #e5e7eb;">
                        <?= e($t['product_type_name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
</div>
 
</body>
</html>