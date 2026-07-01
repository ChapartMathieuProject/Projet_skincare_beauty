<?php
// On redirige un utilisateur déjà connecté : pas de raison de lui remontrer le formulaire.
session_start();

//require 'config/database.php';

//if (isset($_SESSION['user_id'])) {
    //header('Location: profile.php');
    //exit;
//}

$errorMessage = '';
$firstName    = '';
$lastName     = '';
$title        = '';
$email        = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'register') {
        $firstName       = trim($_POST['first_name'] ?? '');
        $lastName        = trim($_POST['last_name'] ?? '');
        $title           = trim($_POST['title'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $acceptTerms     = isset($_POST['accept_terms']);

        if (empty($firstName) || empty($lastName) || empty($title) || empty($email) || empty($password) || empty($confirmPassword)) {
            $errorMessage = 'Veuillez remplir tous les champs.';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errorMessage = 'L\'adresse e-mail saisie n\'est pas valide.';
        } elseif (strlen($password) < 8) {
            $errorMessage = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif ($password !== $confirmPassword) {
            $errorMessage = 'Les mots de passe ne correspondent pas.';
        } elseif ($acceptTerms === false) {
            $errorMessage = 'Vous devez accepter les conditions générales pour créer un compte.';
        } else {
            // On vérifie d'abord que l'e-mail n'est pas déjà pris, sinon l'INSERT échouera (contrainte UNIQUE).
            $statement = $pdo->prepare('SELECT user_id FROM users WHERE user_mail = :email');
            $statement->execute(['email' => $email]);

            if ($statement->fetch() !== false) {
                $errorMessage = 'Cette adresse e-mail est déjà utilisée.';
            } else {
                // On ne stocke jamais le mot de passe en clair : password_hash() génère un hash sécurisé.
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // user_type_id = 1 correspond à "Client" (voir table user_types).
                $insertUser = $pdo->prepare(
                    'INSERT INTO users (user_mail, user_password, user_type_id) VALUES (:email, :password, 1)'
                );
                $insertUser->execute([
                    'email'    => $email,
                    'password' => $hashedPassword,
                ]);

                $newUserId = (int) $pdo->lastInsertId();

                // On déduit le gender_id à partir de la civilité choisie par l'utilisateur (1 = M., 2 = Mme).
                $genderId = ($title === 'Mme') ? 2 : 1;

                // On crée la fiche client liée. Le téléphone n'est pas demandé dans ce formulaire, donc vide pour l'instant.
                $insertCustomer = $pdo->prepare(
                    'INSERT INTO customers (customer_name, customer_firstname, customer_title, customer_phone, gender_id, user_id)
                     VALUES (:lastName, :firstName, :title, :phone, :genderId, :userId)'
                );
                $insertCustomer->execute([
                    'lastName'  => $lastName,
                    'firstName' => $firstName,
                    'title'     => $title,
                    'phone'     => '',
                    'genderId'  => $genderId,
                    'userId'    => $newUserId,
                ]);

                // Inscription réussie : on connecte directement l'utilisateur.
                $_SESSION['user_id']      = $newUserId;
                $_SESSION['user_type_id'] = 1;

                header('Location: profile.php');
                exit;
            }
        }
    }
}

include 'public/includes/header.php';
?>

<main class="auth-section">
  <div class="container">

    <div class="auth-card auth-card--wide">
      <i class="fa-solid fa-spa auth-card__icon" aria-hidden="true"></i>
      <h1 class="auth-card__title">Créer un compte</h1>
      <p class="auth-card__subtitle">Rejoignez SkinCareBeauty en quelques secondes.</p>

      <?php if ($errorMessage !== ''): ?>
        <div class="profile-alert profile-alert--error" role="alert">
          <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
          <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php" novalidate>
        <input type="hidden" name="action" value="register">

        <div class="form-row">
          <div class="form-group-profile">
            <label for="first_name" class="form-label-profile">Prénom</label>
            <input
              type="text"
              id="first_name"
              name="first_name"
              class="form-input-profile"
              value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>"
              autocomplete="given-name"
              required
            >
          </div>

          <div class="form-group-profile">
            <label for="last_name" class="form-label-profile">Nom</label>
            <input
              type="text"
              id="last_name"
              name="last_name"
              class="form-input-profile"
              value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>"
              autocomplete="family-name"
              required
            >
          </div>
        </div>

        <div class="form-group-profile">
          <label for="title" class="form-label-profile">Civilité</label>
          <select id="title" name="title" class="form-input-profile" required>
            <option value="" disabled <?= $title === '' ? 'selected' : '' ?>>Choisir...</option>
            <option value="M." <?= $title === 'M.' ? 'selected' : '' ?>>Monsieur</option>
            <option value="Mme" <?= $title === 'Mme' ? 'selected' : '' ?>>Madame</option>
          </select>
        </div>

        <div class="form-group-profile">
          <label for="email" class="form-label-profile">Adresse e-mail</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-input-profile"
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
            autocomplete="email"
            required
          >
        </div>

        <div class="form-group-profile">
          <label for="password" class="form-label-profile">Mot de passe</label>
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

        <div class="auth-terms">
          <input type="checkbox" id="accept_terms" name="accept_terms" required>
          <label for="accept_terms">
            J'accepte les <a href="cgv.php" class="auth-link">conditions générales</a> et la
            <a href="confidentialite.php" class="auth-link">politique de confidentialité</a>.
          </label>
        </div>

        <button type="submit" class="btn-rose-sm">Créer mon compte</button>
      </form>

      <p class="auth-card__footer">
        Déjà un compte ?
        <a href="login.php" class="auth-link">Se connecter</a>
      </p>
    </div>

  </div>
</main>

<?php include 'public/includes/footer.php'; ?>