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

// Affiche le message d'invitation si l'utilisateur arrive initialement depuis le panier
if (isset($_GET['error']) && $_GET['error'] === 'no_address' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errorMessage = "Pour finaliser votre commande, veuillez d'abord enregistrer une adresse de livraison.";
}

// On récupère la fiche client liée à l'utilisateur connecté
$customerStatement = $pdo->prepare(
    'SELECT customer_id_account, customer_name, customer_firstname
     FROM customers
     WHERE user_id = :userId'
);
$customerStatement->execute(['userId' => $_SESSION['user_id']]);
$customer = $customerStatement->fetch();

// Traitement du formulaire (POST)
if ($customer !== false && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $customerIdAccount = $customer['customer_id_account'];

    // Action : AJOUTER OU MODIFIER UNE ADRESSE (Même structure de formulaire)
    if ($_POST['action'] === 'add_address' || $_POST['action'] === 'edit_address') {
        $addressLabel   = trim($_POST['address_label']   ?? '');
        $addressLine1   = trim($_POST['address_line1']   ?? '');
        $addressCity    = trim($_POST['address_city']    ?? '');
        $addressZip     = trim($_POST['address_zip']     ?? '');
        $addressCountry = trim($_POST['address_country'] ?? '');
        $addressId      = (int) ($_POST['address_id']    ?? 0);

        if (empty($addressLabel) || empty($addressLine1) || empty($addressCity) || empty($addressZip)) {
            $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            if ($_POST['action'] === 'add_address') {
                // INSERTION
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
            } else {
                // MODIFICATION (UPDATE) - Sécurité : on vérifie aussi le customer_id_account
                $updateAddress = $pdo->prepare(
                    'UPDATE addresses 
                     SET address_label = :label, 
                         address_adress_1 = :line1, 
                         address_postcode = :zip, 
                         address_city = :city, 
                         address_country = :country
                     WHERE address_id = :addressId AND customer_id_account = :customerIdAccount'
                );
                $updateAddress->execute([
                    'label'             => $addressLabel,
                    'line1'             => $addressLine1,
                    'zip'               => $addressZip,
                    'city'              => $addressCity,
                    'country'           => $addressCountry !== '' ? $addressCountry : 'France',
                    'addressId'         => $addressId,
                    'customerIdAccount' => $customerIdAccount
                ]);
                $successMessage = 'Adresse modifiée avec succès.';
            }

            // SI LE CLIENT VENAIT DU PANIER, ON LE RENVOIE DIRECTEMENT VERS LE CHECKOUT
            if (isset($_GET['from']) && $_GET['from'] === 'checkout') {
                header('Location: checkout.php');
                exit;
            }
        }
    }

    // Action : SUPPRIMER UNE ADRESSE
    if ($_POST['action'] === 'delete_address') {
        $addressId = (int) ($_POST['address_id'] ?? 0);

        if ($addressId > 0) {
            // 1. Compter le nombre total d'adresses
            $countStatement = $pdo->prepare('SELECT COUNT(*) FROM addresses WHERE customer_id_account = :customerIdAccount');
            $countStatement->execute(['customerIdAccount' => $customerIdAccount]);
            $totalAddresses = (int) $countStatement->fetchColumn();

            if ($totalAddresses <= 1) {
                $errorMessage = "Vous devez conserver au moins une adresse sur votre compte.";
            } else {
                // 2. Récupérer les détails de l'adresse qu'on veut supprimer
                $checkStatement = $pdo->prepare('SELECT address_is_default, address_is_billing FROM addresses WHERE address_id = :addressId');
                $checkStatement->execute(['addressId' => $addressId]);
                $addressToDelete = $checkStatement->fetch();

                if ($addressToDelete['address_is_default'] == 1) {
                    $errorMessage = "Impossible de supprimer votre adresse de livraison par défaut. Veuillez d'abord en définir une autre par défaut.";
                } elseif ($addressToDelete['address_is_billing'] == 1) {
                    $errorMessage = "Impossible de supprimer votre adresse de facturation principale. Veuillez d'abord en définir une autre pour la facturation.";
                } else {
                    try {
                        $deleteAddress = $pdo->prepare(
                            'DELETE FROM addresses
                             WHERE address_id = :addressId AND customer_id_account = :customerIdAccount'
                        );
                        $deleteAddress->execute([
                            'addressId'         => $addressId,
                            'customerIdAccount' => $customerIdAccount,
                        ]);
                        $successMessage = 'Adresse supprimée avec succès.';
                    } catch (PDOException $e) {
                        if ($e->getCode() === '23000') {
                            $errorMessage = "Cette adresse est liée à un historique de commande et ne peut pas être supprimée.";
                        } else {
                            $errorMessage = "Erreur lors de la suppression : " . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}

// On récupère les adresses du client connecté
$addresses = [];

if ($customer !== false) {
    $addressesStatement = $pdo->prepare(
        'SELECT address_id, address_label, address_adress_1, address_city,
                address_postcode, address_country, address_is_default, address_is_billing
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
            'billing' => (bool) $addressRow['address_is_billing'],
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
              <span class="address-badge">Livraison par défaut</span>
            <?php endif; ?>

            <?php if ($address['billing'] === true): ?>
              <span class="address-badge" style="background-color: #6c757d;">Facturation</span>
            <?php endif; ?>
          </div>
          
          <address class="address-card__body">
            <?= htmlspecialchars($address['line1'], ENT_QUOTES, 'UTF-8') ?><br>
            <?= htmlspecialchars($address['zip'], ENT_QUOTES, 'UTF-8') ?>
            <?= htmlspecialchars($address['city'], ENT_QUOTES, 'UTF-8') ?><br>
            <?= htmlspecialchars($address['country'], ENT_QUOTES, 'UTF-8') ?>
          </address>
          
          <div class="address-card__actions">
            <button type="button" class="address-card__link" style="background: none; border: none; cursor: pointer;"
                    onclick="initEditAddress(this)"
                    data-id="<?= (int) $address['id'] ?>"
                    data-label="<?= htmlspecialchars($address['label'], ENT_QUOTES, 'UTF-8') ?>"
                    data-line1="<?= htmlspecialchars($address['line1'], ENT_QUOTES, 'UTF-8') ?>"
                    data-zip="<?= htmlspecialchars($address['zip'], ENT_QUOTES, 'UTF-8') ?>"
                    data-city="<?= htmlspecialchars($address['city'], ENT_QUOTES, 'UTF-8') ?>"
                    data-country="<?= htmlspecialchars($address['country'], ENT_QUOTES, 'UTF-8') ?>">
              Modifier
            </button>
            
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
          id="formToggleBtn"
          aria-expanded="false"
          aria-controls="formAddAddress"
          onclick="toggleAddressForm(this)">
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
          Ajouter une adresse
        </button>

        <form
          method="POST"
          action="addresses.php"
          id="formAddAddress"
          class="address-form"
          novalidate>
          
          <input type="hidden" name="action" id="formActionField" value="add_address">
          <input type="hidden" name="address_id" id="formAddressIdField" value="0">

          <div class="form-group-profile">
            <label for="address_label" class="form-label-profile">Libellé <span aria-hidden="true">*</span></label>
            <input
              type="text"
              id="address_label"
              name="address_label"
              class="form-input-profile"
              placeholder="ex : Domicile, Bureau…"
              required>
          </div>

          <div class="form-group-profile">
            <label for="address_line1" class="form-label-profile">Adresse <span aria-hidden="true">*</span></label>
            <input
              type="text"
              id="address_line1"
              name="address_line1"
              class="form-input-profile"
              placeholder="Numéro et nom de rue"
              required>
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
                  required>
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
                  required>
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
              value="France">
          </div>

          <div class="d-flex flex-column gap-2 mt-2">
            <button type="submit" id="formSubmitBtn" class="btn-rose-sm">Enregistrer l'adresse</button>
            <button type="button" id="formCancelBtn" class="btn btn-light btn-sm" style="display: none;" onclick="resetToAddMode()">Annuler la modification</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</main>

<script>
  /**
   * Ouvre ou ferme le formulaire d'adresse.
   */
  function toggleAddressForm(btn) {
    const form = document.getElementById('formAddAddress');
    const isOpen = form.classList.toggle('address-form--visible');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    
    // Si on ferme manuellement le formulaire ouvert en mode édition, on réinitialise en mode ajout
    if (!isOpen && document.getElementById('formActionField').value === 'edit_address') {
        resetToAddMode();
    }
  }

  /**
   * Injecte les données de l'adresse dans la card formulaire et l'affiche en mode MODIFICATION.
   */
  function initEditAddress(btn) {
    const form = document.getElementById('formAddAddress');
    const toggleBtn = document.getElementById('formToggleBtn');
    
    // 1. Remplissage des inputs du formulaire avec les attributs data-* du bouton
    document.getElementById('formActionField').value = 'edit_address';
    document.getElementById('formAddressIdField').value = btn.getAttribute('data-id');
    document.getElementById('address_label').value = btn.getAttribute('data-label');
    document.getElementById('address_line1').value = btn.getAttribute('data-line1');
    document.getElementById('address_zip').value = btn.getAttribute('data-zip');
    document.getElementById('address_city').value = btn.getAttribute('data-city');
    document.getElementById('address_country').value = btn.getAttribute('data-country');
    
    // 2. Mise à jour des textes et affichage du bouton Annuler
    document.getElementById('formSubmitBtn').innerText = 'Enregistrer les modifications';
    document.getElementById('formCancelBtn').style.display = 'block';
    toggleBtn.innerHTML = '<i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Modifier une adresse';
    
    // 3. Forcer l'ouverture visuelle de la card si elle était fermée
    if (!form.classList.contains('address-form--visible')) {
        form.classList.add('address-form--visible');
        toggleBtn.setAttribute('aria-expanded', 'true');
    }
    
    // Auto-scroll doux vers le formulaire pour le confort de l'utilisateur
    form.scrollIntoView({ behavior: 'smooth' });
  }

  /**
   * Réinitialise le formulaire pour le remettre en mode AJOUT normal.
   */
  function resetToAddMode() {
    document.getElementById('formAddAddress').reset();
    document.getElementById('formActionField').value = 'add_address';
    document.getElementById('formAddressIdField').value = '0';
    document.getElementById('address_country').value = 'France';
    
    document.getElementById('formSubmitBtn').innerText = 'Enregistrer l\'adresse';
    document.getElementById('formCancelBtn').style.display = 'none';
    
    const toggleBtn = document.getElementById('formToggleBtn');
    toggleBtn.innerHTML = '<i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter une adresse';
  }
</script>

<?php
// Inclusion conforme à la structure du projet (sans public/)
include 'public/includes/footer.php';
?>