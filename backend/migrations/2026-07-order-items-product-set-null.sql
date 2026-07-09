-- ============================================================
--  Migration : permettre la suppression définitive d'un produit
--  order_items.product_id devient nullable + FK ON DELETE SET NULL.
--  L'historique des commandes est préservé car order_items stocke
--  déjà une copie de product_name, unit_price et subtotal.
-- ============================================================

USE `riyaas_collection`;

ALTER TABLE `order_items`
  DROP FOREIGN KEY `fk_items_product`;

ALTER TABLE `order_items`
  MODIFY `product_id` INT UNSIGNED DEFAULT NULL;

ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;
