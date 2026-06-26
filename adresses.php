<?php
// Protection de la page : accès réservé aux utilisateurs connectés
session_start();

// Décommenter ce bloc pour la mise en production
/*
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
*/

$successMessage = '';
$errorMessage   = '';

// Traitement de l'ajout d'une nouvelle adresse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add_address') {
        $addressLabel    = trim($_POST['address_label']    ?? '');
        $addressLine1    = trim($_POST['address_line1']    ?? '');
        $addressCity     = trim($_POST['address_city']     ?? '');
        $addressZip      = trim($_POST['address_zip']      ?? '');
        $addressCountry  = trim($_POST['address_country']  ?? '');

        if (empty($addressLabel) || empty($addressLine1) || empty($addressCity) || empty($addressZip)) {
            $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            // TODO (CB) : insérer l'adresse en BDD (table users_addresses)
            $successMessage = 'Adresse ajoutée avec succès.';
        }
    }

    if ($_POST['action'] === 'delete_address') {
        $addressId = (int) ($_POST['address_id'] ?? 0);
        if ($addressId > 0) {
            // TODO (CB) : vérifier que l'adresse appartient bien à l'utilisateur avant suppression
            $successMessage = 'Adresse supprimée.';
        }
    }
}

// Adresses fictives — à remplacer par une requête BDD
$addresses = [
    [
        'id'      => 1,
        'label'   => 'Domicile',
        'line1'   => '12 rue des Acacias',
        'city'    => 'Châtellerault',
        'zip'     => '86100',
        'country' => 'France',
        'default' => true,
    ],
    [
        'id'      => 2,
        'label'   => 'Bureau',
        'line1'   => '5 avenue du Commerce',
        'city'    => 'Poitiers',
        'zip'     => '86000',
        'country' => 'France',
        'default' => false,
    ],
];

// Inclusion conforme à la structure du projet (sans public/)
include 'public/includes/header.php';
?>

<main class="profile-main">
  <div class="container">

    <nav aria-label="Fil d'Ariane" class="profile-breadcrumb">
      <a href="utilisateur.php">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Mon profil
      </a>
    </nav>

    <h1 class="profile-page-title">
      <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
      Adresses
    </h1>

    <?php if ($successMessage !== ''): ?>
      <div class="profile-alert profile-alert--success" role="alert">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
      <div class="profile-alert profile-alert--error" role="alert">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <div class="address-grid">

      <?php foreach ($addresses as $address): ?>
        <div class="address-card">
          <div class="address-card__header">
            <span class="address-card__label">
              <i class="fa-solid fa-house" aria-hidden="true"></i>
              <?= htmlspecialchars($address['label'], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <?php if ($address['default'] === true): ?>
              <span class="address-badge">Par défaut</span>
            <?php endif; ?>
          </div>
          <address class="address-card__body">
            <?= htmlspecialchars($address['line1'], ENT_QUOTES, 'UTF-8') ?><br>
            <?= htmlspecialchars($address['zip'], ENT_QUOTES, 'UTF-8') ?>
            <?= htmlspecialchars($address['city'], ENT_QUOTES, 'UTF-8') ?><br>
            <?= htmlspecialchars($address['country'], ENT_QUOTES, 'UTF-8') ?>
          </address>
          <div class="address-card__actions">
            <a href="#" class="address-card__link">Modifier</a>
            <form method="POST" action="addresses.php" class="d-inline"
              onsubmit="return confirm('Supprimer cette adresse ?')">
              <input type="hidden" name="action" value="delete_address">
              <input type="hidden" name="address_id" value="<?= (int) $address['id'] ?>">
              <button type="submit" class="address-card__link address-card__link--danger">
                Supprimer
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="address-card address-card--add">
        <button
          class="address-add-toggle"
          type="button"
          aria-expanded="false"
          aria-controls="formAddAddress"
          onclick="toggleAddressForm(this)"
        >
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
          Ajouter une adresse
        </button>

        <form
          method="POST"
          action="addresses.php"
          id="formAddAddress"
          class="address-form"
          novalidate
        >
          <input type="hidden" name="action" value="add_address">

          <div class="form-group-profile">
            <label for="address_label" class="form-label-profile">Libellé <span aria-hidden="true">*</span></label>
            <input
              type="text"
              id="address_label"
              name="address_label"
              class="form-input-profile"
              placeholder="ex : Domicile, Bureau…"
              required
            >
          </div>

          <div class="form-group-profile">
            <label for="address_line1" class="form-label-profile">Adresse <span aria-hidden="true">*</span></label>
            <input
              type="text"
              id="address_line1"
              name="address_line1"
              class="form-input-profile"
              placeholder="Numéro et nom de rue"
              required
            >
          </div>

          <div class="row g-2">
            <div class="col-4">
              <div class="form-group-profile">
                <label for="address_zip" class="form-label-profile">Code postal <span aria-hidden="true">*</span></label>
                <input
                  type="text"
                  id="address_zip"
                  name="address_zip"
                  class="form-input-profile"
                  maxlength="10"
                  required
                >
              </div>
            </div>
            <div class="col-8">
              <div class="form-group-profile">
                <label for="address_city" class="form-label-profile">Ville <span aria-hidden="true">*</span></label>
                <input
                  type="text"
                  id="address_city"
                  name="address_city"
                  class="form-input-profile"
                  required
                >
              </div>
            </div>
          </div>

          <div class="form-group-profile">
            <label for="address_country" class="form-label-profile">Pays</label>
            <input
              type="text"
              id="address_country"
              name="address_country"
              class="form-input-profile"
              value="France"
            >
          </div>

          <button type="submit" class="btn-rose-sm">Enregistrer l'adresse</button>
        </form>
      </div>

    </div></div>
</main>

<script>
  /**
   * Affiche ou masque le formulaire d'ajout d'adresse.
   * Met à jour aria-expanded pour l'accessibilité.
   *
   * @param {HTMLElement} btn - Le bouton déclencheur
   */
  function toggleAddressForm(btn) {
    const form = document.getElementById('formAddAddress');
    const isOpen = form.classList.toggle('address-form--visible');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }
</script>

<?php 
// Inclusion conforme à la structure du projet (sans public/)
include 'public/includes/footer.php'; 
?>