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

    public function testFindAllActiveRetourneUnTableauDeProduits(): void
    {
        $lignes = [
            ['product_id' => 1, 'product_name' => 'A', 'product_is_status' => 1],
            ['product_id' => 2, 'product_name' => 'B', 'product_is_status' => 1],
        ];

        $this->pdo->method('query')->willReturn($this->stmt);
        $this->stmt->method('fetchAll')->willReturn($lignes);

        $produits = $this->dao->findAllActive();

        $this->assertIsArray($produits);
        $this->assertCount(2, $produits);
        foreach ($produits as $produit) {
            $this->assertInstanceOf(Product::class, $produit);
            $this->assertTrue($produit->isStatus());
        }
    }

    public function testFindAllActiveRetourneTableauVide(): void
    {
        $this->pdo->method('query')->willReturn($this->stmt);
        $this->stmt->method('fetchAll')->willReturn([]);

        $this->assertSame([], $this->dao->findAllActive());
    }

        public function testFindByIdsAvecTableauVideNInterrogePasLaBase(): void
    {
        $this->pdo->expects($this->never())->method('prepare');
        $this->pdo->expects($this->never())->method('query');

        $this->assertSame([], $this->dao->findByIds([]));
    }

    public function testSearchActiveByNameEchappeLesJokersLike(): void
    {
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('product_is_status = 1'),
                $this->stringContains("LIKE :term ESCAPE '\\\\'")
            ))
            ->willReturn($this->stmt);
        $this->stmt->expects($this->once())
            ->method('execute')
            ->with([':term' => '%50\\%%'])
            ->willReturn(true);
        $this->stmt->method('fetchAll')->willReturn([
            ['product_id' => 7, 'product_name' => 'Remise 50% sérum', 'product_is_status' => 1],
        ]);

        $produits = $this->dao->searchActiveByName('50%');

        $this->assertCount(1, $produits);
        $this->assertInstanceOf(Product::class, $produits[0]);
        $this->assertSame('Remise 50% sérum', $produits[0]->getName());
    }

    public function testSearchActiveByNameEchappeUnderscoreEtAntislash(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())
            ->method('execute')
            ->with([':term' => '%a\\_b\\\\c%'])
            ->willReturn(true);
        $this->stmt->method('fetchAll')->willReturn([]);

        $this->dao->searchActiveByName('a_b\\c');
    }

    public function testSearchActiveByNameSansResultatRetourneTableauVide(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetchAll')->willReturn([]);

        $this->assertSame([], $this->dao->searchActiveByName('zzz'));
    }

    // --- deleteOrDeactivate() ---

    /**
     * Branche le PDO simulé : chaque requête préparée est journalisée dans $sqlLog.
     * $productExists / $isOrdered pilotent les réponses ; $failOnProductDelete simule
     * une commande passée entre la vérification et la suppression (FK → 23000).
     */
    private function wireDeletePdo(array &$sqlLog, bool $productExists, bool $isOrdered, bool $failOnProductDelete = false): void
    {
        $this->pdo->method('prepare')->willReturnCallback(
            function (string $sql) use (&$sqlLog, $productExists, $isOrdered, $failOnProductDelete) {
                $sqlLog[] = preg_replace('/\s+/', ' ', trim($sql));
                $stmt = $this->createMock(PDOStatement::class);

                if (str_starts_with($sql, 'DELETE FROM products') && $failOnProductDelete) {
                    // Le pilote MySQL renvoie le SQLSTATE sous forme de chaîne dans getCode()
                    $exception = new PDOException('SQLSTATE[23000]: Integrity constraint violation');
                    (new ReflectionProperty(Exception::class, 'code'))->setValue($exception, '23000');
                    $stmt->method('execute')->willThrowException($exception);
                    return $stmt;
                }

                $stmt->method('execute')->willReturn(true);
                if (str_starts_with($sql, 'SELECT product_id FROM products')) {
                    $stmt->method('fetchColumn')->willReturn($productExists ? 2 : false);
                } elseif (str_contains($sql, 'EXISTS')) {
                    $stmt->method('fetchColumn')->willReturn($isOrdered ? 1 : 0);
                } elseif (str_starts_with($sql, 'SELECT picture_path')) {
                    $stmt->method('fetchAll')->willReturn(['images/prod_0123456789ab.jpg']);
                }
                return $stmt;
            }
        );
    }

    public function testDeleteOrDeactivateSupprimeUnProduitJamaisCommande(): void
    {
        $sqlLog = [];
        $this->wireDeletePdo($sqlLog, productExists: true, isOrdered: false);
        $this->pdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $this->pdo->expects($this->once())->method('commit')->willReturn(true);
        $this->pdo->expects($this->never())->method('rollBack');

        $result = $this->dao->deleteOrDeactivate(2);

        $this->assertSame(['status' => 'deleted', 'pictures' => ['images/prod_0123456789ab.jpg']], $result);
        $deletes = array_values(array_filter($sqlLog, fn ($sql) => str_starts_with($sql, 'DELETE')));
        $this->assertSame([
            'DELETE FROM cart_items WHERE product_id = :id',
            'DELETE FROM pictures WHERE product_id = :id',
            'DELETE FROM lien_product_type WHERE product_id = :id',
            'DELETE FROM products WHERE product_id = :id',
        ], $deletes, 'Les tables liées sont vidées avant le produit lui-même');
        $this->assertEmpty(array_filter($sqlLog, fn ($sql) => str_starts_with($sql, 'UPDATE')));
    }

    public function testDeleteOrDeactivateDesactiveUnProduitDejaCommande(): void
    {
        $sqlLog = [];
        $this->wireDeletePdo($sqlLog, productExists: true, isOrdered: true);
        $this->pdo->expects($this->never())->method('beginTransaction');

        $result = $this->dao->deleteOrDeactivate(1);

        $this->assertSame(['status' => 'deactivated', 'pictures' => []], $result);
        $this->assertContains('UPDATE products SET product_is_status = 0 WHERE product_id = :id', $sqlLog);
        $this->assertEmpty(array_filter($sqlLog, fn ($sql) => str_starts_with($sql, 'DELETE')));
    }

    public function testDeleteOrDeactivateProduitInconnu(): void
    {
        $sqlLog = [];
        $this->wireDeletePdo($sqlLog, productExists: false, isOrdered: false);
        $this->pdo->expects($this->never())->method('beginTransaction');

        $result = $this->dao->deleteOrDeactivate(999);

        $this->assertSame(['status' => 'not_found', 'pictures' => []], $result);
        $this->assertCount(1, $sqlLog, 'Aucune autre requête après la vérification d\'existence');
    }

    public function testDeleteOrDeactivateDesactiveSiCommandeArriveePendantLaSuppression(): void
    {
        $sqlLog = [];
        $this->wireDeletePdo($sqlLog, productExists: true, isOrdered: false, failOnProductDelete: true);
        $this->pdo->method('beginTransaction')->willReturn(true);
        $this->pdo->expects($this->never())->method('commit');
        $this->pdo->expects($this->once())->method('rollBack')->willReturn(true);

        $result = $this->dao->deleteOrDeactivate(2);

        $this->assertSame(['status' => 'deactivated', 'pictures' => []], $result);
        $this->assertSame('UPDATE products SET product_is_status = 0 WHERE product_id = :id', end($sqlLog));
    }

}