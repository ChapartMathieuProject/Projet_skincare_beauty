<?php

require_once __DIR__ . '/../../public/includes/classes/DAO/DAO.php'; 
require_once __DIR__ . '/../../public/includes/classes/DAO/ProductDAO.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/Product.php';

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

        $this->render("product/show", ["product" => $product]);
    }
}