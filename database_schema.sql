-- Main admin
CREATE TABLE Main_Admin (
    main_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50)
);

-- Admins
CREATE TABLE Admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    main_id INT DEFAULT NULL,
    name VARCHAR(50) DEFAULT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) DEFAULT NULL,
    FOREIGN KEY (main_id) REFERENCES Main_Admin(main_id)
);

-- Staff members
CREATE TABLE Staff (
    staff_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) DEFAULT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) DEFAULT NULL
);

-- Users
CREATE TABLE `User` (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) DEFAULT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) DEFAULT NULL,
    order_id INT DEFAULT NULL,
    warranty_id INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE current_timestamp()
);

-- Reward points
CREATE TABLE RewardPoints (
    points_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    points INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES `User`(user_id)
);

-- Products and related
CREATE TABLE Product (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) DEFAULT NULL,
    warranty_duration INT DEFAULT NULL,
    available_quantity INT DEFAULT NULL,
    reward_points INT DEFAULT 0,
    images TEXT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    warranty_id INT DEFAULT NULL,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

CREATE TABLE BulkPricing (
    product_no INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT DEFAULT NULL,
    min_quantity INT DEFAULT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
);

CREATE TABLE EnergyUsage (
    energy_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT DEFAULT NULL,
    wattage INT DEFAULT NULL,
    hours_used INT DEFAULT NULL,
    estimated_energy DECIMAL(10,2) DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
);

-- Orders and items
CREATE TABLE `Order` (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    order_date DATETIME DEFAULT NULL,
    payment_status VARCHAR(30) DEFAULT NULL,
    total_amount DECIMAL(10,2) DEFAULT NULL,
    discount DECIMAL(10,2) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES `User`(user_id)
);

CREATE TABLE OrderItem (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT DEFAULT NULL,
    product_id INT DEFAULT NULL,
    quantity INT DEFAULT NULL,
    price DECIMAL(10,2) DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES `Order`(order_id),
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
);

-- Warranty & Service
CREATE TABLE Warranty (
    warranty_id INT PRIMARY KEY AUTO_INCREMENT,
    warranty_duration INT DEFAULT NULL,
    purchase_date DATE DEFAULT NULL
);


CREATE TABLE ServiceRequest (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    warranty_id INT DEFAULT NULL,
    issue TEXT DEFAULT NULL,
    status VARCHAR(30) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES `User`(user_id),
    FOREIGN KEY (warranty_id) REFERENCES Warranty(warranty_id)
);


CREATE TABLE CanCheckOrder (
    order_id INT,
    admin_id INT,
    PRIMARY KEY (order_id, admin_id),
    FOREIGN KEY (order_id) REFERENCES `Order`(order_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

CREATE TABLE ContactMessages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    name VARCHAR(100) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    status VARCHAR(30) DEFAULT 'Open',
    response_text TEXT DEFAULT NULL,
    responded_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES `User`(user_id)
);

