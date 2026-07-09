-- ============================================================
--  Migration : tag couleur sur les images produit
--  Permet d'associer une couleur à chaque image (sélecteur de
--  couleurs sur la fiche produit).
--  À exécuter sur les installations existantes :
--    mysql -u root riyaas_collection < 2026-07-add-image-color.sql
-- ============================================================

USE `riyaas_collection`;

ALTER TABLE `product_images`
  ADD COLUMN `color`     VARCHAR(80) DEFAULT NULL AFTER `alt_text`,
  ADD COLUMN `color_hex` CHAR(7)     DEFAULT NULL AFTER `color`;
