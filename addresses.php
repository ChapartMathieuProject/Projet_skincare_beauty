<?php
// Protection de la page : accès réservé aux utilisateurs connectés
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


require_once 'public/includes/db.php';

$successMessage = '';
$errorMessage   = '';

// On récupère la fiche client liée à l'utilisateur connecté : toutes les
// adresses sont rattachées à un client, pas directement à un utilisateur.
$customerStatement = $pdo->prepare(
    'SELECT customer_id_account, customer_name, customer_firstname
     FROM customers
     WHERE user_id = :userId'
);
$customerStatement->execute(['userId' => $_SESSION['user_id']]);
$customer = $customerStatement->fetch();

// Traitement de l'ajout d'une nouvelle adresse
if ($customer !== false && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $customerIdAccount = $customer['customer_id_account'];

    if ($_POST['action'] === 'add_address') {
        $addressLabel   = trim($_POST['address_label']   ?? '');
        $addressLine1   = trim($_POST['address_line1']   ?? '');
        $addressCity    = trim($_POST['address_city']    ?? '');
        $addressZip     = trim($_POST['address_zip']     ?? '');
        $addressCountry = trim($_POST['address_country'] ?? '');

        if (empty($addressLabel) || empty($addressLine1) || empty($addressCity) || empty($addressZip)) {
            $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            // Le destinataire n'est pas demandé dans le formulaire : on reprend
            // par défaut le nom du client connecté.
            $insertAddress = $pdo->prepare(
                'INSERT INTO addresses
                    (customer_id_account, address_label, address_name, address_firstname,
                     address_adress_1, address_postcode, address_city, address_country)
                 VALUES
                    (:customerIdAccount, :label, :lastName, :firstName,
                     :line1, :zip, :city, :country)'
            );
            $insertAddress->execute([
                'customerIdAccount' => $customerIdAccount,
                'label'             => $addressLabel,
                'lastName'          => $customer['customer_name'],
                'firstName'         => $customer['customer_firstname'],
                'line1'             => $addressLine1,
                'zip'               => $addressZip,
                'city'              => $addressCity,
                'country'           => $addressCountry !== '' ? $addressCountry : 'France',
            ]);

            $successMessage = 'Adresse ajoutée avec succès.';
        }
    }

    if ($_POST['action'] === 'delete_address') {
        $addressId = (int) ($_POST['address_id'] ?? 0);

        if ($addressId > 0) {
            // On vérifie que l'adresse appartient bien au client connecté avant
            // de la supprimer, pour empêcher qu'un utilisateur supprime l'adresse
            // de quelqu'un d'autre en modifiant l'ID dans le formulaire.
            $deleteAddress = $pdo->prepare(
                'DELETE FROM addresses
                 WHERE address_id = :addressId AND customer_id_account = :customerIdAccount'
            );
            $deleteAddress->execute([
                'addressId'         => $addressId,
                'customerIdAccount' => $customerIdAccount,
            ]);

            $successMessage = 'Adresse supprimée.';
        }
    }
}

// On récupère les adresses du client connecté, l'adresse par défaut en premier.
$addresses = [];

if ($customer !== false) {
    $addressesStatement = $pdo->prepare(
        'SELECT address_id, address_label, address_adress_1, address_city,
                address_postcode, address_country, address_is_default
         FROM addresses
         WHERE customer_id_account = :customerIdAccount
         ORDER BY address_is_default DESC, address_id ASC'
    );
    $addressesStatement->execute(['customerIdAccount' => $customer['customer_id_account']]);
    $addressesFromDatabase = $addressesStatement->fetchAll();

    foreach ($addressesFromDatabase as $addressRow) {
        $addresses[] = [
            'id'      => $addressRow['address_id'],
            'label'   => $addressRow['address_label'],
            'line1'   => $addressRow['address_adress_1'],
            'city'    => $addressRow['address_city'],
            'zip'     => $addressRow['address_postcode'],
            'country' => $addressRow['address_country'],
            'default' => (bool) $addressRow['address_is_default'],
        ];
    }
}

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