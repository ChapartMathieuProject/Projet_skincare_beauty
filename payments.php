<?php
// Accès réservé : on évite qu'un visiteur non connecté consulte des moyens de paiement.
session_start();

//if (!isset($_SESSION['user_id'])) {
   // header('Location: login.php');
   // exit;
//}

$successMessage = '';
$errorMessage = '';

// 1. On déclare d'abord le tableau initial des cartes
$savedCards = [
    [
        'id'      => 1,
        'brand'   => 'Visa',
        'last4'   => '4242',
        'expiry'  => '09/27',
        'default' => true,
        'icon'    => 'fa-brands fa-cc-visa',
    ],
    [
        'id'      => 2,
        'brand'   => 'Mastercard',
        'last4'   => '1234',
        'expiry'  => '03/26',
        'default' => false,
        'icon'    => 'fa-brands fa-cc-mastercard',
    ],
];

// 2. Traitement des formulaires en méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Action : Suppression d'une carte
    if ($_POST['action'] === 'delete_card') {
        $cardId = (int) ($_POST['card_id'] ?? 0);
        if ($cardId > 0) {
            foreach ($savedCards as $key => $card) {
                if ($card['id'] === $cardId) {
                    unset($savedCards[$key]);
                    $successMessage = 'Carte supprimée avec succès.';
                }
            }
            $savedCards = array_values($savedCards);
        }
    }

    // Action : Ajout d'une carte avec validations strictes
    if ($_POST['action'] === 'add_card') {
        // Nettoyage des entrées pour enlever les espaces accidentels
        $cardNumber = str_replace(' ', '', $_POST['card_number'] ?? '');
        $expiryDate = trim($_POST['expiry_date'] ?? '');
        $cardCvv    = trim($_POST['cvv'] ?? '');
        $cardBrand  = $_POST['brand'] ?? 'Visa';

        // Validation du numéro de carte : uniquement des chiffres et longueur entre 16 et 19
        if (!ctype_digit($cardNumber) || strlen($cardNumber) < 16 || strlen($cardNumber) > 19) {
            $errorMessage = 'Le numéro de carte est invalide. Il doit contenir uniquement des chiffres (16 à 19 caractères).';
        }
        // Validation du format de la date d'expiration (MM/AA)
        elseif (!preg_match('/^(0[1-9]|1[0-2])\/[2-3][0-9]$/', $expiryDate)) {
            $errorMessage = 'La date d\'expiration est invalide. Format attendu : MM/AA (ex : 09/27).';
        }
        // Validation du CVV : uniquement des chiffres, longueur de 3 ou 4 caractères
        elseif (!ctype_digit($cardCvv) || strlen($cardCvv) < 3 || strlen($cardCvv) > 4) {
            $errorMessage = 'Le code de sécurité (CVV) est invalide. Il doit contenir 3 ou 4 chiffres.';
        }
        // Si tout est valide, on procède à l'ajout simulé
        else {
            // Détermination de l'icône FontAwesome selon la marque sélectionnée
            $iconMapping = [
                'Visa'             => 'fa-brands fa-cc-visa',
                'Mastercard'       => 'fa-brands fa-cc-mastercard',
                'American Express' => 'fa-brands fa-cc-amex',
            ];
            $cardIcon = $iconMapping[$cardBrand] ?? 'fa-solid fa-credit-card';

            // On isole les 4 derniers chiffres pour l'affichage sécurisé
            $last4Digits = substr($cardNumber, -4);

            $savedCards[] = [
                'id'      => time(), // Génération d'un ID unique basé sur le timestamp
                'brand'   => $cardBrand,
                'last4'   => $last4Digits,
                'expiry'  => $expiryDate,
                'default' => false,
                'icon'    => $cardIcon,
            ];
            $successMessage = 'Nouvelle carte ajoutée avec succès !';
        }
    }
}

include 'public/includes/header.php';
?>

