<?php
/**
 * RIYAA'S COLLECTION — ProductController
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/Product.php';

class ProductController extends Controller
{
    public function catalog(): void
    {
        $product = new Product();

        $categorySlug = $_GET['categorie'] ?? null;
        $products     = $product->getAll($categorySlug);
        $categories   = $product->getCategories();

        // Trouver la catégorie active pour le titre
        $activeCategory = null;
        if ($categorySlug) {
            foreach ($categories as $cat) {
                if ($cat['slug'] === $categorySlug) {
                    $activeCategory = $cat;
                    break;
                }
            }
        }

        $this->render('catalog/index', [
            'pageTitle'      => $activeCategory
                ? $activeCategory['name'] . " — Riyaa's Collection"
                : "Catalogue — Riyaa's Collection",
            'products'       => $products,
            'categories'     => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    public function show(): void
    {
        $slug = $_GET['slug'] ?? null;
        if (!$slug) {
            $this->redirect('/catalogue');
        }

        $product     = new Product();
        $productData = $product->getBySlug($slug);

        if (!$productData) {
            http_response_code(404);
            require VIEW_PATH . '/404.php';
            return;
        }

        $images   = $product->getImages($productData['id']);
        $variants = $product->getVariants($productData['id']);

        $this->render('catalog/show', [
            'pageTitle' => $productData['name'] . " — Riyaa's Collection",
            'product'   => $productData,
            'images'    => $images,
            'variants'  => $variants,
        ]);
    }
}
