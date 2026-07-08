<?php
// On redirige un utilisateur déjà connecté : pas de raison de lui remontrer le formulaire.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: users.php');
    exit;
}

require_once 'public/includes/db.php';

$errorMessage = '';
$email        = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'login') {
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
      $errorMessage = 'Veuillez renseigner votre e-mail et votre mot de passe.';
    } else {
      // On joint customers pour récupérer le nom/prénom du client.
      // LEFT JOIN car un compte administrateur n'a pas forcément de ligne dans customers.
      $stmt = $pdo->prepare(
        'SELECT users.user_id, users.user_mail, users.user_password, users.user_type_id,
            customers.customer_name, customers.customer_firstname
     FROM users
     LEFT JOIN customers ON customers.user_id = users.user_id
     WHERE users.user_mail = :email'
      );
      $stmt->execute(['email' => $email]);
      $user = $stmt->fetch();

      if ($user && password_verify($password, $user['user_password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id']      = $user['user_id'];
        $_SESSION['user_mail']    = $user['user_mail'];
        $_SESSION['user_type_id'] = $user['user_type_id'];

        // On construit un nom d'affichage. Si customer_firstname est vide (cas admin
        // sans ligne dans customers), on retombe sur l'e-mail pour éviter un nom vide.
        if (!empty($user['customer_firstname'])) {
          $_SESSION['user_name'] = $user['customer_firstname'] . ' ' . $user['customer_name'];
        } else {
            // On joint customers pour récupérer le nom/prénom du client.
            // LEFT JOIN car un compte administrateur n'a pas forcément de ligne dans customers.
            $stmt = $pdo->prepare(
                'SELECT users.user_id, users.user_mail, users.user_password, users.user_type_id,
                        customers.customer_name, customers.customer_firstname
                 FROM users
                 LEFT JOIN customers ON customers.user_id = users.user_id
                 WHERE users.user_mail = :email'
            );
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            // On reste volontairement vague : on ne précise jamais si c'est l'e-mail ou le mot de passe
            // qui est faux, pour ne pas faciliter l'énumération des comptes existants.
            if ($user && password_verify($password, $user['user_password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id']      = $user['user_id'];
                $_SESSION['user_mail']    = $user['user_mail'];
                $_SESSION['user_type_id'] = $user['user_type_id'];

                // On construit un nom d'affichage. Si customer_firstname est vide (cas admin
                // sans ligne dans customers), on retombe sur l'e-mail pour éviter un nom vide.
                if (!empty($user['customer_firstname'])) {
                    $_SESSION['user_name'] = $user['customer_firstname'] . ' ' . $user['customer_name'];
                } else {
                    $_SESSION['user_name'] = $user['user_mail'];
                }

                if ($rememberMe) {
                    // TODO (CB) : générer un token aléatoire, le stocker en BDD (table remember_tokens)
                    // et poser un cookie longue durée contenant ce token (jamais le mot de passe)
                }

                header('Location: users.php');
                exit;
            }

            $errorMessage = 'E-mail ou mot de passe incorrect.';
        }

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