-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : db
-- Généré le : mer. 05 août 2026 à 08:36
-- Version du serveur : 8.0.46
-- Version de PHP : 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `skincarebeauty`
--
CREATE DATABASE IF NOT EXISTS `skincarebeauty` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `skincarebeauty`;

DELIMITER $$
--
-- Fonctions
--
DROP FUNCTION IF EXISTS `generate_slug`$$
CREATE DEFINER=`skincarebeauty_user`@`%` FUNCTION `generate_slug` (`input_text` VARCHAR(250)) RETURNS VARCHAR(250) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC BEGIN
    DECLARE slug VARCHAR(250);
 
    -- Remplacer les caractères accentués manuellement
    SET slug = input_text;
    SET slug = REPLACE(slug, 'à', 'a');
    SET slug = REPLACE(slug, 'á', 'a');
    SET slug = REPLACE(slug, 'â', 'a');
    SET slug = REPLACE(slug, 'ä', 'a');
    SET slug = REPLACE(slug, 'ã', 'a');
    SET slug = REPLACE(slug, 'å', 'a');
    SET slug = REPLACE(slug, 'è', 'e');
    SET slug = REPLACE(slug, 'é', 'e');
    SET slug = REPLACE(slug, 'ê', 'e');
    SET slug = REPLACE(slug, 'ë', 'e');
    SET slug = REPLACE(slug, 'ì', 'i');
    SET slug = REPLACE(slug, 'í', 'i');
    SET slug = REPLACE(slug, 'î', 'i');
    SET slug = REPLACE(slug, 'ï', 'i');
    SET slug = REPLACE(slug, 'ò', 'o');
    SET slug = REPLACE(slug, 'ó', 'o');
    SET slug = REPLACE(slug, 'ô', 'o');
    SET slug = REPLACE(slug, 'ö', 'o');
    SET slug = REPLACE(slug, 'õ', 'o');
    SET slug = REPLACE(slug, 'ù', 'u');
    SET slug = REPLACE(slug, 'ú', 'u');
    SET slug = REPLACE(slug, 'û', 'u');
    SET slug = REPLACE(slug, 'ü', 'u');
    SET slug = REPLACE(slug, 'ç', 'c');
    SET slug = REPLACE(slug, 'ñ', 'n');
    SET slug = REPLACE(slug, 'ý', 'y');
    SET slug = REPLACE(slug, 'ÿ', 'y');
 
    -- Convertir en minuscules
    SET slug = LOWER(slug);
 
    -- Remplacer les espaces et tirets multiples par un seul tiret
    SET slug = REPLACE(slug, ' ', '-');
 
    -- Supprimer les caractères non alphanumériques ou tirets
    SET slug = REGEXP_REPLACE(slug, '[^a-z0-9-]', '');
 
    RETURN slug;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `address_id` int NOT NULL,
  `customer_id_account` int NOT NULL,
  `address_label` varchar(50) NOT NULL,
  `address_name` varchar(50) NOT NULL,
  `address_firstname` varchar(30) NOT NULL,
  `address_adress_1` varchar(50) NOT NULL,
  `address_adress_2` varchar(50) DEFAULT NULL,
  `address_adress_3` varchar(50) DEFAULT NULL,
  `address_adress_4` varchar(50) DEFAULT NULL,
  `address_postcode` varchar(5) NOT NULL,
  `address_city` varchar(30) NOT NULL,
  `address_country` varchar(30) NOT NULL,
  `address_is_default` tinyint(1) NOT NULL DEFAULT '0',
  `address_is_billing` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `addresses`
--

