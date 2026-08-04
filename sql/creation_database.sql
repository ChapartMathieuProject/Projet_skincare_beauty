DROP DATABASE IF EXISTS Skincarebeauty;

create database Skincarebeauty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
use Skincarebeauty; 




-- TABLE : roles utlisateur admin ou client 

CREATE TABLE user_types (
    user_type_id    INT PRIMARY KEY AUTO_INCREMENT,
    user_type_name  VARCHAR(50) NOT NULL UNIQUE
) engine= Innodb DEFAULT charset=utf8mb4;

CREATE TABLE users(
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    user_mail VARCHAR(50) not null,
    user_password VARCHAR (255) NOT NULL,
    UNIQUE (user_mail),
    user_type_id INT NOT NULL,
    FOREIGN KEY (user_type_id) REFERENCES user_types(user_type_id)
) engine= Innodb DEFAULT charset=utf8mb4;





-- TABLE : genre 
CREATE TABLE genders (
    gender_id      INT PRIMARY KEY AUTO_INCREMENT,
    gender_name     VARCHAR(50) NOT NULL UNIQUE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;    




-- table entreprise 
CREATE TABLE companies (
    company_id_account INT PRIMARY KEY AUTO_INCREMENT,
    company_name_account VARCHAR(20) NOT NULL,
    company_password_account VARCHAR(50) NOT NULL,
    company_name VARCHAR(50) NOT NULL,
    company_adress_1 VARCHAR(50) NOT NULL,
    company_adress_2 VARCHAR(50),
    company_adress_3 VARCHAR(50),
    company_adress_4 VARCHAR(50),
    company_postcode VARCHAR(5) NOT NULL,
    company_city VARCHAR(30) NOT NULL,
    company_country VARCHAR(30) NOT NULL
) engine= Innodb DEFAULT charset=utf8mb4;





-- TABLE : type de livraison
CREATE TABLE delivery_types (
    delivery_type_id      INT PRIMARY KEY AUTO_INCREMENT,
    delivery_type_name     VARCHAR(50) NOT NULL UNIQUE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;  


-- TABLE : type de paiement
CREATE TABLE payement_types (
    payement_type_id      INT PRIMARY KEY AUTO_INCREMENT,
    payement_type_name     VARCHAR(50) NOT NULL UNIQUE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


    -- status de la commande
CREATE TABLE order_status (
    order_type_id      INT PRIMARY KEY AUTO_INCREMENT,
    order_type_name     VARCHAR(50) NOT NULL UNIQUE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;    


-- producteur
CREATE TABLE producers (
    producer_id      INT PRIMARY KEY AUTO_INCREMENT,
    producer_name     VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;  


-- TABLE : types de produits
CREATE TABLE product_types(
   product_type_id INT AUTO_INCREMENT,
   product_type_name VARCHAR(50),
   product_type_slug VARCHAR(250) NOT NULL,
   PRIMARY KEY(product_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;





-- TABLE : marque
CREATE TABLE brands (
    brand_id    INT PRIMARY KEY AUTO_INCREMENT,
    brand_name  VARCHAR(50) NOT NULL UNIQUE,
    producer_id INT NOT NULL,
    FOREIGN KEY (producer_id) REFERENCES producers(producer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- TABLE : clients 
CREATE TABLE customers(
    customer_id_account INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(30) NOT NULL,
    customer_firstname VARCHAR(30) NOT NULL,
    customer_title VARCHAR(10) NOT NULL,
    customer_phone char(14) NOT NULL,
    gender_id INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (gender_id) REFERENCES genders (gender_id),
    FOREIGN KEY (user_id) REFERENCES users (user_id)
)engine= Innodb DEFAULT charset=utf8mb4;


-- TABLE : adresse
CREATE TABLE addresses(
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id_account INT NOT NULL,
    address_label VARCHAR(50) NOT NULL,
    address_name VARCHAR(50) NOT NULL,
    address_firstname VARCHAR(30) NOT NULL,
    address_adress_1 VARCHAR(50) NOT NULL,
    address_adress_2 VARCHAR(50),
    address_adress_3 VARCHAR(50),
    address_adress_4 VARCHAR(50),
    address_postcode VARCHAR(5) NOT NULL,
    address_city VARCHAR(30) NOT NULL,
    address_country VARCHAR(30) NOT NULL,
    address_is_default BOOLEAN NOT NULL DEFAULT 0,
    address_is_billing BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (customer_id_account) REFERENCES customers(customer_id_account)
) engine= Innodb DEFAULT charset=utf8mb4;

-- TABLE : produit
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(50) NOT NULL,
    product_ean char(13) NOT NULL UNIQUE,
    product_composition VARCHAR(200) NOT NULL,
    product_description VARCHAR(500) NOT NULL,
    product_is_status BOOLEAN NOT NULL,
    product_buy_price DECIMAL(10, 2) NOT NULL,
    product_margin INT NOT NULL,
    product_quantity INT NOT NULL,
    product_alert INT,
    product_slug VARCHAR(250) NOT NULL,
    producer_id INT NOT NULL,
    brand_id INT NOT NULL,
    company_id_account INT NOT NULL,
    foreign key (producer_id) references producers(producer_id),
    foreign key (brand_id) references brands(brand_id),
    foreign key (company_id_account) references companies(company_id_account)
)engine= Innodb Default charset=utf8mb4;

CREATE TABLE promotions (
    promotion_id        INT PRIMARY KEY AUTO_INCREMENT,
    product_id          INT NOT NULL,
    promotion_percent   INT NOT NULL,
    promotion_is_active BOOLEAN NOT NULL DEFAULT 1,
    UNIQUE (product_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- table images produits 
CREATE TABLE pictures(
    picture_id INT AUTO_INCREMENT PRIMARY KEY,
    picture_path VARCHAR(50) NOT NULL,
    product_id INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) engine= Innodb Default charset=utf8mb4;
    
-- TABLE : statut de la commande
CREATE TABLE deliveries (
    delivery_id              INT PRIMARY KEY AUTO_INCREMENT,
    delivery_number VARCHAR(20) NOT NULL UNIQUE,
    delivery_cost            DECIMAL(10,2) NOT NULL,
    delivery_tracking_number VARCHAR(50) NOT NULL,
    delivery_date            DATETIME NOT NULL,
    customer_id_account      INT NOT NULL,
    address_id               INT NOT NULL,            -- corrigÃ© : "adress_id" -> address_id + FK
    delivery_type_id         INT NOT NULL,            -- colonne ajoutÃ©e (manquait)
    FOREIGN KEY (customer_id_account) REFERENCES customers(customer_id_account),
    FOREIGN KEY (address_id)          REFERENCES addresses(address_id),
    FOREIGN KEY (delivery_type_id)    REFERENCES delivery_types(delivery_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(30) NOT NULL,
    order_date DATETIME NOT NULL,
    order_date_annulation DATETIME,
    order_promotion INT,
    order_type_id INT NOT NULL,
    payment_type_id INT NOT NULL,
    company_id_account INT NOT NULL,
    customer_id_account INT NOT NULL, 
    delivery_type_id INT NOT NULL,
    deliveries_id INT NOT NULL,
    FOREIGN KEY (order_type_id) REFERENCES order_status(order_type_id),
    FOREIGN KEY (payment_type_id) REFERENCES payement_types(payement_type_id),
    FOREIGN KEY (company_id_account) REFERENCES companies(company_id_account),
    FOREIGN KEY (customer_id_account) REFERENCES customers(customer_id_account), 
    FOREIGN KEY (delivery_type_id) REFERENCES delivery_types(delivery_type_id),
    FOREIGN KEY (deliveries_id) REFERENCES deliveries(delivery_id)
)engine= Innodb Default charset=utf8mb4;


CREATE TABLE bills(
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    bill_number VARCHAR(10) NOT NULL,
    bill_delivery_date DATE, 
    bill_number_delivery VARCHAR(50),    
    order_id INT NOT NULL,  
    delivery_id INT,       
    UNIQUE (bill_number),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (delivery_id) REFERENCES deliveries(delivery_id)
)engine= Innodb Default charset=utf8mb4;



DROP TABLE IF EXISTS contains;

CREATE TABLE contains (
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    contains_quantity INT NOT NULL DEFAULT 1,
    contains_unit_price DECIMAL(15,2) NOT NULL,
    PRIMARY KEY (order_id, product_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE do(
    product_id INT PRIMARY KEY,
    order_id INT,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
)engine= Innodb Default charset=utf8mb4;

CREATE TABLE lien_product_type(
    product_id INT PRIMARY KEY,
    product_type_id INT,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (product_type_id) REFERENCES product_types(product_type_id)
)engine= Innodb Default charset=utf8mb4;

-- TABLE : tokens de rÃ©initialisation de mot de passe
CREATE TABLE password_resets (
    password_reset_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id            INT NOT NULL,
    reset_token         VARCHAR(64) NOT NULL UNIQUE,
    reset_expires_at    DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;

CREATE TABLE carts (
    cart_id INT AUTO_INCREMENT,
    user_id INT NOT NULL,
    cart_created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cart_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    cart_item_quantity INT NOT NULL DEFAULT 1,
    PRIMARY KEY (cart_item_id),
    FOREIGN KEY (cart_id) REFERENCES carts(cart_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER $$
--
-- Fonctions
--
DROP FUNCTION IF EXISTS `generate_slug`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `generate_slug` (`input_text` VARCHAR(250)) RETURNS VARCHAR(250) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC BEGIN
    DECLARE slug VARCHAR(250);

    -- Remplacer les caractÃ¨res accentuÃ©s manuellement
    SET slug = input_text;
    SET slug = REPLACE(slug, 'Ã ', 'a');
    SET slug = REPLACE(slug, 'Ã¡', 'a');
    SET slug = REPLACE(slug, 'Ã¢', 'a');
    SET slug = REPLACE(slug, 'Ã¤', 'a');
    SET slug = REPLACE(slug, 'Ã£', 'a');
    SET slug = REPLACE(slug, 'Ã¥', 'a');
    SET slug = REPLACE(slug, 'Ã¨', 'e');
    SET slug = REPLACE(slug, 'Ã©', 'e');
    SET slug = REPLACE(slug, 'Ãª', 'e');
    SET slug = REPLACE(slug, 'Ã«', 'e');
    SET slug = REPLACE(slug, 'Ã¬', 'i');
    SET slug = REPLACE(slug, 'Ã­', 'i');
    SET slug = REPLACE(slug, 'Ã®', 'i');
    SET slug = REPLACE(slug, 'Ã¯', 'i');
    SET slug = REPLACE(slug, 'Ã²', 'o');
    SET slug = REPLACE(slug, 'Ã³', 'o');
    SET slug = REPLACE(slug, 'Ã´', 'o');
    SET slug = REPLACE(slug, 'Ã¶', 'o');
    SET slug = REPLACE(slug, 'Ãµ', 'o');
    SET slug = REPLACE(slug, 'Ã¹', 'u');
    SET slug = REPLACE(slug, 'Ãº', 'u');
    SET slug = REPLACE(slug, 'Ã»', 'u');
    SET slug = REPLACE(slug, 'Ã¼', 'u');
    SET slug = REPLACE(slug, 'Ã§', 'c');
    SET slug = REPLACE(slug, 'Ã±', 'n');
    SET slug = REPLACE(slug, 'Ã½', 'y');
    SET slug = REPLACE(slug, 'Ã¿', 'y');

    -- Convertir en minuscules
    SET slug = LOWER(slug);

    -- Remplacer les espaces et tirets multiples par un seul tiret
    SET slug = REPLACE(slug, ' ', '-');

    -- Supprimer les caractÃ¨res non alphanumÃ©riques ou tirets
    SET slug = REGEXP_REPLACE(slug, '[^a-z0-9-]', '');

    RETURN slug;
END$$

DELIMITER ;



--
-- DÃ©clencheurs `product_types`
--
DROP TRIGGER IF EXISTS `before_insert_product_types`;
DELIMITER $$
CREATE TRIGGER `before_insert_product_types` BEFORE INSERT ON `product_types` FOR EACH ROW BEGIN
    DECLARE base_slug VARCHAR(250);
    DECLARE unique_slug VARCHAR(250);
    DECLARE counter INT DEFAULT 0;

    -- GÃ©nÃ©rer un slug de base
    SET base_slug = generate_slug(NEW.product_type_name);

    -- VÃ©rifier l'unicitÃ© et ajuster si nÃ©cessaire
    SET unique_slug = base_slug;
    WHILE EXISTS (SELECT 1 FROM product_types WHERE product_type_slug COLLATE utf8mb4_unicode_ci = unique_slug) DO
        SET counter = counter + 1;
        SET unique_slug = CONCAT(base_slug, '-', counter);
    END WHILE;

    -- Attribuer le slug unique Ã  la nouvelle ligne
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

    -- VÃ©rifier si Nom_categorie a changÃ©
    IF OLD.product_type_name <> NEW.product_type_name THEN
        -- GÃ©nÃ©rer un slug de base
        SET base_slug = generate_slug(NEW.product_type_name);

        -- VÃ©rifier l'unicitÃ© et ajuster si nÃ©cessaire
        SET unique_slug = base_slug;
        WHILE EXISTS (SELECT 1 FROM product_types WHERE product_type_slug COLLATE utf8mb4_unicode_ci = unique_slug) DO
            SET counter = counter + 1;
            SET unique_slug = CONCAT(base_slug, '-', counter);
        END WHILE;

        -- Attribuer le slug unique Ã  la ligne mise Ã  jour
        SET NEW.product_type_slug = unique_slug;
  END IF;
END
$$
DELIMITER ;





--
-- DÃ©clencheurs `products`
--
DROP TRIGGER IF EXISTS before_insert_products;
DELIMITER $$
CREATE TRIGGER before_insert_products BEFORE INSERT ON products FOR EACH ROW BEGIN
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
END$$
DELIMITER ;



DROP TRIGGER IF EXISTS before_update_products;
DELIMITER $$
CREATE TRIGGER before_update_products BEFORE UPDATE ON products FOR EACH ROW BEGIN
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
END$$
DELIMITER ;



--
-- DÃ©clencheurs `orders`
--

DROP TRIGGER IF EXISTS `before_generate_num_orders`;
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


DROP TRIGGER IF EXISTS `after_insert_orders_create_bill`;
DELIMITER $$
CREATE TRIGGER `after_insert_orders_create_bill` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    INSERT INTO bills (order_id) VALUES (NEW.order_id);
END$$
DELIMITER ;





--
-- DÃ©clencheurs `deliveries`
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




--
-- DÃ©clencheurs `bills`
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


INSERT INTO user_types (user_type_name) VALUES
    ('Client'),
    ('Administrateur');


INSERT INTO users (user_mail, user_type_id, user_password) VALUES
    ('admin@skincare.com',      '2', '$2y$10$ARRxmgoMjHjvmo3wvfD77eQlP63SVS1VBISVCjRSz3MiEWEl6rhk6'),
    ('sophie.martin@email.com', '1', 'Client1234!'),
    ('lucas.dupont@email.com',  '1', 'Client1234!'),
    ('emma.bernard@email.com',  '1', 'Client1234!');

INSERT INTO genders (gender_name) VALUES
    ('Monsieur'), ('Madame'), ('Docteur');

INSERT INTO companies (
    company_name_account, company_password_account, company_name,
    company_adress_1, company_adress_2, company_adress_3, company_adress_4,
    company_postcode, company_city, company_country
) VALUES
    ('loreal', 'company1234', 'Camil SA', '123 Rue de Paris', '17 mai',
     'BÃ¢timent A', '2Ã¨me Ã©tage', '75001', 'Paris', 'France');

INSERT INTO delivery_types (delivery_type_name) VALUES
    ('Livraison Ã  domicile'), ('Retrait sur place'),
    ('Mondial Relay'), ('UPS'), ('Chronopost');

INSERT INTO payement_types (payement_type_name) VALUES
    ('Carte Bancaire'), ('Virement'), ('Paypal'),
    ('Google Pay'), ('Apple Pay');

INSERT INTO order_status (order_type_name) VALUES
    ('En cours'), ('ExpÃ©diÃ©'), ('AnnulÃ©');

INSERT INTO producers (producer_name) VALUES
    ('Roche-posay'), ("L'OrÃ©al");




INSERT INTO brands (brand_name, producer_id) VALUES
    ('La Roche-posay',      1),
    ('Effaclar',            1),
    ('Maybelline',          2),
    ('Professional Makeup', 2);

INSERT INTO product_types (product_type_name) VALUES
    ('SÃ©rum'), ('CrÃ¨me'), ('Gel'), ('Parfum'), ('Masque');

INSERT INTO customers
    (customer_name, customer_firstname, customer_title, customer_phone, gender_id, user_id) VALUES
    ('Martin',  'Sophie', 'Mme', '06 12 34 56 78', 2, 2),
    ('Dupont',  'Lucas',  'M.',  '06 23 45 67 89', 1, 3),
    ('Bernard', 'Emma',   'Mme', '06 34 56 78 90', 2, 4);

-- Produits 
INSERT INTO products
    (product_name, product_ean, product_composition, product_description,
     product_is_status, product_buy_price, product_margin, product_quantity, product_alert,
     producer_id, brand_id, company_id_account) VALUES
    ('P-Tiox',                 '3600000000001', 'Aqua, Glycerin', 'SÃ©rum anti-Ã¢ge',        1, 15.00, 60, 100, 10, 1, 1, 1),
    ('Age Interrupter',        '3600000000002', 'Aqua, Glycerin', 'SÃ©rum anti-Ã¢ge',        1, 18.00, 55, 100, 10, 1, 1, 1),
    ('H.A Intensifier',        '3600000000003', 'Acide hyaluronique', 'SÃ©rum hydratant',   1, 20.00, 50, 100, 10, 1, 1, 1),
    ('C.E Ferulic',            '3600000000004', 'Vitamine C, E', 'SÃ©rum antioxydant',      1, 25.00, 50, 100, 10, 1, 1, 1),
    ('Phloretin CF',           '3600000000005', 'Phloretine', 'SÃ©rum Ã©clat',               1, 24.00, 50, 100, 10, 1, 1, 1),
    ('Cell Cycle Catalyst',    '3600000000006', 'AHA', 'SÃ©rum exfoliant',                  1, 22.00, 50, 100, 10, 1, 1, 1),
    ('Serum 10',               '3600000000007', 'Vitamine C', 'SÃ©rum Ã©clat',               1, 16.00, 55, 100, 10, 1, 2, 1),
    ('Discoloration Defense',  '3600000000008', 'Niacinamide', 'SÃ©rum anti-taches',        1, 19.00, 50, 100, 10, 1, 2, 1),
    ('Blemish Age Defense',    '3600000000009', 'Acide salicylique', 'SÃ©rum imperfections',1, 21.00, 50, 100, 10, 1, 2, 1),
    ('Mela B3 Serum',          '3600000000010', 'MÃ©lasyl, Niacinamide', 'SÃ©rum anti-taches',1, 23.00, 50, 100, 10, 1, 1, 1),
    ('Collagen III Amplifier', '3600000000011', 'Peptides', 'SÃ©rum fermetÃ©',               1, 26.00, 50, 100, 10, 1, 1, 1),
    ('Phyto Corrective',       '3600000000012', 'Extraits botaniques', 'SÃ©rum apaisant',   1, 20.00, 50, 100, 10, 1, 1, 1),
    ('Age Interrupter Triple Lipid Restore', '3600000000013', 'CÃ©ramides', 'CrÃ¨me anti-Ã¢ge', 1, 30.00, 50, 80, 8, 2, 3, 1),
    ('Hydra Beauty Micro Gel CrÃ¨me',         '3600000000014', 'CamÃ©lia', 'CrÃ¨me hydratante', 1, 35.00, 50, 80, 8, 2, 3, 1),
    ('Sublimage La CrÃ¨me LumiÃ¨re',           '3600000000015', 'PFA', 'CrÃ¨me lumiÃ¨re',        1, 40.00, 50, 80, 8, 2, 3, 1),
    ('Advanced Hyalu B5 Gel',  '3600000000016', 'Acide hyaluronique, B5', 'Gel hydratant',  1, 18.00, 55, 90, 9, 1, 1, 1),
    ('Phyto Corrective Gel',   '3600000000017', 'Extraits botaniques', 'Gel apaisant',      1, 19.00, 50, 90, 9, 1, 1, 1),
    ('Discoloration Defense Gel','3600000000018', 'Niacinamide', 'Gel anti-taches',         1, 20.00, 50, 90, 9, 1, 2, 1),
    ('Kayali',                 '3600000000019', 'Parfum', 'Eau de parfum',                  1, 45.00, 60, 60, 6, 2, 3, 1),
    ('NÂ°5',                    '3600000000020', 'Parfum', 'Eau de parfum',                  1, 80.00, 60, 60, 6, 2, 3, 1),
    ('La Vie Est Belle',       '3600000000021', 'Parfum', 'Eau de parfum',                  1, 70.00, 60, 60, 6, 2, 3, 1),
    ('Phyto Corrective Masque','3600000000022', 'Extraits botaniques', 'Masque apaisant',   1, 22.00, 50, 70, 7, 1, 1, 1),
    ('Cellular Hydralift Firming Mask','3600000000023', 'Peptides', 'Masque fermetÃ©',       1, 28.00, 50, 70, 7, 2, 4, 1),
    ('Masque de nuit rÃ©parateur','3600000000024', 'Beurre de karitÃ©', 'Masque de nuit',     1, 24.00, 50, 70, 7, 2, 4, 1);

-- Lien produit <-> type (via la table d'association lien_product_type)
INSERT INTO lien_product_type (product_id, product_type_id) VALUES
    (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),  -- SÃ©rums
    (13,2),(14,2),(15,2),                                                         -- CrÃ¨mes
    (16,3),(17,3),(18,3),                                                         -- Gels
    (19,4),(20,4),(21,4),                                                         -- Parfums
    (22,5),(23,5),(24,5);                                                         -- Masques





-- Adresses
INSERT INTO addresses
    (customer_id_account, address_label, address_name, address_firstname,
     address_adress_1, address_adress_2, address_adress_3, address_adress_4,
     address_postcode, address_city, address_country, address_is_default) VALUES
    (1, 'Domicile', 'Martin', 'Sophie', '12 rue des Lilas',     NULL, NULL, NULL, '86100', 'ChÃ¢tellerault', 'France', 1),
    (2, 'Domicile', 'Dupont', 'Lucas',  '5 avenue Victor Hugo', NULL, NULL, NULL, '75002', 'Paris',         'France', 1);

-- Livraisons
INSERT INTO deliveries
    (delivery_number, delivery_cost, delivery_tracking_number, delivery_date,
     customer_id_account, address_id, delivery_type_id) VALUES
    (1001, 4.99, 'TRK0001', '2026-05-10 10:00:00', 1, 1, 1),
    (1002, 0.00, 'TRK0002', '2026-05-12 14:30:00', 2, 2, 2);

-- Commandes
INSERT INTO orders
    (order_number, order_date, order_date_annulation, order_promotion,
     order_type_id, payment_type_id, company_id_account, customer_id_account, delivery_type_id, deliveries_id) VALUES
    ('CMD-2026-001', '2026-05-09 09:15:00', NULL, NULL, 1, 1, 1, 1, 1, 1),
    ('CMD-2026-002', '2026-05-11 16:40:00', NULL, 10,   2, 3, 1, 2, 2, 2);

-- Contenu des commandes
INSERT INTO contains (product_id, order_id) VALUES
    (1, 1), (4, 1), (16, 2);

-- Contenu des promotions
INSERT INTO promotions (product_id, promotion_percent, promotion_is_active) VALUES
    (1, 20, 1),
    (4, 27, 1);
-- Insertion des rÃ©initialisations de mot de passe
INSERT INTO password_resets (
  user_id, 
  reset_token, 
  reset_expires_at
) VALUES (
  1, 
  '9f82c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2', 
  DATE_ADD(NOW(), INTERVAL 1 HOUR)
);

INSERT INTO password_resets (
  user_id, 
  reset_token, 
  reset_expires_at
) VALUES (
  2, 
  'a1b2c3d4e5f67890abcdef1234567890abcdef1234567890abcdef1234567890', 
  '2026-07-02 18:00:00'
);


-- MODULE : Programme de fidÃ©litÃ© 


DROP TABLE IF EXISTS loyalty_vouchers;
DROP TABLE IF EXISTS loyalty_points;
DROP TABLE IF EXISTS loyalty_tiers;


-- Table de rÃ©fÃ©rence : les paliers 

CREATE TABLE loyalty_tiers (
  loyalty_tier_id               INT AUTO_INCREMENT,
  loyalty_tier_name             VARCHAR(20) NOT NULL,
  loyalty_tier_min_points       INT NOT NULL,
  loyalty_tier_discount_percent INT NOT NULL DEFAULT 0,
  loyalty_tier_is_free_shipping BOOLEAN NOT NULL DEFAULT 0,
  PRIMARY KEY (loyalty_tier_id),
  UNIQUE (loyalty_tier_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Journal des mouvements de points 

CREATE TABLE loyalty_points (
  loyalty_point_id         INT AUTO_INCREMENT,
  customer_id_account      INT NOT NULL,
  order_id                 INT DEFAULT NULL,
  loyalty_point_amount     INT NOT NULL,
  loyalty_point_type       VARCHAR(20) NOT NULL,
  loyalty_point_label      VARCHAR(100) NOT NULL,
  loyalty_point_expires_at DATE DEFAULT NULL,
  loyalty_point_created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (loyalty_point_id),
  FOREIGN KEY (customer_id_account) REFERENCES customers(customer_id_account),
  FOREIGN KEY (order_id) REFERENCES orders(order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index 

CREATE INDEX idx_loyalty_points_customer
  ON loyalty_points (customer_id_account, loyalty_point_created_at);


-- Bons de rÃ©duction 

CREATE TABLE loyalty_vouchers (
  loyalty_voucher_id         INT AUTO_INCREMENT,
  customer_id_account        INT NOT NULL,
  loyalty_voucher_code       VARCHAR(20) NOT NULL,
  loyalty_voucher_amount     DECIMAL(10,2) NOT NULL,
  loyalty_voucher_points_used INT NOT NULL,
  loyalty_voucher_is_used    BOOLEAN NOT NULL DEFAULT 0,
  loyalty_voucher_expires_at DATE NOT NULL,
  loyalty_voucher_created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (loyalty_voucher_id),
  UNIQUE (loyalty_voucher_code),
  FOREIGN KEY (customer_id_account) REFERENCES customers(customer_id_account)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Jeu de donnÃ©es  les 3 paliers

INSERT INTO loyalty_tiers
  (loyalty_tier_name, loyalty_tier_min_points, loyalty_tier_discount_percent, loyalty_tier_is_free_shipping)
VALUES
  ('Bronze', 0,    0,  0),
  ('Argent', 500,  5,  0),
  ('Or',     1500, 10, 1);
  
