<?php
// On redirige un utilisateur déjà connecté : pas de raison de lui montrer ce formulaire.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: users.php');
    exit;
}

require_once 'public/includes/db.php';

$successMessage  = '';
$errorMessage    = '';
$debugResetLink  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'forgot_password') {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errorMessage = 'Veuillez renseigner une adresse e-mail valide.';
        } else {
            $stmt = $pdo->prepare('SELECT user_id FROM users WHERE user_mail = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            // On ne révèle jamais si l'e-mail existe ou non en base, pour ne pas
            // faciliter l'énumération des comptes existants par un attaquant.
            if ($user !== false) {
                // On génère un token aléatoire de 32 octets (64 caractères hexadécimaux),
                // impossible à deviner.
                $resetToken = bin2hex(random_bytes(32));

                // Le lien expire dans 1 heure.
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // On supprime les anciens tokens de cet utilisateur avant d'en créer un
                // nouveau, pour qu'un seul lien de réinitialisation soit valide à la fois.
                $deleteOldTokens = $pdo->prepare('DELETE FROM password_resets WHERE user_id = :userId');
                $deleteOldTokens->execute(['userId' => $user['user_id']]);

                $insertToken = $pdo->prepare(
                    'INSERT INTO password_resets (user_id, reset_token, reset_expires_at)
                     VALUES (:userId, :token, :expiresAt)'
                );
                $insertToken->execute([
                    'userId'    => $user['user_id'],
                    'token'     => $resetToken,
                    'expiresAt' => $expiresAt,
                ]);

                // On construit le lien de réinitialisation avec le token en paramètre GET.
                $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/reset-password.php?token=' . $resetToken;

                $subject = 'SkinCareBeauty - Réinitialisation de votre mot de passe';
                $message = "Bonjour,\n\nCliquez sur ce lien pour choisir un nouveau mot de passe :\n{$resetLink}\n\n"
                         . "Ce lien est valable 1 heure. Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail.\n\n"
                         . "L'équipe SkinCareBeauty";
                $headers = 'From: no-reply@skincarebeauty.local';

                mail($email, $subject, $message, $headers);

                // TODO (CB) : affichage du lien en clair pour tester sans serveur mail
                // configuré en local. À supprimer avant la mise en production, sinon
                // n'importe qui pourrait réinitialiser le mot de passe d'un autre compte.
                $debugResetLink = $resetLink;
            }

            // Message identique que l'e-mail existe ou non en base.
            $successMessage = 'Si cette adresse e-mail est associée à un compte, un lien de réinitialisation vient de lui être envoyé.';
        }
    }
}

include 'public/includes/header.php';
?>

<main class="auth-section">
  <div class="container">

    <h1 class="auth-card__title">Mot de passe oublié</h1>
    <p class="auth-card__subtitle">Renseignez votre e-mail pour recevoir un lien de réinitialisation.</p>

    <?php if ($successMessage !== ''): ?>
      <div class="profile-alert profile-alert--success" role="alert">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($debugResetLink !== ''): ?>
      <div class="profile-alert profile-alert--info" role="alert">
        <strong>Mode test :</strong> lien de réinitialisation —
        <a href="<?= htmlspecialchars($debugResetLink, ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($debugResetLink, ENT_QUOTES, 'UTF-8') ?>
        </a>
      </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
      <div class="profile-alert profile-alert--error" role="alert">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="forgot-password.php" novalidate>
      <input type="hidden" name="action" value="forgot_password">

      <div class="form-group-profile">
        <label for="email" class="form-label-profile">Adresse e-mail</label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-input-profile"
          autocomplete="email"
          required
        >
      </div>

      <button type="submit" class="btn-rose-sm">Envoyer le lien</button>
    </form>

    <p class="auth-card__footer">
      <a href="login.php" class="auth-link">Retour à la connexion</a>
    </p>

  </div>
</main>

<?php include 'public/includes/footer.php'; ?>
