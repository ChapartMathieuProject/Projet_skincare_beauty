<?php
// Protection de la page : accès réservé aux utilisateurs connectés
session_start();

//if (!isset($_SESSION['user_id'])) {
//    header('Location: login.php');
//    exit;
//}

$successMessage = '';
$errorMessage   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_message') {
        $subject = trim($_POST['contact_subject'] ?? '');
        $message = trim($_POST['contact_message'] ?? '');

        if (empty($subject) || empty($message)) {
            $errorMessage = 'Veuillez remplir tous les champs.';
        } elseif (mb_strlen($message) < 20) {
            $errorMessage = 'Votre message est trop court (20 caractères minimum).';
        } else {
            // TODO (CB) : enregistrer le ticket en BDD (table support_tickets)
            // et envoyer un email de confirmation à l'utilisateur via PHPMailer
            $successMessage = 'Votre message a bien été envoyé. Nous vous répondrons dans les 24 h.';
        }
    }
}

// On force une valeur vide en tête de liste pour obliger l'utilisateur à choisir explicitement un sujet.
$contactSubjects = [
    ''         => '-- Choisissez un sujet --',
    'order'    => 'Question sur une commande',
    'product'  => 'Renseignement produit',
    'return'   => 'Retour / remboursement',
    'delivery' => 'Problème de livraison',
    'account'  => 'Problème de compte',
    'other'    => 'Autre',
];

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
      <i class="fa-solid fa-headset" aria-hidden="true"></i>
      Nous Contacter
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

    <div class="contact-grid">

      <div class="security-card">
        <h2 class="security-card__title">
          <i class="fa-regular fa-paper-plane" aria-hidden="true"></i>
          Envoyer un message
        </h2>
        <form method="POST" action="contact.php" novalidate>
          <input type="hidden" name="action" value="send_message">

          <div class="form-group-profile">
            <label for="contact_subject" class="form-label-profile">Sujet</label>
            <select
              id="contact_subject"
              name="contact_subject"
              class="form-input-profile"
              required
            >
              <?php foreach ($contactSubjects as $value => $label): ?>
                <option
                  value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                  <?= (isset($_POST['contact_subject']) && $_POST['contact_subject'] === $value) ? 'selected' : '' ?>
                  <?= $value === '' ? 'disabled' : '' ?>
                >
                  <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group-profile">
            <label for="contact_message" class="form-label-profile">Votre message</label>
            <textarea
              id="contact_message"
              name="contact_message"
              class="form-input-profile form-textarea-profile"
              rows="5"
              placeholder="Décrivez votre problème ou votre question…"
              minlength="20"
              required
            ><?= htmlspecialchars($_POST['contact_message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <button type="submit" class="btn-rose-sm">Envoyer</button>
        </form>
      </div>

      <div class="contact-info">

        <div class="contact-info-card">
          <i class="fa-solid fa-clock" aria-hidden="true"></i>
          <div>
            <h3>Horaires du support</h3>
            <p>Lundi – Vendredi : 9h – 18h</p>
            <p>Réponse sous 24 h ouvrées</p>
          </div>
        </div>

        <div class="contact-info-card">
          <i class="fa-regular fa-envelope" aria-hidden="true"></i>
          <div>
            <h3>Par e-mail</h3>
            <p>
              <a href="mailto:support@skincarebeauty.fr">support@skincarebeauty.fr</a>
            </p>
          </div>
        </div>

      </div>

    </div>

  </div>
</main>

<?php include 'public/includes/footer.php'; ?>
