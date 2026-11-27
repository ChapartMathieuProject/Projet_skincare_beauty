<?php

use PHPUnit\Framework\TestCase;

class LoginThrottleTest extends TestCase
{
    private const NOW = 1_800_000_000;

    // --- Logique pure : seuils et fenêtres ---

    public function testReglesChoisies(): void
    {
        $this->assertSame(['max_failures' => 3, 'window' => 1800, 'lock' => 1800], LOGIN_THROTTLE_RULES[LOGIN_PORTAL_ADMIN]);
        $this->assertSame(['max_failures' => 5, 'window' => 900, 'lock' => 900], LOGIN_THROTTLE_RULES[LOGIN_PORTAL_CLIENT]);
    }

    public function testAdminDeuxEchecsNeBloquentPas(): void
    {
        $times = [self::NOW - 60, self::NOW - 30];

        $this->assertSame(0, login_lock_remaining($times, self::NOW, 3, 1800, 1800));
    }

    public function testAdminTroisEchecsEn30MinutesBloquent30Minutes(): void
    {
        $times = [self::NOW - 120, self::NOW - 60, self::NOW];

        $this->assertSame(1800, login_lock_remaining($times, self::NOW, 3, 1800, 1800));
        $this->assertSame(1, login_lock_remaining($times, self::NOW + 1799, 3, 1800, 1800));
        $this->assertSame(0, login_lock_remaining($times, self::NOW + 1800, 3, 1800, 1800), 'Fin du blocage après 30 min');
    }

    public function testAdminTroisEchecsEtalesSurPlusDe30MinutesNeBloquentPas(): void
    {
        $times = [self::NOW - 1800, self::NOW - 900, self::NOW];

        $this->assertSame(0, login_lock_remaining($times, self::NOW, 3, 1800, 1800));
    }

    public function testClientQuatreEchecsNeBloquentPasCinqBloquent15Minutes(): void
    {
        $four = [self::NOW - 400, self::NOW - 300, self::NOW - 200, self::NOW - 100];
        $five = array_merge($four, [self::NOW]);

        $this->assertSame(0, login_lock_remaining($four, self::NOW, 5, 900, 900));
        $this->assertSame(900, login_lock_remaining($five, self::NOW, 5, 900, 900));
        $this->assertSame(0, login_lock_remaining($five, self::NOW + 900, 5, 900, 900));
    }

    public function testOrdreDesHorodatagesSansImportance(): void
    {
        $times = [self::NOW, self::NOW - 120, self::NOW - 60];

        $this->assertSame(1800, login_lock_remaining($times, self::NOW, 3, 1800, 1800));
    }

    public function testMessageDeBlocage(): void
    {
        $this->assertSame('Trop de tentatives. Réessayez dans 30 minutes.', login_lock_message(1800));
        $this->assertSame('Trop de tentatives. Réessayez dans 15 minutes.', login_lock_message(841));
        $this->assertSame('Trop de tentatives. Réessayez dans 1 minute.', login_lock_message(5));
    }

    public function testEmailNormalise(): void
    {
        $this->assertSame('admin@skincare.com', login_normalize_email('  Admin@SkinCare.COM '));
    }

    // --- Accès base (PDO simulé) ---

