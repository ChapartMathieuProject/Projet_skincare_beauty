<?php

require_once __DIR__ . '/../../public/includes/classes/DAO/DAO.php';
require_once __DIR__ . '/../../public/includes/classes/DAO/ProductDAO.php';
require_once __DIR__ . '/../../public/includes/classes/DAO/BrandDAO.php';
require_once __DIR__ . '/../../public/includes/classes/DAO/ProductTypeDAO.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/Product.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/Brand.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/ProductType.php';

class ProductController extends Controller
{
    public function show(string $slug): void
    {
        $productDAO = new ProductDAO($this->pdo);
        $product = $productDAO->findBySlug($slug);

        if ($product === null) {
            http_response_code(404);
            $this->render("errors/404");
            return;
        }

        $brandDAO       = new BrandDAO($this->pdo);
        $productTypeDAO = new ProductTypeDAO($this->pdo);
        $brands         = $brandDAO->findAllKeyedById();
        $product_types  = $productTypeDAO->findAllKeyedById();
        $product_type_of = [];

        foreach ($this->pdo->query("SELECT product_id, product_type_id FROM lien_product_type") as $row) {
            $product_type_of[(int) $row["product_id"]] = (int) $row["product_type_id"];
        }

        $promotions = [];
        foreach ($this->pdo->query("SELECT product_id, promotion_percent FROM promotions WHERE promotion_is_active = 1") as $row) {
            $promotions[(int) $row["product_id"]] = (int) $row["promotion_percent"];
        }

        $pictures = [];
        foreach ($this->pdo->query("SELECT product_id, picture_path FROM pictures") as $row) {
            if (!isset($pictures[(int) $row["product_id"]])) {
                $pictures[(int) $row["product_id"]] = $row["picture_path"];
            }
        }

        $current_id = $product->getId();
        $type_id    = $product_type_of[$current_id] ?? null;

        $stmt = $this->pdo->prepare(
            "SELECT picture_path FROM pictures WHERE product_id = ? ORDER BY picture_id"
        );
        $stmt->execute([$current_id]);
        $gallery = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $similar_products = [];
        foreach ($productDAO->findAllActive() as $p) {
            if ($p->getId() !== $current_id
                && isset($product_type_of[$p->getId()])
                && $product_type_of[$p->getId()] === $type_id) {
                $similar_products[] = $p;
            }
        }
        shuffle($similar_products);
        $similar_products = array_slice($similar_products, 0, 4);

        $this->render("product/show", [
            "product"          => $product,
            "similar_products" => $similar_products,
            "brands"           => $brands,
            "product_types"    => $product_types,
            "product_type_of"  => $product_type_of,
            "promotions"       => $promotions,
            "pictures"         => $pictures,
            "gallery"          => $gallery,
        ]);
    }
}