<main class="profile-main">
  <div class="container">

    <nav aria-label="Fil d'Ariane" class="profile-breadcrumb">
      <a href="/users.php">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Mon profil
      </a>
    </nav>

    <h1 class="profile-page-title">
      <i class="fa-solid fa-credit-card" aria-hidden="true"></i>
      Vos Paiements
    </h1>

    <?php if ($successMessage !== ''): ?>
      <div class="profile-alert profile-alert--success" role="alert">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
      <div class="profile-alert profile-alert--danger" role="alert" style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 0.75rem 1.25rem; border-radius: 0.25rem; margin-bottom: 1rem;">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <h2 class="profile-section-title">Cartes enregistrées</h2>

    <?php if (empty($savedCards)): ?>
      <div class="profile-empty">
        <i class="fa-solid fa-credit-card" aria-hidden="true"></i>
        <p>Aucune carte enregistrée pour le moment.</p>
      </div>
    <?php else: ?>
      <div class="payment-grid">
        <?php foreach ($savedCards as $card): ?>
          <div class="payment-card">
            <div class="payment-card__header">
              <i class="<?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8') ?> payment-card__brand-icon" aria-label="<?= htmlspecialchars($card['brand'], ENT_QUOTES, 'UTF-8') ?>"></i>
              <?php if ($card['default'] === true): ?>
                <span class="address-badge">Par défaut</span>
              <?php endif; ?>
            </div>
            <p class="payment-card__number">
              •••• •••• •••• <?= htmlspecialchars($card['last4'], ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p class="payment-card__expiry">
              Expire le <?= htmlspecialchars($card['expiry'], ENT_QUOTES, 'UTF-8') ?>
            </p>
            <form method="POST" action="payments.php" class="mt-2"
                  onsubmit="return confirm('Supprimer cette carte ?')">
              <input type="hidden" name="action" value="delete_card">
              <input type="hidden" name="card_id" value="<?= (int) $card['id'] ?>">
              <button type="submit" class="address-card__link address-card__link--danger" style="background: none; border: none; padding: 0; cursor: pointer;">
                Supprimer
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="payment-add-block" style="max-width: 480px;">
      <p class="payment-add-block__note">
        <i class="fa-solid fa-lock" aria-hidden="true"></i>
        Vos données bancaires sont chiffrées et sécurisées. Nous ne stockons jamais vos numéros de carte.
      </p>

      <form method="POST" action="payments.php" style="width: 100%; display: flex; flex-direction: column; gap: 0.75rem; text-align: left;">
        <input type="hidden" name="action" value="add_card">

        <div>
          <label for="brand" style="font-size: 0.85rem; font-weight: 600; color: var(--texte);">Type de carte</label>
          <select name="brand" id="brand" style="width: 100%; padding: 0.5rem; border: 1px solid #e5e0db; border-radius: 0.25rem;">
            <option value="Visa">Visa</option>
            <option value="Mastercard">Mastercard</option>
            <option value="American Express">American Express</option>
          </select>
        </div>

        <div>
          <label for="card_number" style="font-size: 0.85rem; font-weight: 600; color: var(--texte);">Numéro de carte</label>
          <input type="text" name="card_number" id="card_number" placeholder="4242424242424242" required style="width: 100%; padding: 0.5rem; border: 1px solid #e5e0db; border-radius: 0.25rem;">
        </div>

        <div style="display: flex; gap: 0.5rem;">
          <div style="flex: 1;">
            <label for="expiry_date" style="font-size: 0.85rem; font-weight: 600; color: var(--texte);">Expiration</label>
            <input type="text" name="expiry_date" id="expiry_date" placeholder="MM/AA" required style="width: 100%; padding: 0.5rem; border: 1px solid #e5e0db; border-radius: 0.25rem;">
          </div>
          <div style="flex: 1;">
            <label for="cvv" style="font-size: 0.85rem; font-weight: 600; color: var(--texte);">CVV</label>
            <input type="text" name="cvv" id="cvv" placeholder="123" required style="width: 100%; padding: 0.5rem; border: 1px solid #e5e0db; border-radius: 0.25rem;">
          </div>
        </div>

        <button type="submit" class="btn-rose-sm" style="margin-top: 0.5rem; align-self: center;">
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
          Ajouter une carte
        </button>
      </form>
    </div>

  </div>
</main>

<?php include 'public/includes/footer.php'; ?>