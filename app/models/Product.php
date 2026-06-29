<?php
/**
 * RIYAA'S COLLECTION — Model Product
 */

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /** Tous les produits actifs, avec filtre catégorie optionnel */
    public function getAll(?string $categorySlug = null): array
    {
        $sql = "
            SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   pi.image_path AS cover_image
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_cover = 1
            WHERE p.is_active = 1
        ";
        $params = [];

        if ($categorySlug) {
            $sql     .= " AND c.slug = ?";
            $params[] = $categorySlug;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Produits mis en avant (homepage) */
    public function getFeatured(int $limit = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name,
                   pi.image_path AS cover_image
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_cover = 1
            WHERE p.is_active = 1 AND p.is_featured = 1
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /** Un produit par son slug */
    public function getBySlug(string $slug): array|false
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ? AND p.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    /** Un produit par son ID */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT p.*, pi.image_path AS cover_image
            FROM products p
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_cover = 1
            WHERE p.id = ? AND p.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Images d'un produit */
    public function getImages(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM product_images
            WHERE product_id = ?
            ORDER BY is_cover DESC, sort_order ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /** Variantes d'un produit */
    public function getVariants(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM product_variants
            WHERE product_id = ? AND is_active = 1
            ORDER BY size ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /** Une variante par son ID */
    public function getVariantById(int $variantId): array|false
    {
        $stmt = $this->db->prepare("
            SELECT * FROM product_variants WHERE id = ? LIMIT 1
        ");
        $stmt->execute([$variantId]);
        return $stmt->fetch();
    }

    /** Toutes les catégories */
    public function getCategories(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM categories ORDER BY sort_order ASC
        ");
        return $stmt->fetchAll();
    }
}
