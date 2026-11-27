<?php
// Portail de connexion ADMINISTRATION : réservé aux comptes administrateur (2) et agent SAV (3).
// Les clients se connectent sur login.php.
require_once 'public/includes/auth.php';

// Un administrateur ou un agent SAV déjà connecté va directement dans son espace.
if (is_admin()) {
    header('Location: admin_dashboard.php');
    exit;
}
if (is_sav()) {
    header('Location: admin_tickets.php');
    exit;
}

require_once 'public/includes/db.php';
require_once 'public/includes/login_throttle.php';

$errorMessage = '';
$email        = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'admin_login') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $clientIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $now      = time();

    if ($email === '' || $password === '') {
        $errorMessage = 'Veuillez renseigner votre e-mail et votre mot de passe.';
    } else {
        // Blocage vérifié AVANT l'authentification, le tout sérialisé par e-mail et par IP.
        $attempt = login_attempt(
            $pdo,
            LOGIN_PORTAL_ADMIN,
            $email,
            $clientIp,
            $now,
            fn () => authenticate($pdo, $email, $password, [USER_TYPE_ADMIN, USER_TYPE_SAV])
        );

        if ($attempt['status'] === 'ok') {
            $user = $attempt['user'];
            login_user($user);
            header('Location: ' . ((int) $user['user_type_id'] === USER_TYPE_ADMIN ? 'admin_dashboard.php' : 'admin_tickets.php'));
            exit;
        }

        // Même message neutre que le compte soit inconnu, le mot de passe faux ou le compte client.
        $errorMessage = login_attempt_message($attempt);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion administration — SkinCareBeauty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="public/css/style.css">
</head>

<body>
    <main class="auth-section">
        <div class="container">

            <h1 class="auth-card__title">Espace administration</h1>
            <p class="auth-card__subtitle">Connexion réservée aux administrateurs et aux agents SAV.</p>

            <?php if ($errorMessage !== ''): ?>
                <div class="profile-alert profile-alert--error" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin_login.php" novalidate>
                <input type="hidden" name="action" value="admin_login">

                <div class="form-group-profile">
                    <label for="email" class="form-label-profile">Adresse e-mail</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input-profile"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
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

                <button type="submit" class="btn-rose-sm">Se connecter</button>
            </form>
        </div>
    </main>
</body>

</html>
