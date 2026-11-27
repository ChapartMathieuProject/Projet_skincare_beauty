<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testAdministrateurEstAdminEtSav(): void
    {
        $_SESSION['user_id']      = 1;
        $_SESSION['user_type_id'] = 2;

        $this->assertTrue(is_logged());
        $this->assertTrue(is_admin());
        $this->assertTrue(is_sav(), "L'administrateur possède aussi les droits de l'agent SAV");
    }

    public function testAgentSavEstSavMaisPasAdmin(): void
    {
        $_SESSION['user_id']      = 6;
        $_SESSION['user_type_id'] = 3;

        $this->assertTrue(is_sav());
        $this->assertFalse(is_admin(), "L'agent SAV ne peut pas corriger l'historique");
    }

    public function testClientNAAucunDroitAdministratif(): void
    {
        $_SESSION['user_id']      = 2;
        $_SESSION['user_type_id'] = 1;

        $this->assertTrue(is_logged());
        $this->assertFalse(is_sav());
        $this->assertFalse(is_admin());
    }

    public function testVisiteurNonConnecteNAAucunDroit(): void
    {
        $this->assertFalse(is_logged());
        $this->assertFalse(is_sav());
        $this->assertFalse(is_admin());
    }

    public function testSessionSansTypeNAccordeAucunDroit(): void
    {
        $_SESSION['user_id'] = 4;

        $this->assertTrue(is_logged());
        $this->assertFalse(is_sav());
        $this->assertFalse(is_admin());
    }

    // --- authenticate() : séparation des portails client / administration ---

    /** PDO simulé qui renvoie la ligne utilisateur donnée (ou false si compte inconnu). */
    private function pdoReturningUser(array|false $row): PDO
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($row);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);
        return $pdo;
    }

    private function userRow(int $typeId, string $password): array
    {
        return [
            'user_id'            => 10,
            'user_mail'          => 'compte@example.test',
            // coût minimal : le test vérifie la logique, pas la robustesse du hash
            'user_password'      => password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]),
            'user_type_id'       => $typeId,
            'customer_name'      => null,
            'customer_firstname' => null,
        ];
    }

    public function testAdminAvecBonMotDePasseRefuseSurLePortailClient(): void
    {
        $pdo = $this->pdoReturningUser($this->userRow(USER_TYPE_ADMIN, 'Secret-123'));

        $this->assertNull(authenticate($pdo, 'compte@example.test', 'Secret-123', [USER_TYPE_CLIENT]));
    }

    public function testAgentSavAvecBonMotDePasseRefuseSurLePortailClient(): void
    {
        $pdo = $this->pdoReturningUser($this->userRow(USER_TYPE_SAV, 'Secret-123'));

        $this->assertNull(authenticate($pdo, 'compte@example.test', 'Secret-123', [USER_TYPE_CLIENT]));
    }

    public function testClientAvecBonMotDePasseRefuseSurLePortailAdmin(): void
    {
        $pdo = $this->pdoReturningUser($this->userRow(USER_TYPE_CLIENT, 'Secret-123'));

        $this->assertNull(authenticate($pdo, 'compte@example.test', 'Secret-123', [USER_TYPE_ADMIN, USER_TYPE_SAV]));
    }

    public function testAdminAvecBonMotDePasseAccepteSurLePortailAdmin(): void
    {
        $row = $this->userRow(USER_TYPE_ADMIN, 'Secret-123');
        $pdo = $this->pdoReturningUser($row);

        $this->assertSame($row, authenticate($pdo, 'compte@example.test', 'Secret-123', [USER_TYPE_ADMIN, USER_TYPE_SAV]));
    }

    public function testClientAvecBonMotDePasseAccepteSurLePortailClient(): void
    {
        $row = $this->userRow(USER_TYPE_CLIENT, 'Secret-123');
        $pdo = $this->pdoReturningUser($row);

        $this->assertSame($row, authenticate($pdo, 'compte@example.test', 'Secret-123', [USER_TYPE_CLIENT]));
    }

    public function testMauvaisMotDePasseRefuse(): void
    {
        $pdo = $this->pdoReturningUser($this->userRow(USER_TYPE_ADMIN, 'Secret-123'));

        $this->assertNull(authenticate($pdo, 'compte@example.test', 'mauvais', [USER_TYPE_ADMIN, USER_TYPE_SAV]));
    }

    public function testCompteInconnuRefuse(): void
    {
        $pdo = $this->pdoReturningUser(false);

        $this->assertNull(authenticate($pdo, 'inconnu@example.test', 'Secret-123', [USER_TYPE_CLIENT]));
    }

    public function testHashFacticeEstUnBcryptDeCout10(): void
    {
        $info = password_get_info(DUMMY_PASSWORD_HASH);

        $this->assertSame('bcrypt', $info['algoName']);
        $this->assertSame(10, $info['options']['cost'], 'Même coût que les vrais hash : même temps de vérification');
        $this->assertFalse(password_verify('Secret-123', DUMMY_PASSWORD_HASH));
    }

    public function testCompteInconnuPrendLeTempsDUnPasswordVerify(): void
    {
        // Sans vérification factice, ce chemin dure quelques microsecondes contre ~50 ms pour bcrypt
        // coût 10 : l'écart (×1000) rend la comparaison fiable. On garde le meilleur de 3 essais.
        $pdo = $this->pdoReturningUser(false);
        $bestOf3 = function (callable $fn): float {
            $best = INF;
            for ($i = 0; $i < 3; $i++) {
                $start = hrtime(true);
                $fn();
                $best = min($best, hrtime(true) - $start);
            }
            return $best;
        };

        $unknownAccount = $bestOf3(fn () => authenticate($pdo, 'inconnu@example.test', 'Secret-123', [USER_TYPE_CLIENT]));
        $oneVerify      = $bestOf3(fn () => password_verify('Secret-123', DUMMY_PASSWORD_HASH));

        $this->assertGreaterThanOrEqual($oneVerify * 0.1, $unknownAccount);
    }

    // --- Jeton CSRF ---

    public function testJetonCsrfValideEstAccepte(): void
    {
        $token = csrf_token();

        $this->assertSame(64, strlen($token));
        $this->assertSame($token, csrf_token(), 'Le jeton reste stable pendant la session');
        $this->assertTrue(csrf_is_valid($token));
    }

    public function testJetonCsrfInvalideOuAbsentEstRefuse(): void
    {
        csrf_token();

        $this->assertFalse(csrf_is_valid('jeton-forge'));
        $this->assertFalse(csrf_is_valid(''));
        $this->assertFalse(csrf_is_valid(null));
    }

    public function testJetonCsrfRefuseSansJetonEnSession(): void
    {
        $this->assertFalse(csrf_is_valid(''));
        $this->assertFalse(csrf_is_valid('nimporte-quoi'));
    }
}