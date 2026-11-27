<?php

if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

const USER_TYPE_CLIENT = 1;
const USER_TYPE_ADMIN  = 2;
const USER_TYPE_SAV    = 3;

// Hash bcrypt (coût 10, comme password_hash par défaut) d'un secret aléatoire jeté :
// vérifié quand l'e-mail est inconnu, pour que le temps de réponse ne révèle pas les comptes existants.
const DUMMY_PASSWORD_HASH = '$2y$10$o8MzQKf9b9VEqG3o6Gr9t.CnQtIfe0M5j4//quGOAOQWrMbu8ZC3G';

function is_logged(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged() && (int) ($_SESSION['user_type_id'] ?? 0) === 2;
}

function is_sav(): bool
{
    return is_logged() && in_array((int) ($_SESSION['user_type_id'] ?? 0), [2, 3], true);
}

function require_sav(): void
{
    if (!is_sav()) {
        header('Location: admin_login.php');
        exit;
    }
}

function require_admin(): void
{
    if (is_admin()) {
        return;
    }
    if (is_sav()) {
        header('Location: admin_tickets.php');
        exit;
    }
    header('Location: admin_login.php');
    exit;
}

/**
 * Vérifie les identifiants et le type de compte autorisé sur le portail appelant.
 * Renvoie null si le compte est inconnu, si le mot de passe est faux ou si le type
 * n'est pas autorisé : l'appelant affiche toujours le même message neutre.
 */
function authenticate(PDO $pdo, string $email, string $password, array $allowedTypes): ?array
{
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

    // password_verify est toujours exécuté (hash factice si compte inconnu) : temps de réponse identique.
    $passwordIsValid = password_verify($password, $user ? $user['user_password'] : DUMMY_PASSWORD_HASH);
    if (!$user || !$passwordIsValid) {
        return null;
    }
    if (!in_array((int) $user['user_type_id'], $allowedTypes, true)) {
        return null;
    }
    return $user;
}

/**
 * Ouvre la session de l'utilisateur authentifié (nouvel identifiant de session).
 */
function login_user(array $user): void
{
    session_regenerate_id(true);
    // Changement de compte : le jeton CSRF de l'ancien compte ne doit pas survivre.
    unset($_SESSION['csrf_token']);

    $_SESSION['user_id']      = $user['user_id'];
    $_SESSION['user_mail']    = $user['user_mail'];
    $_SESSION['user_type_id'] = $user['user_type_id'];

    // Nom d'affichage ; sans ligne customers (cas admin / SAV), on retombe sur l'e-mail.
    if (!empty($user['customer_firstname'])) {
        $_SESSION['user_name'] = $user['customer_firstname'] . ' ' . $user['customer_name'];
    } else {
        $_SESSION['user_name'] = $user['user_mail'];
    }
}

/**
 * Jeton CSRF de session, réservé aux actions destructives de l'administration.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_is_valid(?string $token): bool
{
    $expected = $_SESSION['csrf_token'] ?? null;
    return is_string($expected) && $expected !== '' && is_string($token) && hash_equals($expected, $token);
}