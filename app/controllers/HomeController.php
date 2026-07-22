<?php
require_once __DIR__ . '/../../public/includes/classes/DAO/DAO.php';
require_once __DIR__ . '/../../public/includes/classes/DAO/ProductDAO.php';
require_once __DIR__ . '/../../public/includes/classes/DAO/BrandDAO.php';
require_once __DIR__ . '/../../public/includes/classes/DAO/ProductTypeDAO.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/Product.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/Brand.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/ProductType.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $productDAO     = new ProductDAO($this->pdo);
        $brandDAO       = new BrandDAO($this->pdo);
        $productTypeDAO = new ProductTypeDAO($this->pdo);

        $brands        = $brandDAO->findAllKeyedById();        // [brand_id => Brand]
        $product_types = $productTypeDAO->findAllKeyedById();  // [product_type_id => ProductType]

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

        $products = $productDAO->findAllActiveOrderedByName();

        $flash_products = [];
        foreach ($products as $p) {
            if (isset($promotions[$p->getId()])) {
                $flash_products[] = $p;
            }
        }
        usort($flash_products, function ($a, $b) use ($promotions) {
            return $promotions[$b->getId()] <=> $promotions[$a->getId()];
        });

        // --- Passage à la vue ---
        $this->render('home/index', [
            'products'        => $products,
            'flash_products'  => $flash_products,
            'brands'          => $brands,
            'product_types'   => $product_types,
            'product_type_of' => $product_type_of,
            'promotions'      => $promotions,
            'pictures'        => $pictures,
        ]);
    }
}