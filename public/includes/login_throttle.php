<?php

/**
 * Limitation des essais de connexion, par e-mail ET par IP (table login_attempts).
 * Règles : portail admin 3 échecs en 30 min → blocage 30 min ;
 *          portail client 5 échecs en 15 min → blocage 15 min.
 * Seuls les échecs sont enregistrés ; un essai bloqué n'est pas compté (pas de prolongation).
 */

const LOGIN_PORTAL_ADMIN  = 'admin';
const LOGIN_PORTAL_CLIENT = 'client';

const LOGIN_THROTTLE_RULES = [
    LOGIN_PORTAL_ADMIN  => ['max_failures' => 3, 'window' => 1800, 'lock' => 1800],
    LOGIN_PORTAL_CLIENT => ['max_failures' => 5, 'window' => 900,  'lock' => 900],
];

// Attente maximale (s) d'un verrou de sérialisation ; au-delà, réponse de blocage générique.
const LOGIN_LOCK_TIMEOUT = 5;

/** E-mail normalisé pour le comptage (même casse que la comparaison SQL insensible à la casse). */
function login_normalize_email(string $email): string
{
    return mb_substr(mb_strtolower(trim($email)), 0, 255);
}

/**
 * Logique pure : secondes de blocage restantes (0 = pas bloqué) à partir des horodatages
 * d'échecs (timestamps Unix). Un blocage commence dès que $maxFailures échecs tiennent
 * dans une fenêtre de $window secondes, et dure $lock secondes à partir du dernier de ces échecs.
 *
 * @param int[] $failureTimes
 */
function login_lock_remaining(array $failureTimes, int $now, int $maxFailures, int $window, int $lock): int
{
    sort($failureTimes);
    $lockedUntil = 0;
    $count = count($failureTimes);

    for ($i = $maxFailures - 1; $i < $count; $i++) {
        // L'échec $i et les $maxFailures - 1 précédents tiennent-ils dans la fenêtre ?
        if ($failureTimes[$i] - $failureTimes[$i - $maxFailures + 1] < $window) {
            $lockedUntil = max($lockedUntil, $failureTimes[$i] + $lock);
        }
    }

    return max(0, $lockedUntil - $now);
}

/** Message de blocage générique (identique que le compte existe ou non). */
function login_lock_message(int $secondsRemaining): string
{
    $minutes = max(1, (int) ceil($secondsRemaining / 60));
    return 'Trop de tentatives. Réessayez dans ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . '.';
}

function login_db_time(int $timestamp): string
{
    return gmdate('Y-m-d H:i:s', $timestamp);
}

/**
 * Secondes de blocage restantes pour cet e-mail OU cette IP sur ce portail (0 = autorisé).
 * Purge au passage les lignes trop anciennes pour compter encore.
 */
function login_throttle_remaining(PDO $pdo, string $portal, string $email, string $ip, int $now): int
{
    $rule = LOGIN_THROTTLE_RULES[$portal];
    // Un échec ne compte plus après fenêtre + blocage : tout ce qui est plus ancien est purgé.
    $maxAge = max(array_map(fn (array $r) => $r['window'] + $r['lock'], LOGIN_THROTTLE_RULES));
    $pdo->prepare('DELETE FROM login_attempts WHERE login_attempt_at < :limit')
        ->execute([':limit' => login_db_time($now - $maxAge)]);

    $since     = login_db_time($now - ($rule['window'] + $rule['lock']));
    $remaining = 0;
    foreach (['login_attempt_email' => login_normalize_email($email), 'login_attempt_ip' => $ip] as $column => $value) {
        $stmt = $pdo->prepare(
            "SELECT login_attempt_at FROM login_attempts
             WHERE login_attempt_portal = :portal AND $column = :value AND login_attempt_at >= :since"
        );
        $stmt->execute([':portal' => $portal, ':value' => $value, ':since' => $since]);
        $times = array_map(
            fn (string $at) => (new DateTimeImmutable($at, new DateTimeZone('UTC')))->getTimestamp(),
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        );
        $remaining = max($remaining, login_lock_remaining($times, $now, $rule['max_failures'], $rule['window'], $rule['lock']));
    }
    return $remaining;
}

function login_record_failure(PDO $pdo, string $portal, string $email, string $ip, int $now): void
{
    $pdo->prepare(
        'INSERT INTO login_attempts (login_attempt_portal, login_attempt_email, login_attempt_ip, login_attempt_at)
         VALUES (:portal, :email, :ip, :at)'
    )->execute([
        ':portal' => $portal,
        ':email'  => login_normalize_email($email),
        ':ip'     => $ip,
        ':at'     => login_db_time($now),
    ]);
}

