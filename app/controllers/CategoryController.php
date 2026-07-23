<?php
require_once __DIR__ . '/../../public/includes/classes/DAO/DAO.php';
require_once __DIR__ . '/../../public/includes/classes/DAO/ProductDAO.php';
require_once __DIR__ . '/../../public/includes/classes/DAO/BrandDAO.php';
require_once __DIR__ . '/../../public/includes/classes/DAO/ProductTypeDAO.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/Product.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/Brand.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/ProductType.php';

class CategoryController extends Controller
{
    public function index(): void
    {
        $productDAO     = new ProductDAO($this->pdo);
        $brandDAO       = new BrandDAO($this->pdo);
        $productTypeDAO = new ProductTypeDAO($this->pdo);

        // --- Maps pour résolution sans JOIN ---
        $brands        = $brandDAO->findAllKeyedById();
        $product_types = $productTypeDAO->findAllKeyedById();

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

        // --- Aiguillage : quel filtre appliquer ? ---
        $cat    = isset($_GET["cat"])    ? trim($_GET["cat"])    : "";
        $filter = isset($_GET["filter"]) ? trim($_GET["filter"]) : "";

        $page_title = "Tous nos produits";
        $products   = [];

        if ($cat !== "") {
            $type_id_active = null;
            foreach ($product_types as $id => $type) {
                if ($type->getSlug() === $cat) {
                    $type_id_active = $id;
                    $page_title     = $type->getName();
                    break;
                }
            }

            if ($type_id_active !== null) {
                $ids = [];
                foreach ($product_type_of as $product_id => $type_id) {
                    if ($type_id === $type_id_active) {
                        $ids[] = $product_id;
                    }
                }
                $products = $productDAO->findByIds($ids);
            } else {
                $page_title = "Catégorie introuvable";
            }

        } elseif ($filter === "promotions") {
            $page_title = "Promotions";
            $products   = $productDAO->findByIds(array_keys($promotions));

        } elseif ($filter === "nouveautes") {
            $page_title = "Nouveautés";
            $products   = $productDAO->findNewest(8);

        } else {
            $page_title = "Tous nos produits";
            $products   = $productDAO->findAllActiveOrderedByName();
        }

        $this->render('category/index', [
            'page_title'      => $page_title,
            'products'        => $products,
            'brands'          => $brands,
            'product_types'   => $product_types,
            'product_type_of' => $product_type_of,
            'promotions'      => $promotions,
            'pictures'        => $pictures,
        ]);
    }
}