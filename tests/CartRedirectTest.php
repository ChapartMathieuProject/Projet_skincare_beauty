<?php

use PHPUnit\Framework\TestCase;

class CartRedirectTest extends TestCase
{
    private const HOST = 'localhost:8080';

    public function testChampRedirectSurPrioritaire(): void
    {
        $this->assertSame('/produit/c-e-ferulic', cart_redirect_target('/produit/c-e-ferulic', 'http://localhost:8080/produits', self::HOST, ''));
    }

    public function testChampRedirectDangereuxIgnoreAuProfitDuReferer(): void
    {
        foreach (['//evil.test', '/\\evil.test', 'https://evil.test/', 'produits', '', "/a\r\nSet-Cookie: x"] as $posted) {
            $this->assertSame('/produits', cart_redirect_target($posted, 'http://localhost:8080/produits?cat=serum', self::HOST, ''), json_encode($posted));
        }
    }

    public function testRefererDuMemeHoteReduitASonChemin(): void
    {
        $this->assertSame('/produit/kayali', cart_redirect_target(null, 'http://localhost:8080/produit/kayali?x=1#top', self::HOST, ''));
    }

    public function testRefererDUnAutreHoteIgnore(): void
    {
        $this->assertSame('/', cart_redirect_target(null, 'http://evil.test/produits', self::HOST, ''));
        $this->assertSame('/', cart_redirect_target(null, 'http://localhost:9999/produits', self::HOST, ''), 'Autre port = autre origine');
    }

    public function testSansRedirectNiRefererRetourAccueil(): void
    {
        $this->assertSame('/', cart_redirect_target(null, null, self::HOST, ''));
        $this->assertSame('/', cart_redirect_target(null, 'pas une url', self::HOST, ''));
    }

    public function testBasePathRetireDuReferer(): void
    {
        // url() rajoute BASE_PATH : on ne doit pas le doubler.
        $this->assertSame('/produits', cart_redirect_target(null, 'http://localhost:8080/app/produits', self::HOST, '/app'));
        $this->assertSame('/', cart_redirect_target(null, 'http://localhost:8080/app', self::HOST, '/app'));
    }

    public function testCheminLocalSur(): void
    {
        $this->assertTrue(is_safe_local_path('/'));
        $this->assertTrue(is_safe_local_path('/produit/n-5'));
        $this->assertFalse(is_safe_local_path('//evil.test'));
        $this->assertFalse(is_safe_local_path('/\\evil.test'));
        $this->assertFalse(is_safe_local_path('cart.php'));
    }

    public function testTousLesCaracteresDeControleSontRefuses(): void
    {
        $this->assertFalse(is_safe_local_path("/\x0b/evil.test"), 'Tabulation verticale (VT)');
        foreach (array_merge(range(0x00, 0x1F), [0x7F]) as $code) {
            $this->assertFalse(is_safe_local_path('/a' . chr($code) . 'b'), sprintf('0x%02X', $code));
        }
        $this->assertSame('/', cart_redirect_target("/\x0b/evil.test", null, 'localhost:8080', ''));
        $this->assertTrue(is_safe_local_path('/produits?q=crème%20bio'), 'Les caractères imprimables restent acceptés');
    }
}
