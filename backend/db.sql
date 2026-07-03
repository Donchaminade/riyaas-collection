-- ============================================================
--  RIYAA'S COLLECTION — Script SQL complet FINAL
--  Version avec toutes les modifications
--  Encodage : UTF-8
-- ============================================================

CREATE DATABASE IF NOT EXISTS `riyaas_collection`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `riyaas_collection`;

-- ------------------------------------------------------------
-- TABLE : users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `role`          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `first_name`    VARCHAR(100)  NOT NULL,
  `last_name`     VARCHAR(100)  NOT NULL,
  `email`         VARCHAR(180)  NOT NULL UNIQUE,
  `phone`         VARCHAR(20)   DEFAULT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `address`       TEXT          DEFAULT NULL,
  `city`          VARCHAR(100)  DEFAULT NULL,
  `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE : categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `slug`        VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT         DEFAULT NULL,
  `sort_order`  TINYINT      NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`name`, `slug`, `sort_order`) VALUES
  ('Jupes en soie',    'jupes-en-soie',    1),
  ('Chemises en soie', 'chemises-en-soie', 2),
  ('Hauts en soie',    'hauts-en-soie',    3);

-- ------------------------------------------------------------
-- TABLE : products
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id`                  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `category_id`         INT UNSIGNED     NOT NULL,
  `name`                VARCHAR(200)     NOT NULL,
  `slug`                VARCHAR(220)     NOT NULL UNIQUE,
  `description`         TEXT             DEFAULT NULL,
  `material`            VARCHAR(100)     NOT NULL DEFAULT 'Soie naturelle 100%',
  `price`               DECIMAL(10,2)    NOT NULL,
  `en_promotion`        TINYINT(1)       NOT NULL DEFAULT 0,
  `prix_promotion`      DECIMAL(10,2)    DEFAULT NULL,
  `stock_status`        ENUM('available','made_to_order','out_of_stock')
                                         NOT NULL DEFAULT 'made_to_order',
  `stock_quantity`      INT              DEFAULT NULL,
  `seuil_alerte_stock`  INT              NOT NULL DEFAULT 3,
  `delivery_days`       TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `is_featured`         TINYINT(1)       NOT NULL DEFAULT 0,
  `is_active`           TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_products_category` (`category_id`),
  INDEX `idx_products_active`   (`is_active`),
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE : product_images
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(300) NOT NULL,
  `alt_text`   VARCHAR(200) DEFAULT NULL,
  `is_cover`   TINYINT(1)   NOT NULL DEFAULT 0,
  `sort_order` TINYINT      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_images_product` (`product_id`),
  CONSTRAINT `fk_images_product`
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE : product_variants
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`  INT UNSIGNED NOT NULL,
  `size`        VARCHAR(20)  DEFAULT NULL,
  `color`       VARCHAR(80)  DEFAULT NULL,
  `color_hex`   CHAR(7)      DEFAULT NULL,
  `extra_price` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `idx_variants_product` (`product_id`),
  CONSTRAINT `fk_variants_product`
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE : orders
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED  DEFAULT NULL,
  `order_number`     VARCHAR(30)   NOT NULL UNIQUE,
  `total_amount`     DECIMAL(10,2) NOT NULL,
  `deposit_amount`   DECIMAL(10,2) NOT NULL,
  `balance_amount`   DECIMAL(10,2) NOT NULL,
  `order_status`     ENUM(
                       'pending',
                       'deposit_paid',
                       'in_production',
                       'ready',
                       'delivered',
                       'completed',
                       'cancelled'
                     ) NOT NULL DEFAULT 'pending',
  `payment_status`   ENUM(
                       'awaiting_deposit',
                       'deposit_paid',
                       'balance_paid',
                       'fully_paid',
                       'refunded'
                     ) NOT NULL DEFAULT 'awaiting_deposit',
  `delivery_deadline` DATE         DEFAULT NULL,
  `delivery_address`  TEXT         DEFAULT NULL,
  `delivery_city`     VARCHAR(100) DEFAULT NULL,
  `delivery_notes`    TEXT         DEFAULT NULL,
  `customer_name`     VARCHAR(200) NOT NULL,
  `customer_email`    VARCHAR(180) NOT NULL,
  `customer_phone`    VARCHAR(20)  NOT NULL,
  `notes`             TEXT         DEFAULT NULL,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_orders_user`           (`user_id`),
  INDEX `idx_orders_status`         (`order_status`),
  INDEX `idx_orders_payment_status` (`payment_status`),
  CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE : order_items
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `order_id`     INT UNSIGNED   NOT NULL,
  `product_id`   INT UNSIGNED   NOT NULL,
  `variant_id`   INT UNSIGNED   DEFAULT NULL,
  `product_name` VARCHAR(200)   NOT NULL,
  `size`         VARCHAR(20)    DEFAULT NULL,
  `color`        VARCHAR(80)    DEFAULT NULL,
  `unit_price`   DECIMAL(10,2)  NOT NULL,
  `quantity`     TINYINT        NOT NULL DEFAULT 1,
  `subtotal`     DECIMAL(10,2)  NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_items_order`   (`order_id`),
  INDEX `idx_items_product` (`product_id`),
  CONSTRAINT `fk_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE : payments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id`                     INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `order_id`               INT UNSIGNED   NOT NULL,
  `payment_type`           ENUM('deposit','balance') NOT NULL,
  `amount`                 DECIMAL(10,2)  NOT NULL,
  `currency`               CHAR(3)        NOT NULL DEFAULT 'XOF',
  `method`                 ENUM('tmoney','flooz','cash','other') NOT NULL DEFAULT 'tmoney',
  `gateway`                VARCHAR(50)    DEFAULT 'fedapay',
  `gateway_transaction_id` VARCHAR(100)   DEFAULT NULL,
  `gateway_reference`      VARCHAR(100)   DEFAULT NULL,
  `gateway_response`       JSON           DEFAULT NULL,
  `status`                 ENUM('pending','approved','declined','refunded') NOT NULL DEFAULT 'pending',
  `paid_at`                DATETIME       DEFAULT NULL,
  `created_at`             DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_payments_order`       (`order_id`),
  INDEX `idx_payments_gateway_tid` (`gateway_transaction_id`),
  CONSTRAINT `fk_payments_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE : invoices
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoices` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `order_id`       INT UNSIGNED   NOT NULL UNIQUE,
  `invoice_number` VARCHAR(30)    NOT NULL UNIQUE,
  `total_amount`   DECIMAL(10,2)  NOT NULL,
  `deposit_paid`   DECIMAL(10,2)  NOT NULL,
  `balance_due`    DECIMAL(10,2)  NOT NULL,
  `issued_at`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pdf_path`       VARCHAR(300)   DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_invoices_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE : cart_sessions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cart_sessions` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_key` VARCHAR(64)  NOT NULL UNIQUE,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `cart_data`   JSON         NOT NULL,
  `expires_at`  DATETIME     NOT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_cart_session` (`session_key`),
  CONSTRAINT `fk_cart_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  FIN DU SCRIPT
-- ============================================================