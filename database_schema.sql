-- Smart Electric Shop Management System - SQL Schema

-- Admin Tables
CREATE TABLE Main_Admin (
    main_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50)
);

CREATE TABLE Admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    main_id INT,
    name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    phone_number VARCHAR(20),
    FOREIGN KEY (main_id) REFERENCES Main_Admin(main_id)
);

CREATE TABLE Staff (
    staff_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    name VARCHAR(50),
    email VARCHAR(100),
    password VARCHAR(255),
    phone_number VARCHAR(20),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);


-- User & Reward Points
CREATE TABLE User (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    phone_number VARCHAR(20),
    order_id INT,
    warranty_id INT
);

CREATE TABLE RewardPoints (
    points_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    points INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE Handles (
    points_id INT,
    admin_id INT,
    PRIMARY KEY (points_id, admin_id),
    FOREIGN KEY (points_id) REFERENCES RewardPoints(points_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

-- Product, BulkPricing, and Energy
CREATE TABLE Product (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2),
    warranty_duration INT,
    available_quantity INT,
    admin_id INT,
    warranty_id INT,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

CREATE TABLE BulkPricing (
    product_no INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    min_quantity INT,
    discount_percentage DECIMAL(5,2),
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
);

CREATE TABLE EnergyUsage (
    energy_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    wattage INT,
    hours_used INT,
    estimated_energy DECIMAL(10,2),
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
);

-- Order/Cart System
CREATE TABLE `Order` (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_date DATETIME,
    payment_status VARCHAR(30),
    total_amount DECIMAL(10,2),
    discount DECIMAL(10,2),
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE OrderItem (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES `Order`(order_id),
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
);

-- Warranty & Service
CREATE TABLE Warranty (
    warranty_id INT PRIMARY KEY AUTO_INCREMENT,
    warranty_duration INT,
    purchase_date DATE
);

CREATE TABLE CanManage (
    warranty_id INT,
    admin_id INT,
    PRIMARY KEY (warranty_id, admin_id),
    FOREIGN KEY (warranty_id) REFERENCES Warranty(warranty_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

CREATE TABLE ServiceRequest (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    warranty_id INT,
    issue TEXT,
    status VARCHAR(30),
    FOREIGN KEY (user_id) REFERENCES User(user_id),
    FOREIGN KEY (warranty_id) REFERENCES Warranty(warranty_id)
);

CREATE TABLE DealsWith (
    request_id INT,
    admin_id INT,
    PRIMARY KEY (request_id, admin_id),
    FOREIGN KEY (request_id) REFERENCES ServiceRequest(request_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

-- Access Control/Misc. Relationships
CREATE TABLE CanGiveAccess (
    user_id INT,
    main_id INT,
    PRIMARY KEY (user_id, main_id),
    FOREIGN KEY (user_id) REFERENCES User(user_id),
    FOREIGN KEY (main_id) REFERENCES Main_Admin(main_id)
);

CREATE TABLE Conducts (
    item_id INT,
    admin_id INT,
    PRIMARY KEY (item_id, admin_id),
    FOREIGN KEY (item_id) REFERENCES OrderItem(item_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

CREATE TABLE CanCheckOrder (
    order_id INT,
    admin_id INT,
    PRIMARY KEY (order_id, admin_id),
    FOREIGN KEY (order_id) REFERENCES `Order`(order_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