    /** PDO simulé : journalise les requêtes ; les SELECT renvoient $emailTimes / $ipTimes. */
    /**
     * $getLockResults : résultats successifs de GET_LOCK (1 = obtenu, 0 = délai dépassé,
     * ou une PDOException levée à l'exécution, ex. interblocage 3058).
     * WEIGHT_STRING simulé : renvoie « W: » suivi de l'e-mail reçu (déjà normalisé).
     */
    private function pdo(array &$log, array $emailTimes = [], array $ipTimes = [], array $getLockResults = [1, 1]): PDO
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturnCallback(function (string $sql) use (&$log, $emailTimes, $ipTimes, &$getLockResults) {
            $stmt  = $this->createMock(PDOStatement::class);
            $entry = ['sql' => preg_replace('/\s+/', ' ', trim($sql)), 'params' => null];
            $log[] = &$entry;
            $result = str_contains($sql, 'GET_LOCK') ? (array_shift($getLockResults) ?? 1) : null;
            $stmt->method('execute')->willReturnCallback(function (array $params) use (&$entry, $result) {
                $entry['params'] = $params;
                if ($result instanceof PDOException) {
                    throw $result;
                }
                return true;
            });
            if (str_contains($sql, 'GET_LOCK')) {
                $stmt->method('fetchColumn')->willReturn($result);
            } elseif (str_contains($sql, 'WEIGHT_STRING')) {
                $stmt->method('fetchColumn')->willReturnCallback(function () use (&$entry) {
                    return 'W:' . $entry['params'][':email'];
                });
            }
            $times = str_contains($sql, 'login_attempt_email = :value') ? $emailTimes : $ipTimes;
            $stmt->method('fetchAll')->willReturn(array_map(fn (int $t) => gmdate('Y-m-d H:i:s', $t), $times));
            unset($entry);
            return $stmt;
        });
        return $pdo;
    }

    public function testBlocageParEmail(): void
    {
        $log = [];
        $pdo = $this->pdo($log, emailTimes: [self::NOW - 20, self::NOW - 10, self::NOW]);

        $this->assertSame(1800, login_throttle_remaining($pdo, LOGIN_PORTAL_ADMIN, 'Admin@Skincare.com', '10.0.0.1', self::NOW));
        $this->assertSame('DELETE FROM login_attempts WHERE login_attempt_at < :limit', $log[0]['sql'], 'Purge des lignes expirées');
        $this->assertSame([':limit' => gmdate('Y-m-d H:i:s', self::NOW - 3600)], $log[0]['params']);
        $this->assertSame([':portal' => 'admin', ':value' => 'admin@skincare.com', ':since' => gmdate('Y-m-d H:i:s', self::NOW - 3600)], $log[1]['params']);
        $this->assertSame([':portal' => 'admin', ':value' => '10.0.0.1', ':since' => gmdate('Y-m-d H:i:s', self::NOW - 3600)], $log[2]['params']);
    }

    public function testBlocageParIpMemeAvecUnAutreEmail(): void
    {
        $log = [];
        $pdo = $this->pdo($log, ipTimes: [self::NOW - 30, self::NOW - 20, self::NOW - 10, self::NOW - 5, self::NOW]);

        $this->assertSame(900, login_throttle_remaining($pdo, LOGIN_PORTAL_CLIENT, 'nouveau@example.test', '10.0.0.1', self::NOW));
    }

    public function testAucunEchecAucunBlocage(): void
    {
        $log = [];
        $pdo = $this->pdo($log);

        $this->assertSame(0, login_throttle_remaining($pdo, LOGIN_PORTAL_CLIENT, 'sophie.martin@email.com', '10.0.0.1', self::NOW));
    }

    public function testEchecEnregistreAvecEmailNormalise(): void
    {
        $log = [];
        login_record_failure($this->pdo($log), LOGIN_PORTAL_ADMIN, ' Admin@Skincare.com ', '10.0.0.1', self::NOW);

        $this->assertStringStartsWith('INSERT INTO login_attempts', $log[0]['sql']);
        $this->assertSame([
            ':portal' => 'admin', ':email' => 'admin@skincare.com', ':ip' => '10.0.0.1', ':at' => gmdate('Y-m-d H:i:s', self::NOW),
        ], $log[0]['params']);
    }

    public function testSuccesEffaceLesEchecsDeCetEmailSurCePortail(): void
    {
        $log = [];
        login_clear_failures($this->pdo($log), LOGIN_PORTAL_CLIENT, 'Sophie.Martin@email.com');

        $this->assertSame(
            'DELETE FROM login_attempts WHERE login_attempt_portal = :portal AND login_attempt_email = :email',
            $log[0]['sql']
        );
        $this->assertSame([':portal' => 'client', ':email' => 'sophie.martin@email.com'], $log[0]['params']);
    }

    // --- login_attempt() : séquence complète utilisée par les deux portails ---

    /** Requêtes du journal dont le SQL commence par $prefix. */
    private function sqlStartingWith(array $log, string $prefix): array
    {
        return array_values(array_filter(array_column($log, 'sql'), fn (string $sql) => str_starts_with($sql, $prefix)));
    }

    public function testNomsDeVerrousStablesEtOrdonnesEmailPuisIp(): void
    {
        $names = login_lock_names(LOGIN_PORTAL_ADMIN, 'CLE-EMAIL', '10.0.0.1');

        $this->assertSame([
            'login:e:' . sha1('admin|CLE-EMAIL'),
            'login:i:' . sha1('admin|10.0.0.1'),
        ], $names);
        $this->assertLessThanOrEqual(64, strlen($names[0]), 'Limite MySQL des noms de verrou');
        $this->assertNotSame($names, login_lock_names(LOGIN_PORTAL_CLIENT, 'CLE-EMAIL', '10.0.0.1'), 'Un verrou par portail');
    }

    public function testUnEmailEgalAUneIpNeDonnePasLeMemeVerrouQueCetteIp(): void
    {
        // Requête X : e-mail « 1.2.3.4 » depuis 5.6.7.8 ; requête Y : e-mail « 5.6.7.8 » depuis 1.2.3.4.
        [$emailLockX, $ipLockX] = login_lock_names(LOGIN_PORTAL_ADMIN, '1.2.3.4', '5.6.7.8');
        [$emailLockY, $ipLockY] = login_lock_names(LOGIN_PORTAL_ADMIN, '5.6.7.8', '1.2.3.4');

        $this->assertNotSame($emailLockX, $ipLockY, 'Sans préfixes distincts : même nom, ordre inverse, interblocage');
        $this->assertNotSame($emailLockY, $ipLockX);
    }

    public function testCleEmailDeriveeDeLaCollationAvecEmailNormalise(): void
    {
        $log = [];
        $key = login_email_lock_key($this->pdo($log), '  ÁGENT.Sav@Skincare.com ');

        $this->assertSame('SELECT HEX(WEIGHT_STRING(:email COLLATE utf8mb4_0900_ai_ci))', $log[0]['sql']);
        $this->assertSame([':email' => 'ágent.sav@skincare.com'], $log[0]['params']);
        $this->assertSame('W:ágent.sav@skincare.com', $key);
    }

    public function testVariantesDUnMemeEmailMemeVerrouSiLaCollationDonneLeMemePoids(): void
    {
        // Poids de la collation simulé (la preuve réelle sur MySQL est faite à part) :
        // casse, accents et pleine chasse ont le même poids primaire.
        $weights = ['agent.sav@skincare.com' => 'AAAA', 'ágent.sav@skincare.com' => 'AAAA', 'ａｇｅｎｔ.sav@skincare.com' => 'AAAA'];
        $names   = [];
        foreach (['agent.sav@skincare.com', 'ÁGENT.SAV@skincare.com', ' AGENT.sav@SKINCARE.COM ', 'ａｇｅｎｔ.sav@skincare.com'] as $email) {
            $stmt = $this->createMock(PDOStatement::class);
            $stmt->method('execute')->willReturnCallback(function (array $p) use (&$current) { $current = $p[':email']; return true; });
            $stmt->method('fetchColumn')->willReturnCallback(function () use (&$current, $weights) { return $weights[$current] ?? 'AUTRE'; });
            $pdo = $this->createMock(PDO::class);
            $pdo->method('prepare')->willReturn($stmt);

            $names[] = login_lock_names(LOGIN_PORTAL_ADMIN, login_email_lock_key($pdo, $email), '10.0.0.1')[0];
        }

        $this->assertCount(1, array_unique($names), 'Même compte → même verrou e-mail');
    }

    public function testMessagesParStatut(): void
    {
        $this->assertSame('Trop de tentatives. Réessayez dans 30 minutes.', login_attempt_message(['status' => 'locked', 'lock_seconds' => 1800]));
        $this->assertSame('Trop de tentatives. Réessayez dans quelques instants.', login_attempt_message(['status' => 'busy', 'lock_seconds' => 0]));
        $this->assertSame('E-mail ou mot de passe incorrect.', login_attempt_message(['status' => 'failed', 'lock_seconds' => 0]));
    }

    public function testBloqueAuthenticateNonAppeleeEtEssaiNonEnregistre(): void
    {
        $log    = [];
        $called = false;
        $pdo    = $this->pdo($log, emailTimes: [self::NOW - 20, self::NOW - 10, self::NOW - 5]);

        $result = login_attempt($pdo, LOGIN_PORTAL_ADMIN, 'admin@skincare.com', '10.0.0.1', self::NOW,
            function () use (&$called) { $called = true; return ['user_id' => 1]; });

        $this->assertSame(['status' => 'locked', 'user' => null, 'lock_seconds' => 1795], $result);
        $this->assertFalse($called, 'authenticate() ne doit pas être appelée pendant un blocage');
        $this->assertSame([], $this->sqlStartingWith($log, 'INSERT'), 'Un essai bloqué n\'est pas enregistré');
        $this->assertCount(2, $this->sqlStartingWith($log, 'SELECT RELEASE_LOCK'), 'Les deux verrous sont relâchés');
    }

    public function testControleDuBlocageAvantAuthenticateEtSousVerrou(): void
    {
        $log      = [];
        $pdo      = $this->pdo($log);
        $logAtAuth = null;

        login_attempt($pdo, LOGIN_PORTAL_CLIENT, 'sophie.martin@email.com', '10.0.0.1', self::NOW,
            function () use (&$log, &$logAtAuth) { $logAtAuth = array_column($log, 'sql'); return null; });

        // Au moment d'authenticate() : clé e-mail dérivée, 2 verrous pris (e-mail puis IP), contrôle fait.
        $expected = login_lock_names(LOGIN_PORTAL_CLIENT, 'W:sophie.martin@email.com', '10.0.0.1');
        $this->assertStringContainsString('WEIGHT_STRING', $logAtAuth[0]);
        $this->assertSame('SELECT GET_LOCK(:name, :timeout)', $logAtAuth[1]);
        $this->assertSame('SELECT GET_LOCK(:name, :timeout)', $logAtAuth[2]);
        $this->assertSame($expected[0], $log[1]['params'][':name']);
        $this->assertSame($expected[1], $log[2]['params'][':name']);
        $this->assertCount(2, array_filter($logAtAuth, fn ($sql) => str_starts_with($sql, 'SELECT login_attempt_at')));
        $this->assertSame([], array_filter($logAtAuth, fn ($sql) => str_contains($sql, 'RELEASE_LOCK')), 'Verrous tenus pendant authenticate()');
    }

    public function testEchecEnregistrePuisVerrousRelaches(): void
    {
        $log = [];
        $pdo = $this->pdo($log);

        $result = login_attempt($pdo, LOGIN_PORTAL_ADMIN, 'admin@skincare.com', '10.0.0.1', self::NOW, fn () => null);

        $this->assertSame(['status' => 'failed', 'user' => null, 'lock_seconds' => 0], $result);
        $sql = array_column($log, 'sql');
        $insertAt  = array_search('INSERT INTO login_attempts (login_attempt_portal, login_attempt_email, login_attempt_ip, login_attempt_at) VALUES (:portal, :email, :ip, :at)', $sql, true);
        $releaseAt = array_search('SELECT RELEASE_LOCK(:name)', $sql, true);
        $this->assertIsInt($insertAt);
        $this->assertGreaterThan($insertAt, $releaseAt, 'Verrous relâchés seulement après l\'enregistrement de l\'échec');
        $this->assertSame([':name' => login_lock_names(LOGIN_PORTAL_ADMIN, 'W:admin@skincare.com', '10.0.0.1')[1]], $log[$releaseAt]['params'], 'IP relâchée en premier (ordre inverse)');
    }

    public function testSuccesEffaceLesEchecsEtRenvoieLUtilisateur(): void
    {
        $log  = [];
        $pdo  = $this->pdo($log, emailTimes: [self::NOW - 20, self::NOW - 10]);
        $user = ['user_id' => 2, 'user_type_id' => 1];

        $result = login_attempt($pdo, LOGIN_PORTAL_CLIENT, 'sophie.martin@email.com', '10.0.0.1', self::NOW, fn () => $user);

        $this->assertSame(['status' => 'ok', 'user' => $user, 'lock_seconds' => 0], $result);
        $this->assertSame(
            ['DELETE FROM login_attempts WHERE login_attempt_portal = :portal AND login_attempt_email = :email'],
            $this->sqlStartingWith($log, 'DELETE FROM login_attempts WHERE login_attempt_portal')
        );
        $this->assertSame([], $this->sqlStartingWith($log, 'INSERT'));
    }

    public function testVerrouNonObtenuReponseDeBlocageSansAuthentifier(): void
    {
        $log    = [];
        $called = false;
        // Verrou e-mail obtenu, verrou IP non obtenu dans le délai.
        $pdo    = $this->pdo($log, getLockResults: [1, 0]);

        $result = login_attempt($pdo, LOGIN_PORTAL_ADMIN, 'admin@skincare.com', '10.0.0.1', self::NOW,
            function () use (&$called) { $called = true; return null; });

        $this->assertSame(['status' => 'busy', 'user' => null, 'lock_seconds' => 0], $result);
        $this->assertSame('Trop de tentatives. Réessayez dans quelques instants.', login_attempt_message($result));
        $this->assertFalse($called);
        $this->assertSame([], $this->sqlStartingWith($log, 'SELECT login_attempt_at'), 'Aucun contrôle ni écriture sans verrou');
        $this->assertSame([], $this->sqlStartingWith($log, 'INSERT'));
        $releases = $this->sqlStartingWith($log, 'SELECT RELEASE_LOCK');
        $this->assertCount(1, $releases, 'Seul le verrou obtenu est relâché');
    }

    public function testExceptionDeGetLockTraiteeCommeVerrouNonObtenu(): void
    {
        $log    = [];
        $called = false;
        // Verrou e-mail obtenu, puis MySQL signale un interblocage sur le verrou IP.
        $deadlock = new PDOException('SQLSTATE[HY000]: General error: 3058 Deadlock found when trying to get user-level lock');
        $pdo      = $this->pdo($log, getLockResults: [1, $deadlock]);

        $result = login_attempt($pdo, LOGIN_PORTAL_ADMIN, '1.2.3.4', '5.6.7.8', self::NOW,
            function () use (&$called) { $called = true; return null; });

        $this->assertSame(['status' => 'busy', 'user' => null, 'lock_seconds' => 0], $result, 'Pas d\'exception remontée (pas de page d\'erreur)');
        $this->assertFalse($called);
        $this->assertSame([], $this->sqlStartingWith($log, 'INSERT'));
        $releaseParams = array_values(array_map(
            fn (array $e) => $e['params'][':name'],
            array_filter($log, fn (array $e) => str_starts_with($e['sql'], 'SELECT RELEASE_LOCK'))
        ));
        $this->assertSame([login_lock_names(LOGIN_PORTAL_ADMIN, 'W:1.2.3.4', '5.6.7.8')[0]], $releaseParams, 'Le verrou obtenu est relâché, lui seul');
    }

    public function testVerrousRelachesMemeSiAuthenticateEchoueParException(): void
    {
        $log = [];
        $pdo = $this->pdo($log);

        try {
            login_attempt($pdo, LOGIN_PORTAL_CLIENT, 'sophie.martin@email.com', '10.0.0.1', self::NOW,
                fn () => throw new RuntimeException('base indisponible'));
            $this->fail('L\'exception doit remonter');
        } catch (RuntimeException) {
        }

        $this->assertCount(2, $this->sqlStartingWith($log, 'SELECT RELEASE_LOCK'));
    }
}
