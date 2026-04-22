-- =============================================
-- PHP Portfolio - Complete Database Export
-- =============================================
-- Database: phpp_portfolio
-- Charset: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- Export Date: 2024
-- =============================================

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS phpp_portfolio 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE phpp_portfolio;

-- Drop tables in reverse dependency order (to avoid FK constraint errors)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS automobiles;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- TABLE: users
-- =============================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    admin TINYINT(1) NOT NULL DEFAULT 0,
    name VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    phone VARCHAR(20),
    dob DATE,
    about TEXT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_login (login),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test users (passwords: Password123)
INSERT INTO users (login, password_hash, email, admin, name, gender, phone, dob, about) VALUES
('admin', '$2y$12$7HqE8QZJxNlCZ.Pu3MkWYO0hBqvY9X3p5X2R8mP.vU5yK8qG1C2Wa', 'admin@example.com', 1, 'Administrator', 'male', '+380991234567', '1990-01-15', 'System administrator with full access rights'),
('user1', '$2y$12$7HqE8QZJxNlCZ.Pu3MkWYO0hBqvY9X3p5X2R8mP.vU5yK8qG1C2Wa', 'user1@example.com', 0, 'John Doe', 'male', '+380502345678', '1995-06-20', 'Regular user account for testing');

-- =============================================
-- TABLE: categories
-- =============================================

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test categories
INSERT INTO categories (name, description) VALUES
('Седани', 'Класичні чотиридверні автомобілі з окремим багажником'),
('Позашляховики', 'Автомобілі для їзди по бездоріжжю з підвищеною прохідністю'),
('Купе', 'Спортивні дводверні автомобілі'),
('Хетчбеки', 'Компактні автомобілі з дверима багажника'),
('Універсали', 'Автомобілі з подовженим кузовом та великим багажником'),
('Електромобілі', 'Автомобілі з електричним двигуном');

-- =============================================
-- TABLE: automobiles
-- =============================================

CREATE TABLE automobiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    color VARCHAR(50),
    price DECIMAL(10, 2),
    mileage INT,
    description TEXT,
    visible TINYINT(1) NOT NULL DEFAULT 0,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    author_id INT NOT NULL,
    category_id INT NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_visible (visible),
    INDEX idx_date (date),
    INDEX idx_author (author_id),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test automobiles
INSERT INTO automobiles (brand, model, year, color, price, mileage, description, visible, author_id, category_id) VALUES
('Toyota', 'Camry', 2020, 'Чорний', 25000.00, 35000, 'Надійний бізнес-седан в відмінному стані. Повне технічне обслуговування, один власник. Комплектація: шкіряний салон, клімат-контроль, парктроніки, камера заднього виду.', 1, 1, 1),
('BMW', 'X5', 2019, 'Білий', 48000.00, 45000, 'Преміум позашляховик з повним приводом. Спортивний пакет М, панорамний дах, проекція на лобове скло. Обслуговувався лише в офіційному дилерському центрі.', 1, 1, 2),
('Audi', 'A4', 2021, 'Сірий', 35000.00, 15000, 'Майже новий седан з мінімальним пробігом. Потужний 2.0 TFSI двигун, коробка S-tronic. Адаптивний круїз-контроль, LED-фари, віртуальна панель приладів.', 1, 1, 1),
('Mercedes-Benz', 'C-Class', 2018, 'Чорний', 32000.00, 60000, 'Елегантний седан з повною історією обслуговування. Дизельний двигун, економічний та динамічний. Мультимедійна система COMAND, шкіряний салон.', 1, 2, 1),
('Honda', 'CR-V', 2020, 'Синій', 28000.00, 40000, 'Практичний сімейний кросовер. Просторий салон, велике багажне відділення. Система безпеки Honda Sensing, камера кругового огляду.', 1, 2, 2),
('Volkswagen', 'Golf', 2019, 'Червоний', 18000.00, 50000, 'Популярний компактний хетчбек. Бензиновий двигун 1.5 TSI, робот DSG. Економічний, зручний у міському циклі. App-Connect з Apple CarPlay та Android Auto.', 0, 2, 4),
('Tesla', 'Model 3', 2022, 'Білий', 42000.00, 8000, 'Електромобіль майбутнього з автопілотом. Запас ходу 500 км, розгін до 100 км/год за 3.3 секунди. Мінімалістичний інтер\'єр, величезний центральний екран.', 1, 1, 6);

-- =============================================
-- TABLE: comments
-- =============================================

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    automobile_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (automobile_id) REFERENCES automobiles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_automobile (automobile_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test comments
INSERT INTO comments (automobile_id, user_id, comment_text) VALUES
(1, 2, 'Чудовий автомобіль! Шукаю саме таку Camry. Чи можна подивитись вживу?'),
(1, 1, 'Звичайно! Напишіть мені в особисті повідомлення, домовимось про зустріч.'),
(2, 2, 'Який стан салону? На фото виглядає ідеально.'),
(3, 2, 'Дуже хороша ціна для такого пробігу. Є сервісна книжка?'),
(4, 1, 'Mercedes завжди Mercedes! Якість на висоті.'),
(5, 2, 'Honda - це надійність. У мене батько 10 років на Accord їздить, жодних проблем.'),
(7, 2, 'Tesla - це мрія! Коли вже електромобілі стануть доступнішими?');

-- =============================================
-- VERIFICATION QUERIES
-- =============================================

-- Verify users
SELECT 'users' as table_name, COUNT(*) as record_count FROM users;

-- Verify categories
SELECT 'categories' as table_name, COUNT(*) as record_count FROM categories;

-- Verify automobiles
SELECT 'automobiles' as table_name, COUNT(*) as record_count FROM automobiles;

-- Verify comments
SELECT 'comments' as table_name, COUNT(*) as record_count FROM comments;

-- =============================================
-- Expected Output:
-- users: 2 records
-- categories: 6 records
-- automobiles: 7 records
-- comments: 7 records
-- =============================================
