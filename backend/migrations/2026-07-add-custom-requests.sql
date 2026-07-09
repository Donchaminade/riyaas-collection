-- ============================================================
--  Migration : demandes sur mesure (custom_requests)
--  Permet aux clientes de demander une pièce particulière
--  (photo de référence facultative), gérée depuis le back-office.
--  À exécuter sur les installations existantes :
--    mysql -u root riyaas_collection < 2026-07-add-custom-requests.sql
-- ============================================================

USE `riyaas_collection`;

CREATE TABLE IF NOT EXISTS `custom_requests` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `request_number` VARCHAR(30)   NOT NULL UNIQUE,
  `customer_name`  VARCHAR(200)  NOT NULL,
  `customer_phone` VARCHAR(20)   NOT NULL,
  `description`    TEXT          NOT NULL,
  `budget`         DECIMAL(10,2) DEFAULT NULL,
  `image_path`     VARCHAR(300)  DEFAULT NULL,
  `status`         ENUM('new','in_review','quoted','accepted','rejected','completed')
                                 NOT NULL DEFAULT 'new',
  `admin_notes`    TEXT          DEFAULT NULL,
  `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_custom_requests_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
