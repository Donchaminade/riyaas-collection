<?php
/**
 * RIYAA'S COLLECTION — HomeController
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/Product.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $product = new Product();

        // Produits mis en avant sur la page d'accueil
        $featuredProducts = $product->getFeatured(6);

        // Catégories pour la section catalogue
        $categories = $product->getCategories();

        $this->render('home/index', [
            'pageTitle'        => "Riyaa's Collection — Prêt-à-porter en soie haut de gamme",
            'featuredProducts' => $featuredProducts,
            'categories'       => $categories,
        ]);
    }
}
