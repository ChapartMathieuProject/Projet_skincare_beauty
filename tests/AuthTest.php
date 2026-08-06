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
}