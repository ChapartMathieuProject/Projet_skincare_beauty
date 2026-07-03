<?php
// Ce fichier est atteint via le lien reçu par e-mail : ?token=xxxx
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'public/includes/db.php';

$errorMessage   = '';
$successMessage = '';
$token          = trim($_GET['token'] ?? $_POST['token'] ?? '');
$tokenIsValid   = false;

if ($token === '') {
    $errorMessage = 'Lien de réinitialisation invalide.';
} else {
    // On vérifie que le token existe et n'a pas expiré.
    $tokenStatement = $pdo->prepare(
        'SELECT password_resets.user_id, password_resets.reset_expires_at
         FROM password_resets
         WHERE reset_token = :token'
    );
    $tokenStatement->execute(['token' => $token]);
    $resetRequest = $tokenStatement->fetch();

    if ($resetRequest === false) {
        $errorMessage = 'Ce lien de réinitialisation est invalide ou a déjà été utilisé.';
    } elseif (strtotime($resetRequest['reset_expires_at']) < time()) {
        $errorMessage = 'Ce lien de réinitialisation a expiré. Merci d\'en demander un nouveau.';
    } else {
        $tokenIsValid = true;
    }
}

// Traitement du nouveau mot de passe
if ($tokenIsValid && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $errorMessage = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Les mots de passe ne correspondent pas.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $updatePassword = $pdo->prepare('UPDATE users SET user_password = :password WHERE user_id = :userId');
        $updatePassword->execute([
            'password' => $hashedPassword,
            'userId'   => $resetRequest['user_id'],
        ]);

        // Le token est à usage unique : on le supprime une fois utilisé.
        $deleteToken = $pdo->prepare('DELETE FROM password_resets WHERE reset_token = :token');
        $deleteToken->execute(['token' => $token]);

        $successMessage = 'Votre mot de passe a été modifié avec succès.';
        $tokenIsValid    = false; // On masque le formulaire après succès.
    }
}

include 'public/includes/header.php';
?>

<main class="auth-section">
  <div class="container">

    <h1 class="auth-card__title">Réinitialiser mon mot de passe</h1>

    <?php if ($successMessage !== ''): ?>
      <div class="profile-alert profile-alert--success" role="alert">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
      </div>
      <p class="auth-card__footer">
        <a href="login.php" class="auth-link">Se connecter</a>
      </p>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
      <div class="profile-alert profile-alert--error" role="alert">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($tokenIsValid): ?>
      <form method="POST" action="reset-password.php" novalidate>
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group-profile">
          <label for="password" class="form-label-profile">Nouveau mot de passe</label>
          <input
            type="password"
            id="password"
            name="password"
            class="form-input-profile"
            autocomplete="new-password"
            minlength="8"
            required
          >
          <span class="form-hint">8 caractères minimum.</span>
        </div>

        <div class="form-group-profile">
          <label for="confirm_password" class="form-label-profile">Confirmer le mot de passe</label>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            class="form-input-profile"
            autocomplete="new-password"
            required
          >
        </div>

        <button type="submit" class="btn-rose-sm">Réinitialiser</button>
      </form>
    <?php endif; ?>

  </div>
</main>

<?php include 'public/includes/footer.php'; ?>