INSERT INTO `addresses` (`address_id`, `customer_id_account`, `address_label`, `address_name`, `address_firstname`, `address_adress_1`, `address_adress_2`, `address_adress_3`, `address_adress_4`, `address_postcode`, `address_city`, `address_country`, `address_is_default`, `address_is_billing`) VALUES
(1, 1, 'Domicile', 'Martin', 'Sophie', '12 rue des Lilas', NULL, NULL, NULL, '86100', 'Châtellerault', 'France', 1, 0),
(2, 2, 'Domicile', 'Dupont', 'Lucas', '5 avenue Victor Hugo', NULL, NULL, NULL, '75002', 'Paris', 'France', 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `bills`
--

DROP TABLE IF EXISTS `bills`;
CREATE TABLE `bills` (
  `bill_id` int NOT NULL,
  `bill_number` varchar(10) NOT NULL,
  `bill_delivery_date` date DEFAULT NULL,
  `bill_number_delivery` varchar(50) DEFAULT NULL,
  `order_id` int NOT NULL,
  `delivery_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `bills`
--

INSERT INTO `bills` (`bill_id`, `bill_number`, `bill_delivery_date`, `bill_number_delivery`, `order_id`, `delivery_id`) VALUES
(1, 'FAC0000001', NULL, NULL, 1, NULL),
(2, 'FAC0000002', NULL, NULL, 2, NULL);

--
-- Déclencheurs `bills`
--
DROP TRIGGER IF EXISTS `before_generate_num_bills`;
DELIMITER $$
CREATE TRIGGER `before_generate_num_bills` BEFORE INSERT ON `bills` FOR EACH ROW BEGIN
    DECLARE prefix CHAR(3) DEFAULT 'FAC';
    DECLARE num INT;
 
    SELECT COUNT(*) INTO num FROM bills;
    SET num = num + 1;
 
    SET NEW.bill_number = CONCAT(prefix, LPAD(num, 7, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `brand_id` int NOT NULL,
  `brand_name` varchar(50) NOT NULL,
  `producer_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `producer_id`) VALUES
(1, 'La Roche-posay', 1),
(2, 'Effaclar', 1),
(3, 'Maybelline', 2),
(4, 'Professional Makeup', 2);

-- --------------------------------------------------------

--
-- Structure de la table `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
  `cart_id` int NOT NULL,
  `user_id` int NOT NULL,
  `cart_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `cart_item_id` int NOT NULL,
  `cart_id` int NOT NULL,
  `product_id` int NOT NULL,
  `cart_item_quantity` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `company_id_account` int NOT NULL,
  `company_name_account` varchar(20) NOT NULL,
  `company_password_account` varchar(50) NOT NULL,
  `company_name` varchar(50) NOT NULL,
  `company_adress_1` varchar(50) NOT NULL,
  `company_adress_2` varchar(50) DEFAULT NULL,
  `company_adress_3` varchar(50) DEFAULT NULL,
  `company_adress_4` varchar(50) DEFAULT NULL,
  `company_postcode` varchar(5) NOT NULL,
  `company_city` varchar(30) NOT NULL,
  `company_country` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `companies`
--

INSERT INTO `companies` (`company_id_account`, `company_name_account`, `company_password_account`, `company_name`, `company_adress_1`, `company_adress_2`, `company_adress_3`, `company_adress_4`, `company_postcode`, `company_city`, `company_country`) VALUES
(1, 'loreal', 'company1234', 'Camil SA', '123 Rue de Paris', '17 mai', 'Bâtiment A', '2ème étage', '75001', 'Paris', 'France');

-- --------------------------------------------------------

--
-- Structure de la table `contains`
--

DROP TABLE IF EXISTS `contains`;
CREATE TABLE `contains` (
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `contains_quantity` int NOT NULL DEFAULT '1',
  `contains_unit_price` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `contains`
--

INSERT INTO `contains` (`order_id`, `product_id`, `contains_quantity`, `contains_unit_price`) VALUES
(1, 1, 1, 19.90),
(1, 4, 1, 24.50),
(2, 16, 1, 14.90);

-- --------------------------------------------------------

--
-- Structure de la table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id_account` int NOT NULL,
  `customer_name` varchar(30) NOT NULL,
  `customer_firstname` varchar(30) NOT NULL,
  `customer_title` varchar(10) NOT NULL,
  `customer_phone` char(14) NOT NULL,
  `gender_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `customers`
--

INSERT INTO `customers` (`customer_id_account`, `customer_name`, `customer_firstname`, `customer_title`, `customer_phone`, `gender_id`, `user_id`) VALUES
(1, 'Martin', 'Sophie', 'Mme', '06 12 34 56 78', 2, 2),
(2, 'Dupont', 'Lucas', 'M.', '06 23 45 67 89', 1, 3),
(3, 'Bernard', 'Emma', 'Mme', '06 34 56 78 90', 2, 4);

-- --------------------------------------------------------

--
-- Structure de la table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
CREATE TABLE `deliveries` (
  `delivery_id` int NOT NULL,
  `delivery_number` varchar(20) NOT NULL,
  `delivery_cost` decimal(10,2) NOT NULL,
  `delivery_tracking_number` varchar(50) NOT NULL,
  `delivery_date` datetime NOT NULL,
  `customer_id_account` int NOT NULL,
  `address_id` int NOT NULL,
  `delivery_type_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `deliveries`
--

INSERT INTO `deliveries` (`delivery_id`, `delivery_number`, `delivery_cost`, `delivery_tracking_number`, `delivery_date`, `customer_id_account`, `address_id`, `delivery_type_id`) VALUES
(1, 'EXP0000001', 4.99, 'TRK0001', '2026-05-10 10:00:00', 1, 1, 1),
(2, 'EXP0000002', 0.00, 'TRK0002', '2026-05-12 14:30:00', 2, 2, 2);

--
-- Déclencheurs `deliveries`
--
DROP TRIGGER IF EXISTS `before_generate_num_deliveries`;
DELIMITER $$
CREATE TRIGGER `before_generate_num_deliveries` BEFORE INSERT ON `deliveries` FOR EACH ROW BEGIN
    DECLARE prefix CHAR(3) DEFAULT 'EXP';
    DECLARE num INT;
 
    SELECT COUNT(*) INTO num FROM deliveries;
    SET num = num + 1;
 
    SET NEW.delivery_number= CONCAT(prefix, LPAD(num, 7, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `delivery_types`
--

DROP TABLE IF EXISTS `delivery_types`;
CREATE TABLE `delivery_types` (
  `delivery_type_id` int NOT NULL,
  `delivery_type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `delivery_types`
--

INSERT INTO `delivery_types` (`delivery_type_id`, `delivery_type_name`) VALUES
(5, 'Chronopost'),
(1, 'Livraison à domicile'),
(3, 'Mondial Relay'),
(2, 'Retrait sur place'),
(4, 'UPS');

-- --------------------------------------------------------

--
-- Structure de la table `do`
--

DROP TABLE IF EXISTS `do`;
CREATE TABLE `do` (
  `product_id` int NOT NULL,
  `order_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `genders`
--

DROP TABLE IF EXISTS `genders`;
CREATE TABLE `genders` (
  `gender_id` int NOT NULL,
  `gender_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `genders`
--

INSERT INTO `genders` (`gender_id`, `gender_name`) VALUES
(3, 'Docteur'),
(2, 'Madame'),
(1, 'Monsieur');

-- --------------------------------------------------------

--
-- Structure de la table `lien_product_type`
--

DROP TABLE IF EXISTS `lien_product_type`;
CREATE TABLE `lien_product_type` (
  `product_id` int NOT NULL,
  `product_type_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `lien_product_type`
--

INSERT INTO `lien_product_type` (`product_id`, `product_type_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 2),
(14, 2),
(15, 2),
(16, 3),
(17, 3),
(18, 3),
(19, 4),
(20, 4),
(21, 4),
(22, 5),
(23, 5),
(24, 5);

-- --------------------------------------------------------

--
-- Structure de la table `loyalty_points`
--

DROP TABLE IF EXISTS `loyalty_points`;
CREATE TABLE `loyalty_points` (
  `loyalty_point_id` int NOT NULL,
  `customer_id_account` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `loyalty_point_amount` int NOT NULL,
  `loyalty_point_type` varchar(20) NOT NULL,
  `loyalty_point_label` varchar(100) NOT NULL,
  `loyalty_point_expires_at` date DEFAULT NULL,
  `loyalty_point_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `loyalty_tiers`
--

DROP TABLE IF EXISTS `loyalty_tiers`;
CREATE TABLE `loyalty_tiers` (
  `loyalty_tier_id` int NOT NULL,
  `loyalty_tier_name` varchar(20) NOT NULL,
  `loyalty_tier_min_points` int NOT NULL,
  `loyalty_tier_discount_percent` int NOT NULL DEFAULT '0',
  `loyalty_tier_is_free_shipping` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `loyalty_tiers`
--

INSERT INTO `loyalty_tiers` (`loyalty_tier_id`, `loyalty_tier_name`, `loyalty_tier_min_points`, `loyalty_tier_discount_percent`, `loyalty_tier_is_free_shipping`) VALUES
(1, 'Bronze', 0, 0, 0),
(2, 'Argent', 500, 5, 0),
(3, 'Or', 1500, 10, 1);

-- --------------------------------------------------------

--
-- Structure de la table `loyalty_vouchers`
--

DROP TABLE IF EXISTS `loyalty_vouchers`;
CREATE TABLE `loyalty_vouchers` (
  `loyalty_voucher_id` int NOT NULL,
  `customer_id_account` int NOT NULL,
  `loyalty_voucher_code` varchar(20) NOT NULL,
  `loyalty_voucher_amount` decimal(10,2) NOT NULL,
  `loyalty_voucher_points_used` int NOT NULL,
  `loyalty_voucher_is_used` tinyint(1) NOT NULL DEFAULT '0',
  `loyalty_voucher_expires_at` date NOT NULL,
  `loyalty_voucher_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `order_date` datetime NOT NULL,
  `order_date_annulation` datetime DEFAULT NULL,
  `order_promotion` int DEFAULT NULL,
  `order_type_id` int NOT NULL,
  `payment_type_id` int NOT NULL,
  `company_id_account` int NOT NULL,
  `customer_id_account` int NOT NULL,
  `delivery_type_id` int NOT NULL,
  `deliveries_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`order_id`, `order_number`, `order_date`, `order_date_annulation`, `order_promotion`, `order_type_id`, `payment_type_id`, `company_id_account`, `customer_id_account`, `delivery_type_id`, `deliveries_id`) VALUES
(1, 'CMD0000001', '2026-05-09 09:15:00', NULL, NULL, 1, 1, 1, 1, 1, 1),
(2, 'CMD0000002', '2026-05-11 16:40:00', NULL, 10, 2, 3, 1, 2, 2, 2);

--
-- Déclencheurs `orders`
--
DROP TRIGGER IF EXISTS `after_insert_orders_create_bill`;
DELIMITER $$
CREATE TRIGGER `after_insert_orders_create_bill` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    INSERT INTO bills (order_id) VALUES (NEW.order_id);
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `before_num_orders`;
DELIMITER $$
CREATE TRIGGER `before_num_orders` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN
    DECLARE prefix CHAR(3) DEFAULT 'CMD';
    DECLARE num INT;
 
    SELECT COUNT(*) INTO num FROM orders;
    SET num = num + 1;
 
    SET NEW.order_number = CONCAT(prefix, LPAD(num, 7, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `order_status`
--

DROP TABLE IF EXISTS `order_status`;
CREATE TABLE `order_status` (
  `order_type_id` int NOT NULL,
  `order_type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `order_status`
--

INSERT INTO `order_status` (`order_type_id`, `order_type_name`) VALUES
(3, 'Annulée'),
(1, 'En cours'),
(2, 'Expédié');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `password_reset_id` int NOT NULL,
  `user_id` int NOT NULL,
  `reset_token` varchar(64) NOT NULL,
  `reset_expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`password_reset_id`, `user_id`, `reset_token`, `reset_expires_at`) VALUES
(1, 1, '9f82c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2', '2026-08-05 09:31:51'),
(2, 2, 'a1b2c3d4e5f67890abcdef1234567890abcdef1234567890abcdef1234567890', '2026-07-02 18:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `payement_types`
--

DROP TABLE IF EXISTS `payement_types`;
CREATE TABLE `payement_types` (
  `payement_type_id` int NOT NULL,
  `payement_type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `payement_types`
--

INSERT INTO `payement_types` (`payement_type_id`, `payement_type_name`) VALUES
(5, 'Apple Pay'),
(1, 'Carte Bancaire'),
(4, 'Google Pay'),
(3, 'Paypal'),
(2, 'Virement');

-- --------------------------------------------------------

--
-- Structure de la table `pictures`
--

DROP TABLE IF EXISTS `pictures`;
CREATE TABLE `pictures` (
  `picture_id` int NOT NULL,
  `picture_path` varchar(50) NOT NULL,
  `product_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `producers`
--

DROP TABLE IF EXISTS `producers`;
CREATE TABLE `producers` (
  `producer_id` int NOT NULL,
  `producer_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `producers`
--

INSERT INTO `producers` (`producer_id`, `producer_name`) VALUES
(2, 'L\'Oréal'),
(1, 'Roche-posay');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `product_name` varchar(50) NOT NULL,
  `product_ean` char(13) NOT NULL,
  `product_composition` varchar(200) NOT NULL,
  `product_description` varchar(500) NOT NULL,
  `product_is_status` tinyint(1) NOT NULL,
  `product_buy_price` decimal(10,2) NOT NULL,
  `product_margin` int NOT NULL,
  `product_quantity` int NOT NULL,
  `product_alert` int DEFAULT NULL,
  `product_slug` varchar(250) NOT NULL,
  `producer_id` int NOT NULL,
  `brand_id` int NOT NULL,
  `company_id_account` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `product_ean`, `product_composition`, `product_description`, `product_is_status`, `product_buy_price`, `product_margin`, `product_quantity`, `product_alert`, `product_slug`, `producer_id`, `brand_id`, `company_id_account`) VALUES
(1, 'P-Tiox', '3600000000001', 'Aqua, Glycerin', 'Sérum anti-âge', 1, 15.00, 60, 100, 10, 'p-tiox', 1, 1, 1),
(2, 'Age Interrupter', '3600000000002', 'Aqua, Glycerin', 'Sérum anti-âge', 1, 18.00, 55, 100, 10, 'age-interrupter', 1, 1, 1),
(3, 'H.A Intensifier', '3600000000003', 'Acide hyaluronique', 'Sérum hydratant', 1, 20.00, 50, 100, 10, 'ha-intensifier', 1, 1, 1),
(4, 'C.E Ferulic', '3600000000004', 'Vitamine C, E', 'Sérum antioxydant', 1, 25.00, 50, 100, 10, 'ce-ferulic', 1, 1, 1),
(5, 'Phloretin CF', '3600000000005', 'Phloretine', 'Sérum éclat', 1, 24.00, 50, 100, 10, 'phloretin-cf', 1, 1, 1),
(6, 'Cell Cycle Catalyst', '3600000000006', 'AHA', 'Sérum exfoliant', 1, 22.00, 50, 100, 10, 'cell-cycle-catalyst', 1, 1, 1),
(7, 'Serum 10', '3600000000007', 'Vitamine C', 'Sérum éclat', 1, 16.00, 55, 100, 10, 'serum-10', 1, 2, 1),
(8, 'Discoloration Defense', '3600000000008', 'Niacinamide', 'Sérum anti-taches', 1, 19.00, 50, 100, 10, 'discoloration-defense', 1, 2, 1),
(9, 'Blemish Age Defense', '3600000000009', 'Acide salicylique', 'Sérum imperfections', 1, 21.00, 50, 100, 10, 'blemish-age-defense', 1, 2, 1),
(10, 'Mela B3 Serum', '3600000000010', 'Mélasyl, Niacinamide', 'Sérum anti-taches', 1, 23.00, 50, 100, 10, 'mela-b3-serum', 1, 1, 1),
(11, 'Collagen III Amplifier', '3600000000011', 'Peptides', 'Sérum fermeté', 1, 26.00, 50, 100, 10, 'collagen-iii-amplifier', 1, 1, 1),
(12, 'Phyto Corrective', '3600000000012', 'Extraits botaniques', 'Sérum apaisant', 1, 20.00, 50, 100, 10, 'phyto-corrective', 1, 1, 1),
(13, 'Age Interrupter Triple Lipid Restore', '3600000000013', 'Céramides', 'Crème anti-âge', 1, 30.00, 50, 80, 8, 'age-interrupter-triple-lipid-restore', 2, 3, 1),
(14, 'Hydra Beauty Micro Gel Crème', '3600000000014', 'Camélia', 'Crème hydratante', 1, 35.00, 50, 80, 8, 'hydra-beauty-micro-gel-creme', 2, 3, 1),
(15, 'Sublimage La Crème Lumière', '3600000000015', 'PFA', 'Crème lumière', 1, 40.00, 50, 80, 8, 'sublimage-la-creme-lumiere', 2, 3, 1),
(16, 'Advanced Hyalu B5 Gel', '3600000000016', 'Acide hyaluronique, B5', 'Gel hydratant', 1, 18.00, 55, 90, 9, 'advanced-hyalu-b5-gel', 1, 1, 1),
(17, 'Phyto Corrective Gel', '3600000000017', 'Extraits botaniques', 'Gel apaisant', 1, 19.00, 50, 90, 9, 'phyto-corrective-gel', 1, 1, 1),
(18, 'Discoloration Defense Gel', '3600000000018', 'Niacinamide', 'Gel anti-taches', 1, 20.00, 50, 90, 9, 'discoloration-defense-gel', 1, 2, 1),
(19, 'Kayali', '3600000000019', 'Parfum', 'Eau de parfum', 1, 45.00, 60, 60, 6, 'kayali', 2, 3, 1),
(20, 'N°5', '3600000000020', 'Parfum', 'Eau de parfum', 1, 80.00, 60, 60, 6, 'n5', 2, 3, 1),
(21, 'La Vie Est Belle', '3600000000021', 'Parfum', 'Eau de parfum', 1, 70.00, 60, 60, 6, 'la-vie-est-belle', 2, 3, 1),
(22, 'Phyto Corrective Masque', '3600000000022', 'Extraits botaniques', 'Masque apaisant', 1, 22.00, 50, 70, 7, 'phyto-corrective-masque', 1, 1, 1),
(23, 'Cellular Hydralift Firming Mask', '3600000000023', 'Peptides', 'Masque fermeté', 1, 28.00, 50, 70, 7, 'cellular-hydralift-firming-mask', 2, 4, 1),
(24, 'Masque de nuit réparateur', '3600000000024', 'Beurre de karité', 'Masque de nuit', 1, 24.00, 50, 70, 7, 'masque-de-nuit-reparateur', 2, 4, 1);

--
-- Déclencheurs `products`
--
DROP TRIGGER IF EXISTS `before_insert_products`;
DELIMITER $$
CREATE TRIGGER `before_insert_products` BEFORE INSERT ON `products` FOR EACH ROW BEGIN
    DECLARE base_slug VARCHAR(250);
    DECLARE unique_slug VARCHAR(250);
    DECLARE counter INT DEFAULT 0;
    SET base_slug = generate_slug(NEW.product_name);
    SET unique_slug = base_slug;
    WHILE EXISTS (SELECT 1 FROM products WHERE product_slug COLLATE utf8mb4_unicode_ci = unique_slug) DO
        SET counter = counter + 1;
        SET unique_slug = CONCAT(base_slug, '-', counter);
    END WHILE;
    SET NEW.product_slug = unique_slug;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `before_update_products`;
DELIMITER $$
CREATE TRIGGER `before_update_products` BEFORE UPDATE ON `products` FOR EACH ROW BEGIN
    DECLARE base_slug VARCHAR(250);
    DECLARE unique_slug VARCHAR(250);
    DECLARE counter INT DEFAULT 0;
    IF OLD.product_name <> NEW.product_name THEN
        SET base_slug = generate_slug(NEW.product_name);
        SET unique_slug = base_slug;
        WHILE EXISTS (SELECT 1 FROM products WHERE product_slug = unique_slug AND product_id <> OLD.product_id) DO
            SET counter = counter + 1;
            SET unique_slug = CONCAT(base_slug, '-', counter);
        END WHILE;
        SET NEW.product_slug = unique_slug;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `product_types`
--

DROP TABLE IF EXISTS `product_types`;
CREATE TABLE `product_types` (
  `product_type_id` int NOT NULL,
  `product_type_name` varchar(50) DEFAULT NULL,
  `product_type_slug` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `product_types`
--

INSERT INTO `product_types` (`product_type_id`, `product_type_name`, `product_type_slug`) VALUES
(1, 'Sérum', 'serum'),
(2, 'Crème', 'creme'),
(3, 'Gel', 'gel'),
(4, 'Parfum', 'parfum'),
(5, 'Masque', 'masque');

--
-- Déclencheurs `product_types`
--
DROP TRIGGER IF EXISTS `before_insert_product_types`;
DELIMITER $$
CREATE TRIGGER `before_insert_product_types` BEFORE INSERT ON `product_types` FOR EACH ROW BEGIN
    DECLARE base_slug VARCHAR(250);
    DECLARE unique_slug VARCHAR(250);
    DECLARE counter INT DEFAULT 0;
 
    -- Générer un slug de base
    SET base_slug = generate_slug(NEW.product_type_name);
 
    -- Vérifier l'unicité et ajuster si nécessaire
    SET unique_slug = base_slug;
    WHILE EXISTS (SELECT 1 FROM product_types WHERE product_type_slug COLLATE utf8mb4_unicode_ci = unique_slug) DO
        SET counter = counter + 1;
        SET unique_slug = CONCAT(base_slug, '-', counter);
    END WHILE;
 
    -- Attribuer le slug unique à la nouvelle ligne
    SET NEW.product_type_slug = unique_slug;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `before_update_product_types`;
DELIMITER $$
CREATE TRIGGER `before_update_product_types` BEFORE UPDATE ON `product_types` FOR EACH ROW BEGIN
    DECLARE base_slug VARCHAR(250);
    DECLARE unique_slug VARCHAR(250);
    DECLARE counter INT DEFAULT 0;
 
    -- Vérifier si Nom_categorie a changé
    IF OLD.product_type_name <> NEW.product_type_name THEN
        -- Générer un slug de base
        SET base_slug = generate_slug(NEW.product_type_name);
 
        -- Vérifier l'unicité et ajuster si nécessaire
        SET unique_slug = base_slug;
        WHILE EXISTS (SELECT 1 FROM product_types WHERE product_type_slug COLLATE utf8mb4_unicode_ci = unique_slug) DO
            SET counter = counter + 1;
            SET unique_slug = CONCAT(base_slug, '-', counter);
        END WHILE;
 
        -- Attribuer le slug unique à la ligne mise à jour
        SET NEW.product_type_slug = unique_slug;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
CREATE TABLE `promotions` (
  `promotion_id` int NOT NULL,
  `product_id` int NOT NULL,
  `promotion_percent` int NOT NULL,
  `promotion_is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `promotions`
--

INSERT INTO `promotions` (`promotion_id`, `product_id`, `promotion_percent`, `promotion_is_active`) VALUES
(1, 1, 20, 1),
(2, 4, 27, 1);

-- --------------------------------------------------------

--
-- Structure de la table `return_types`
--

DROP TABLE IF EXISTS `return_types`;
CREATE TABLE `return_types` (
  `return_type_id` int NOT NULL,
  `return_type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `return_types`
--

INSERT INTO `return_types` (`return_type_id`, `return_type_name`) VALUES
(2, 'Adresse incomplète'),
(3, 'Colis non réclamé'),
(1, 'NPAI');

-- --------------------------------------------------------

--
-- Structure de la table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `ticket_id` int NOT NULL,
  `ticket_return_number` varchar(15) NOT NULL,
  `ticket_comment` varchar(500) NOT NULL,
  `ticket_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `order_id` int NOT NULL,
  `return_type_id` int NOT NULL,
  `ticket_status_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `ticket_return_number`, `ticket_comment`, `ticket_created_at`, `order_id`, `return_type_id`, `ticket_status_id`, `user_id`) VALUES
(1, 'RET-2026-0001', 'Colis revenu NPAI, client injoignable.', '2026-08-05 08:31:51', 1, 1, 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `ticket_history`
--

DROP TABLE IF EXISTS `ticket_history`;
CREATE TABLE `ticket_history` (
  `ticket_history_id` int NOT NULL,
  `ticket_history_action` varchar(255) NOT NULL,
  `ticket_history_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ticket_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `ticket_history`
--

INSERT INTO `ticket_history` (`ticket_history_id`, `ticket_history_action`, `ticket_history_created_at`, `ticket_id`, `user_id`) VALUES
(1, 'Retour créé par Admin Skincare', '2026-08-05 08:31:51', 1, 1),
(2, 'Numéro de retour RET-2026-0001 généré, e-mail envoyé au client', '2026-08-05 08:31:51', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `ticket_status`
--

DROP TABLE IF EXISTS `ticket_status`;
CREATE TABLE `ticket_status` (
  `ticket_status_id` int NOT NULL,
  `ticket_status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `ticket_status`
--

INSERT INTO `ticket_status` (`ticket_status_id`, `ticket_status_name`) VALUES
(3, 'Clôturé'),
(2, 'En cours'),
(1, 'Ouvert'),
(4, 'Refusé');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `user_mail` varchar(50) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_type_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`user_id`, `user_mail`, `user_password`, `user_type_id`) VALUES
(1, 'admin@skincare.com', '$2y$10$ARRxmgoMjHjvmo3wvfD77eQlP63SVS1VBISVCjRSz3MiEWEl6rhk6', 2),
(2, 'sophie.martin@email.com', '$2y$10$fWyz/d1Bd8RjbXzBsNv3JeBhEklEtmRS/sLJsRdMlq2sTwsAlUSsy', 1),
(3, 'lucas.dupont@email.com', '$2y$10$fWyz/d1Bd8RjbXzBsNv3JeBhEklEtmRS/sLJsRdMlq2sTwsAlUSsy', 1),
(4, 'emma.bernard@email.com', '$2y$10$fWyz/d1Bd8RjbXzBsNv3JeBhEklEtmRS/sLJsRdMlq2sTwsAlUSsy', 1),
(5, 'agent.sav@skincare.com', '$2y$10$iqgmbhUnrA7oVHFix/s79eV3DdVBHhIEs5Kf71yDIxCpsFOFk.Zl.', 3);

-- --------------------------------------------------------

--
-- Structure de la table `user_types`
--

DROP TABLE IF EXISTS `user_types`;
CREATE TABLE `user_types` (
  `user_type_id` int NOT NULL,
  `user_type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `user_types`
--

INSERT INTO `user_types` (`user_type_id`, `user_type_name`) VALUES
(2, 'Administrateur'),
(3, 'Agent SAV'),
(1, 'Client');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `customer_id_account` (`customer_id_account`);

--
-- Index pour la table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`bill_id`),
  ADD UNIQUE KEY `bill_number` (`bill_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `delivery_id` (`delivery_id`);

--
-- Index pour la table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `brand_name` (`brand_name`),
  ADD KEY `producer_id` (`producer_id`);

--
-- Index pour la table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id_account`);

--
-- Index pour la table `contains`
--
ALTER TABLE `contains`
  ADD PRIMARY KEY (`order_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id_account`),
  ADD KEY `gender_id` (`gender_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`delivery_id`),
  ADD UNIQUE KEY `delivery_number` (`delivery_number`),
  ADD KEY `customer_id_account` (`customer_id_account`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `delivery_type_id` (`delivery_type_id`);

--
-- Index pour la table `delivery_types`
--
ALTER TABLE `delivery_types`
  ADD PRIMARY KEY (`delivery_type_id`),
  ADD UNIQUE KEY `delivery_type_name` (`delivery_type_name`);

--
-- Index pour la table `do`
--
ALTER TABLE `do`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Index pour la table `genders`
--
ALTER TABLE `genders`
  ADD PRIMARY KEY (`gender_id`),
  ADD UNIQUE KEY `gender_name` (`gender_name`);

--
-- Index pour la table `lien_product_type`
--
ALTER TABLE `lien_product_type`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `product_type_id` (`product_type_id`);

--
-- Index pour la table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`loyalty_point_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_loyalty_points_customer` (`customer_id_account`,`loyalty_point_created_at`);

--
-- Index pour la table `loyalty_tiers`
--
ALTER TABLE `loyalty_tiers`
  ADD PRIMARY KEY (`loyalty_tier_id`),
  ADD UNIQUE KEY `loyalty_tier_name` (`loyalty_tier_name`);

--
-- Index pour la table `loyalty_vouchers`
--
ALTER TABLE `loyalty_vouchers`
  ADD PRIMARY KEY (`loyalty_voucher_id`),
  ADD UNIQUE KEY `loyalty_voucher_code` (`loyalty_voucher_code`),
  ADD KEY `customer_id_account` (`customer_id_account`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `order_type_id` (`order_type_id`),
  ADD KEY `payment_type_id` (`payment_type_id`),
  ADD KEY `company_id_account` (`company_id_account`),
  ADD KEY `customer_id_account` (`customer_id_account`),
  ADD KEY `delivery_type_id` (`delivery_type_id`),
  ADD KEY `deliveries_id` (`deliveries_id`);

--
-- Index pour la table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`order_type_id`),
  ADD UNIQUE KEY `order_type_name` (`order_type_name`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`password_reset_id`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `payement_types`
--
ALTER TABLE `payement_types`
  ADD PRIMARY KEY (`payement_type_id`),
  ADD UNIQUE KEY `payement_type_name` (`payement_type_name`);

--
-- Index pour la table `pictures`
--
ALTER TABLE `pictures`
  ADD PRIMARY KEY (`picture_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `producers`
--
ALTER TABLE `producers`
  ADD PRIMARY KEY (`producer_id`),
  ADD UNIQUE KEY `producer_name` (`producer_name`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_ean` (`product_ean`),
  ADD KEY `producer_id` (`producer_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `company_id_account` (`company_id_account`);

--
-- Index pour la table `product_types`
--
ALTER TABLE `product_types`
  ADD PRIMARY KEY (`product_type_id`);

--
-- Index pour la table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`),
  ADD UNIQUE KEY `product_id` (`product_id`);

--
-- Index pour la table `return_types`
--
ALTER TABLE `return_types`
  ADD PRIMARY KEY (`return_type_id`),
  ADD UNIQUE KEY `return_type_name` (`return_type_name`);

--
-- Index pour la table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD UNIQUE KEY `ticket_return_number` (`ticket_return_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `return_type_id` (`return_type_id`),
  ADD KEY `ticket_status_id` (`ticket_status_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `ticket_history`
--
ALTER TABLE `ticket_history`
  ADD PRIMARY KEY (`ticket_history_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `ticket_status`
--
ALTER TABLE `ticket_status`
  ADD PRIMARY KEY (`ticket_status_id`),
  ADD UNIQUE KEY `ticket_status_name` (`ticket_status_name`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_mail` (`user_mail`),
  ADD KEY `user_type_id` (`user_type_id`);

--
-- Index pour la table `user_types`
--
ALTER TABLE `user_types`
  ADD PRIMARY KEY (`user_type_id`),
  ADD UNIQUE KEY `user_type_name` (`user_type_name`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `bills`
--
ALTER TABLE `bills`
  MODIFY `bill_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id_account` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id_account` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `delivery_types`
--
ALTER TABLE `delivery_types`
  MODIFY `delivery_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `genders`
--
ALTER TABLE `genders`
  MODIFY `gender_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `loyalty_point_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `loyalty_tiers`
--
ALTER TABLE `loyalty_tiers`
  MODIFY `loyalty_tier_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `loyalty_vouchers`
--
ALTER TABLE `loyalty_vouchers`
  MODIFY `loyalty_voucher_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `order_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `password_reset_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `payement_types`
--
ALTER TABLE `payement_types`
  MODIFY `payement_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `pictures`
--
ALTER TABLE `pictures`
  MODIFY `picture_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `producers`
--
ALTER TABLE `producers`
  MODIFY `producer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `product_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promotion_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `return_types`
--
ALTER TABLE `return_types`
  MODIFY `return_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `ticket_history`
--
ALTER TABLE `ticket_history`
  MODIFY `ticket_history_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `ticket_status`
--
ALTER TABLE `ticket_status`
  MODIFY `ticket_status_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `user_types`
--
ALTER TABLE `user_types`
  MODIFY `user_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`customer_id_account`) REFERENCES `customers` (`customer_id_account`);

--
-- Contraintes pour la table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`delivery_id`);

--
-- Contraintes pour la table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`producer_id`) REFERENCES `producers` (`producer_id`);

--
-- Contraintes pour la table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Contraintes pour la table `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `contains_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `contains_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Contraintes pour la table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`gender_id`) REFERENCES `genders` (`gender_id`),
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`customer_id_account`) REFERENCES `customers` (`customer_id_account`),
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`),
  ADD CONSTRAINT `deliveries_ibfk_3` FOREIGN KEY (`delivery_type_id`) REFERENCES `delivery_types` (`delivery_type_id`);

--
-- Contraintes pour la table `do`
--
ALTER TABLE `do`
  ADD CONSTRAINT `do_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `do_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Contraintes pour la table `lien_product_type`
--
ALTER TABLE `lien_product_type`
  ADD CONSTRAINT `lien_product_type_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `lien_product_type_ibfk_2` FOREIGN KEY (`product_type_id`) REFERENCES `product_types` (`product_type_id`);

--
-- Contraintes pour la table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`customer_id_account`) REFERENCES `customers` (`customer_id_account`),
  ADD CONSTRAINT `loyalty_points_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Contraintes pour la table `loyalty_vouchers`
--
ALTER TABLE `loyalty_vouchers`
  ADD CONSTRAINT `loyalty_vouchers_ibfk_1` FOREIGN KEY (`customer_id_account`) REFERENCES `customers` (`customer_id_account`);

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`order_type_id`) REFERENCES `order_status` (`order_type_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`payment_type_id`) REFERENCES `payement_types` (`payement_type_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`company_id_account`) REFERENCES `companies` (`company_id_account`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`customer_id_account`) REFERENCES `customers` (`customer_id_account`),
  ADD CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`delivery_type_id`) REFERENCES `delivery_types` (`delivery_type_id`),
  ADD CONSTRAINT `orders_ibfk_6` FOREIGN KEY (`deliveries_id`) REFERENCES `deliveries` (`delivery_id`);

--
-- Contraintes pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `pictures`
--
ALTER TABLE `pictures`
  ADD CONSTRAINT `pictures_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`producer_id`) REFERENCES `producers` (`producer_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`),
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`company_id_account`) REFERENCES `companies` (`company_id_account`);

--
-- Contraintes pour la table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`return_type_id`) REFERENCES `return_types` (`return_type_id`),
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`ticket_status_id`) REFERENCES `ticket_status` (`ticket_status_id`),
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `ticket_history`
--
ALTER TABLE `ticket_history`
  ADD CONSTRAINT `ticket_history_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`),
  ADD CONSTRAINT `ticket_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`user_type_id`) REFERENCES `user_types` (`user_type_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
