<?php

use PHPUnit\Framework\TestCase;

class ProductDAOTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $stmt;
    private ProductDAO $dao;

    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->dao  = new ProductDAO($this->pdo);
    }

    public function testFindBySlugRetourneUnProduit(): void
    {
        $ligne = [
            "product_id"            => 42,
            "product_name"          => "Crème hydratante",
            "product_slug"          => "creme-hydratante",
            "product_ean"           => "1234567890258",
            "product_composition"   => "Aqua, oil",
            "product_description"   => "Super crème",
            "product_quantity"      => 12,
            
        ];

        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn($ligne);

        $produit = $this->dao->findBySlug('creme-hydratante');

        $this->assertInstanceOf(Product::class, $produit);
        $this->assertSame("creme-hydratante", $produit->getSlug());
        $this->assertSame("Crème hydratante", $produit->getName());
        $this->assertSame("1234567890258", $produit->getEan());
        $this->assertSame("Aqua, oil", $produit->getComposition());
        $this->assertSame("Super crème", $produit->getDescription());
        $this->assertSame(12, $produit->getQuantity());
    }

    public function testFindBySlugRetourneNullSiIntrouvable(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn(false);

        $produit = $this->dao->findBySlug('slug-inexistant');

        $this->assertNull($produit);
    }
}