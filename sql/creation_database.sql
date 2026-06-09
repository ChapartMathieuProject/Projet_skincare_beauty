DROP DATABASE IF EXISTS Skincarebeauty;

create database Skincarebeauty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
use Skincarebeauty; 



-- TABLE : roles utlisateur admin ou client 
CREATE TABLE users(
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    user_mail VARCHAR(50) not null,
    user_type_account char(1)NOT NULL,
    user_password VARCHAR (20) NOT NULL,
    UNIQUE (user_mail)
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


-- TABLE : adresse
CREATE TABLE addresses(
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    address_name VARCHAR(50) NOT NULL,
    address_firstname VARCHAR(30) NOT NULL,
    address_adress_1 VARCHAR(50) NOT NULL,
    address_adress_2 VARCHAR(50),
    address_adress_3 VARCHAR(50),
    address_adress_4 VARCHAR(50),
    address_postcode VARCHAR(5) NOT NULL,
    address_city VARCHAR(30) NOT NULL,
    address_country VARCHAR(30) NOT NULL
)engine= Innodb DEFAULT charset=utf8mb4;


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
    producer_id INT NOT NULL,
    brand_id INT NOT NULL,
    company_id_account INT NOT NULL,
    foreign key (producer_id) references producers(producer_id),
    foreign key (brand_id) references brands(brand_id),
    foreign key (company_id_account) references companies(company_id_account)
)engine= Innodb Default charset=utf8mb4;


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
    delivery_number          INT NOT NULL UNIQUE,
    delivery_cost            DECIMAL(10,2) NOT NULL,
    delivery_tracking_number VARCHAR(50) NOT NULL,
    delivery_date            DATETIME NOT NULL,
    customer_id_account      INT NOT NULL,
    address_id               INT NOT NULL,            -- corrigé : "adress_id" -> address_id + FK
    delivery_type_id         INT NOT NULL,            -- colonne ajoutée (manquait)
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
    delivery_type_id INT NOT NULL,
    deliveries_id INT NOT NULL,
    FOREIGN KEY (order_type_id) REFERENCES order_status(order_type_id),
    FOREIGN KEY (payment_type_id) REFERENCES payement_types(payement_type_id),
    FOREIGN KEY (company_id_account) REFERENCES companies(company_id_account),
    FOREIGN KEY (delivery_type_id) REFERENCES delivery_types(delivery_type_id),
    FOREIGN KEY (deliveries_id) REFERENCES deliveries(delivery_id)
)engine= Innodb Default charset=utf8mb4;


CREATE TABLE bills(
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    bill_number VARCHAR(10) NOT NULL,
    bill_delivery_date DATE NOT NULL,
    bill_number_delivery VARCHAR(50) NOT NULL,
    delivery_id INT NOT NULL,
    UNIQUE (bill_number),
    FOREIGN KEY (delivery_id) REFERENCES deliveries(delivery_id)
)engine= Innodb Default charset=utf8mb4;



CREATE TABLE contains ( 
    product_id INT PRIMARY KEY,
    order_id INT,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
)engine= Innodb Default charset=utf8mb4;

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






INSERT INTO users (user_mail, user_type_account, user_password) VALUES
    ('admin@skincare.com',      'A', 'Admin1234!'),
    ('sophie.martin@email.com', 'C', 'Client1234!'),
    ('lucas.dupont@email.com',  'C', 'Client1234!'),
    ('emma.bernard@email.com',  'C', 'Client1234!');

INSERT INTO genders (gender_name) VALUES
    ('Monsieur'), ('Madame'), ('Docteur');

INSERT INTO companies (
    company_name_account, company_password_account, company_name,
    company_adress_1, company_adress_2, company_adress_3, company_adress_4,
    company_postcode, company_city, company_country
) VALUES
    ('loreal', 'company1234', 'Camil SA', '123 Rue de Paris', '17 mai',
     'Bâtiment A', '2ème étage', '75001', 'Paris', 'France');

INSERT INTO delivery_types (delivery_type_name) VALUES
    ('Livraison à domicile'), ('Retrait sur place'),
    ('Mondial Relay'), ('UPS'), ('Chronopost');

INSERT INTO payement_types (payement_type_name) VALUES
    ('Carte Bancaire'), ('Virement'), ('Paypal'),
    ('Google Pay'), ('Apple Pay');

INSERT INTO order_status (order_type_name) VALUES
    ('En cours'), ('Expédié'), ('Annulé');

INSERT INTO producers (producer_name) VALUES
    ('Roche-posay'), ("L'Oréal");

INSERT INTO brands (brand_name, producer_id) VALUES
    ('La Roche-posay',      1),
    ('Effaclar',            1),
    ('Maybelline',          2),
    ('Professional Makeup', 2);

