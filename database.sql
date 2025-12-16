-- Hostel Mess Management System Database
-- Home & Search Module Tables

CREATE DATABASE IF NOT EXISTS hostel_mess;
USE hostel_mess;

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_number VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    photo_path VARCHAR(255),
    hostel_name VARCHAR(100) NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    food_preference ENUM('veg', 'non-veg') NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Meal Records Table: track each meal usage (breakfast/lunch/dinner)
CREATE TABLE IF NOT EXISTS meal_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    meal_type ENUM('breakfast','lunch','dinner') NOT NULL,
    meal_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Staff Table: store mess staff members
CREATE TABLE IF NOT EXISTS staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(120) NOT NULL,
    role VARCHAR(80) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(120),
    joined_at DATE,
    active ENUM('yes','no') DEFAULT 'yes'
);

-- Feedback Table: students submit ratings and comments
CREATE TABLE IF NOT EXISTS feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    account_number VARCHAR(50),
    rating TINYINT NOT NULL DEFAULT 5,
    quality TINYINT DEFAULT 0,
    hygiene TINYINT DEFAULT 0,
    service TINYINT DEFAULT 0,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL
);

-- Menu Table: daily/weekly menu entries
CREATE TABLE IF NOT EXISTS menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    menu_date DATE NOT NULL,
    meal_type ENUM('breakfast','lunch','dinner') NOT NULL,
    title VARCHAR(200),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (menu_date, meal_type)
);

-- Meal Interest Table: students mark interested/not-interested for a menu item
CREATE TABLE IF NOT EXISTS meal_interest (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    menu_id INT NOT NULL,
    interested ENUM('yes','no') NOT NULL DEFAULT 'no',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
);

-- Billing Table: record bills and payment status
CREATE TABLE IF NOT EXISTS billing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    due_date DATE,
    status ENUM('unpaid','paid','partial') DEFAULT 'unpaid',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