/** Connexion réussie : on efface les échecs de cet e-mail sur ce portail. */
function login_clear_failures(PDO $pdo, string $portal, string $email): void
{
    $pdo->prepare('DELETE FROM login_attempts WHERE login_attempt_portal = :portal AND login_attempt_email = :email')
        ->execute([':portal' => $portal, ':email' => login_normalize_email($email)]);
}

/**
 * Clé de verrou de l'e-mail, alignée sur la comparaison SQL : poids de tri de l'e-mail
 * normalisé dans la collation des colonnes (utf8mb4_0900_ai_ci, insensible à la casse,
 * aux accents et à la pleine chasse). Deux saisies qui désignent le même compte
 * (« agent… », « ÁGENT… », « ａｇｅｎｔ… ») donnent donc le même verrou.
 */
function login_email_lock_key(PDO $pdo, string $email): string
{
    $stmt = $pdo->prepare('SELECT HEX(WEIGHT_STRING(:email COLLATE utf8mb4_0900_ai_ci))');
    $stmt->execute([':email' => login_normalize_email($email)]);
    return (string) $stmt->fetchColumn();
}

/**
 * Noms des verrous MySQL de sérialisation (≤ 64 caractères), toujours dans le même ordre :
 * e-mail puis IP. Préfixes distincts (« e: » / « i: ») : un e-mail saisi égal à une IP
 * ne peut pas donner le même nom qu'un verrou IP. Aucune requête ne prend un verrou e-mail
 * en tenant déjà un verrou IP, donc pas d'interblocage.
 *
 * @return string[]
 */
function login_lock_names(string $portal, string $emailLockKey, string $ip): array
{
    return [
        'login:e:' . sha1($portal . '|' . $emailLockKey),
        'login:i:' . sha1($portal . '|' . $ip),
    ];
}

/** Message affiché pour le résultat d'un essai (générique, identique que le compte existe ou non). */
function login_attempt_message(array $attempt): string
{
    return match ($attempt['status']) {
        'locked' => login_lock_message($attempt['lock_seconds']),
        'busy'   => 'Trop de tentatives. Réessayez dans quelques instants.',
        default  => 'E-mail ou mot de passe incorrect.',
    };
}

/**
 * Séquence complète d'un essai de connexion, sérialisée par e-mail et par IP :
 * verrous → contrôle du blocage → authentification → effacement (succès) ou enregistrement (échec).
 * Deux requêtes simultanées pour le même e-mail ou la même IP ne peuvent donc pas passer
 * le contrôle avant que l'échec de l'autre soit enregistré.
 * Un essai bloqué (« locked »), ou dont les verrous ne sont pas obtenus (« busy » : délai dépassé,
 * interblocage MySQL 3058 ou autre erreur), n'appelle pas $authenticate et n'est pas enregistré.
 *
 * @param callable():?array $authenticate renvoie la ligne utilisateur, ou null en cas d'échec
 * @return array{status: 'ok'|'failed'|'locked'|'busy', user: ?array, lock_seconds: int}
 */
function login_attempt(PDO $pdo, string $portal, string $email, string $ip, int $now, callable $authenticate): array
{
    $busy     = ['status' => 'busy', 'user' => null, 'lock_seconds' => 0];
    $acquired = [];

    try {
        try {
            foreach (login_lock_names($portal, login_email_lock_key($pdo, $email), $ip) as $name) {
                $stmt = $pdo->prepare('SELECT GET_LOCK(:name, :timeout)');
                $stmt->execute([':name' => $name, ':timeout' => LOGIN_LOCK_TIMEOUT]);
                if ((int) $stmt->fetchColumn() !== 1) {
                    return $busy; // verrou non obtenu à temps : on ne tente rien
                }
                $acquired[] = $name;
            }
        } catch (PDOException) {
            return $busy; // interblocage (3058) ou erreur : traité comme verrou non obtenu, sans page d'erreur
        }

        $remaining = login_throttle_remaining($pdo, $portal, $email, $ip, $now);
        if ($remaining > 0) {
            return ['status' => 'locked', 'user' => null, 'lock_seconds' => $remaining];
        }

        $user = $authenticate();
        if ($user !== null) {
            login_clear_failures($pdo, $portal, $email);
            return ['status' => 'ok', 'user' => $user, 'lock_seconds' => 0];
        }

        login_record_failure($pdo, $portal, $email, $ip, $now);
        return ['status' => 'failed', 'user' => null, 'lock_seconds' => 0];
    } finally {
        foreach (array_reverse($acquired) as $name) {
            $pdo->prepare('SELECT RELEASE_LOCK(:name)')->execute([':name' => $name]);
        }
    }
}
