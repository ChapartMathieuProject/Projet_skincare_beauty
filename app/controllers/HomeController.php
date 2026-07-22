<?php
require_once __DIR__ . '/../../public/includes/classes/DAO/DAO.php'; 
require_once __DIR__ . '/../../public/includes/classes/DAO/ProductDAO.php';
require_once __DIR__ . '/../../public/includes/classes/Entity/Product.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $productDAO = new ProductDAO($this->pdo);
        $newest = $productDAO->findNewest(8);

        $this->render('home/index', ['newest' => $newest]);
    }
}