INSERT INTO product_types (product_type_name) VALUES
    ('Sérum'), ('Crème'), ('Gel'), ('Parfum'), ('Masque');

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
    ('P-Tiox',                 '3600000000001', 'Aqua, Glycerin', 'Sérum anti-âge',        1, 15.00, 60, 100, 10, 1, 1, 1),
    ('Age Interrupter',        '3600000000002', 'Aqua, Glycerin', 'Sérum anti-âge',        1, 18.00, 55, 100, 10, 1, 1, 1),
    ('H.A Intensifier',        '3600000000003', 'Acide hyaluronique', 'Sérum hydratant',   1, 20.00, 50, 100, 10, 1, 1, 1),
    ('C.E Ferulic',            '3600000000004', 'Vitamine C, E', 'Sérum antioxydant',      1, 25.00, 50, 100, 10, 1, 1, 1),
    ('Phloretin CF',           '3600000000005', 'Phloretine', 'Sérum éclat',               1, 24.00, 50, 100, 10, 1, 1, 1),
    ('Cell Cycle Catalyst',    '3600000000006', 'AHA', 'Sérum exfoliant',                  1, 22.00, 50, 100, 10, 1, 1, 1),
    ('Serum 10',               '3600000000007', 'Vitamine C', 'Sérum éclat',               1, 16.00, 55, 100, 10, 1, 2, 1),
    ('Discoloration Defense',  '3600000000008', 'Niacinamide', 'Sérum anti-taches',        1, 19.00, 50, 100, 10, 1, 2, 1),
    ('Blemish Age Defense',    '3600000000009', 'Acide salicylique', 'Sérum imperfections',1, 21.00, 50, 100, 10, 1, 2, 1),
    ('Mela B3 Serum',          '3600000000010', 'Mélasyl, Niacinamide', 'Sérum anti-taches',1, 23.00, 50, 100, 10, 1, 1, 1),
    ('Collagen III Amplifier', '3600000000011', 'Peptides', 'Sérum fermeté',               1, 26.00, 50, 100, 10, 1, 1, 1),
    ('Phyto Corrective',       '3600000000012', 'Extraits botaniques', 'Sérum apaisant',   1, 20.00, 50, 100, 10, 1, 1, 1),
    ('Age Interrupter Triple Lipid Restore', '3600000000013', 'Céramides', 'Crème anti-âge', 1, 30.00, 50, 80, 8, 2, 3, 1),
    ('Hydra Beauty Micro Gel Crème',         '3600000000014', 'Camélia', 'Crème hydratante', 1, 35.00, 50, 80, 8, 2, 3, 1),
    ('Sublimage La Crème Lumière',           '3600000000015', 'PFA', 'Crème lumière',        1, 40.00, 50, 80, 8, 2, 3, 1),
    ('Advanced Hyalu B5 Gel',  '3600000000016', 'Acide hyaluronique, B5', 'Gel hydratant',  1, 18.00, 55, 90, 9, 1, 1, 1),
    ('Phyto Corrective Gel',   '3600000000017', 'Extraits botaniques', 'Gel apaisant',      1, 19.00, 50, 90, 9, 1, 1, 1),
    ('Discoloration Defense Gel','3600000000018', 'Niacinamide', 'Gel anti-taches',         1, 20.00, 50, 90, 9, 1, 2, 1),
    ('Kayali',                 '3600000000019', 'Parfum', 'Eau de parfum',                  1, 45.00, 60, 60, 6, 2, 3, 1),
    ('N°5',                    '3600000000020', 'Parfum', 'Eau de parfum',                  1, 80.00, 60, 60, 6, 2, 3, 1),
    ('La Vie Est Belle',       '3600000000021', 'Parfum', 'Eau de parfum',                  1, 70.00, 60, 60, 6, 2, 3, 1),
    ('Phyto Corrective Masque','3600000000022', 'Extraits botaniques', 'Masque apaisant',   1, 22.00, 50, 70, 7, 1, 1, 1),
    ('Cellular Hydralift Firming Mask','3600000000023', 'Peptides', 'Masque fermeté',       1, 28.00, 50, 70, 7, 2, 4, 1),
    ('Masque de nuit réparateur','3600000000024', 'Beurre de karité', 'Masque de nuit',     1, 24.00, 50, 70, 7, 2, 4, 1);

-- Lien produit <-> type (via la table d'association lien_product_type)
INSERT INTO lien_product_type (product_id, product_type_id) VALUES
    (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),  -- Sérums
    (13,2),(14,2),(15,2),                                                         -- Crèmes
    (16,3),(17,3),(18,3),                                                         -- Gels
    (19,4),(20,4),(21,4),                                                         -- Parfums
    (22,5),(23,5),(24,5);                                                         -- Masques

-- Adresses
INSERT INTO addresses
    (address_name, address_firstname, address_adress_1, address_adress_2,
     address_adress_3, address_adress_4, address_postcode, address_city, address_country) VALUES
    ('Martin', 'Sophie', '12 rue des Lilas',      NULL, NULL, NULL, '86100', 'Châtellerault', 'France'),
    ('Dupont', 'Lucas',  '5 avenue Victor Hugo',  NULL, NULL, NULL, '75002', 'Paris',         'France');

-- Livraisons
INSERT INTO deliveries
    (delivery_number, delivery_cost, delivery_tracking_number, delivery_date,
     customer_id_account, address_id, delivery_type_id) VALUES
    (1001, 4.99, 'TRK0001', '2026-05-10 10:00:00', 1, 1, 1),
    (1002, 0.00, 'TRK0002', '2026-05-12 14:30:00', 2, 2, 2);

-- Commandes
INSERT INTO orders
    (order_number, order_date, order_date_annulation, order_promotion,
     order_type_id, payment_type_id, company_id_account, delivery_type_id, deliveries_id) VALUES
    ('CMD-2026-001', '2026-05-09 09:15:00', NULL, NULL, 1, 1, 1, 1, 1),
    ('CMD-2026-002', '2026-05-11 16:40:00', NULL, 10,   2, 3, 1, 2, 2);

-- Factures
INSERT INTO bills (bill_number, bill_delivery_date, bill_number_delivery, delivery_id) VALUES
    ('F0001', '2026-05-10', 'BL-0001', 1);

-- Contenu des commandes
INSERT INTO contains (product_id, order_id) VALUES
    (1, 1), (4, 1), (16, 2);