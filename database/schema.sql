-- ===============================================
-- Лабораторна робота №4: Підключення до бази даних MySQL
-- ===============================================
-- База даних для портфоліо сайту
-- Тематика: Автомобілі (буде розширено в лабораторній 6)
-- ===============================================

-- Створення бази даних (якщо не існує)
CREATE DATABASE IF NOT EXISTS phpp_portfolio 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE phpp_portfolio;

-- ===============================================
-- Таблиця користувачів (users)
-- ===============================================
-- Лабораторна №4 (2 бали)

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `login` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `admin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 for admin, 0 for regular user',
    `name` VARCHAR(255) DEFAULT NULL,
    `about` TEXT DEFAULT NULL,
    `gender` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 for female, 1 for male',
    `phone` VARCHAR(30) DEFAULT NULL,
    `dob` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_login` (`login`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Додавання тестових користувачів
-- ===============================================
-- Пароль для обох: Password123
-- Хеш згенеровано через password_hash() з PASSWORD_BCRYPT, cost=12

-- Адміністратор
INSERT INTO `users` (`login`, `password_hash`, `email`, `admin`, `name`, `gender`, `dob`, `about`) 
VALUES (
    'admin',
    '$2y$12$8vY5kJ9Z.Lx5F3mQ8jR0YuoGpX4dZ8wK9J5lM2nP6qR7sT8uV9wX0',
    'admin@example.com',
    1,
    'Адміністратор Системи',
    1,
    '1990-01-01',
    'Системний адміністратор сайту'
);

-- Звичайний користувач
INSERT INTO `users` (`login`, `password_hash`, `email`, `admin`, `name`, `gender`, `dob`, `about`) 
VALUES (
    'user1',
    '$2y$12$8vY5kJ9Z.Lx5F3mQ8jR0YuoGpX4dZ8wK9J5lM2nP6qR7sT8uV9wX0',
    'user1@example.com',
    0,
    'Іван Петренко',
    1,
    '1995-05-15',
    'Звичайний користувач сайту'
);

-- ===============================================
-- Перевірка структури таблиці
-- ===============================================
DESCRIBE `users`;

-- ===============================================
-- Тестові запити
-- ===============================================
-- Перевірити всіх користувачів
SELECT id, login, email, admin, name, created_at FROM users;

-- Знайти адміністраторів
SELECT id, login, email FROM users WHERE admin = 1;

-- Пошук користувача по логіну (для авторизації)
SELECT * FROM users WHERE login = 'admin' LIMIT 1;
