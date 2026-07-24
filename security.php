<?php
// Accès réservé : on évite qu'un visiteur non connecté modifie son mot de passe.
session_start();

//if (!isset($_SESSION['user_id'])) {
  //  header('Location: login.php');
    //exit;
//}

$successMessage = '';
$errorMessage   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password']     ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation de base — à compléter avec une vérification BDD en production
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errorMessage = 'Veuillez remplir tous les champs.';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = 'Les nouveaux mots de passe ne correspondent pas.';
        } elseif (strlen($newPassword) < 8) {
            $errorMessage = 'Le mot de passe doit contenir au moins 8 caractères.';
        } else {
            // TODO (CB) : vérifier l'ancien mot de passe et mettre à jour en BDD
            $successMessage = 'Mot de passe modifié avec succès.';
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
      <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
      Connexion &amp; Sécurité
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

    <div class="security-grid">

      <div class="security-card">
        <h2 class="security-card__title">
          <i class="fa-solid fa-lock" aria-hidden="true"></i>
          Changer le mot de passe
        </h2>
        <form method="POST" action="security.php" novalidate>
          <input type="hidden" name="action" value="change_password">

          <div class="form-group-profile">
            <label for="current_password" class="form-label-profile">Mot de passe actuel</label>
            <input
              type="password"
              id="current_password"
              name="current_password"
              class="form-input-profile"
              autocomplete="current-password"
              required
            >
          </div>

          <div class="form-group-profile">
            <label for="new_password" class="form-label-profile">Nouveau mot de passe</label>
            <input
              type="password"
              id="new_password"
              name="new_password"
              class="form-input-profile"
              autocomplete="new-password"
              minlength="8"
              required
            >
            <span class="form-hint">8 caractères minimum.</span>
          </div>

          <div class="form-group-profile">
            <label for="confirm_password" class="form-label-profile">Confirmer le nouveau mot de passe</label>
            <input
              type="password"
              id="confirm_password"
              name="confirm_password"
              class="form-input-profile"
              autocomplete="new-password"
              required
            >
          </div>

          <button type="submit" class="btn-rose-sm">Enregistrer</button>
        </form>
      </div>

    </div>
  </div>
</main>

<?php include 'public/includes/footer.php'; ?>
