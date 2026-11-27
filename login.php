<?php
// Portail de connexion CLIENT : les comptes administrateur et SAV passent par admin_login.php.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'public/includes/auth.php';

// On redirige un utilisateur déjà connecté vers son espace : pas de raison de lui remontrer le formulaire.
if (is_logged()) {
    if (is_admin()) {
        header('Location: admin_dashboard.php');
    } elseif (is_sav()) {
        header('Location: admin_tickets.php');
    } else {
        header('Location: users.php');
    }
    exit;
}

require_once 'public/includes/db.php';

$errorMessage = '';
$email        = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email      = trim((string) ($_POST['email'] ?? ''));
    $password   = (string) ($_POST['password'] ?? '');
    $rememberMe = isset($_POST['remember_me']);

    if ($email === '' || $password === '') {
        $errorMessage = 'Veuillez renseigner votre e-mail et votre mot de passe.';
    } else {
        // Seuls les clients sont acceptés ici. On reste volontairement vague en cas d'échec
        // (compte inconnu, mauvais mot de passe ou compte admin/SAV) pour ne pas faciliter
        // l'énumération des comptes existants.
        $user = authenticate($pdo, $email, $password, [USER_TYPE_CLIENT]);

        if ($user !== null) {
            login_user($user);

            if ($rememberMe) {
                // TODO (CB) : générer un token aléatoire, le stocker en BDD (table remember_tokens)
                // et poser un cookie longue durée contenant ce token (jamais le mot de passe)
            }

            header('Location: index.php');
            exit;
        }

        $errorMessage = 'E-mail ou mot de passe incorrect.';
    }
}

include 'public/includes/header.php';



?>

<main class="auth-section">
  <div class="container">

    <h1 class="auth-card__title">Connexion</h1>
    <p class="auth-card__subtitle">Accédez à votre compte SkinCareBeauty.</p>

    <?php if ($errorMessage !== ''): ?>
      <div class="profile-alert profile-alert--error" role="alert">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <input type="hidden" name="action" value="login">

      <div class="form-group-profile">
        <label for="email" class="form-label-profile">Adresse e-mail</label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-input-profile"
          value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
          autocomplete="email"
          required>
      </div>

      <div class="form-group-profile">
        <label for="password" class="form-label-profile">Mot de passe</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-input-profile"
          autocomplete="current-password"
          required>
      </div>

      <div class="auth-options">
        <a href="forgot_password.php" class="auth-link">Mot de passe oublié ?</a>
      </div>

      <button type="submit" class="btn-rose-sm">Se connecter</button>
    </form>

    <p class="auth-card__footer">
      Pas encore de compte ?
      <a href="register.php" class="auth-link">Créer un compte</a>
    </p>
  </div>

  </div>
</main>

<?php include 'public/includes/footer.php'; ?>