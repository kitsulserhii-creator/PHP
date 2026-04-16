-- ===============================================
-- Лабораторна робота №6: CRUD-сторінки
-- ===============================================
-- Тематика: Автомобілі
-- ===============================================

USE phpp_portfolio;

-- ===============================================
-- Таблиця автомобілів (automobiles)
-- ===============================================

CREATE TABLE IF NOT EXISTS `automobiles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `brand` VARCHAR(100) NOT NULL COMMENT 'Марка автомобіля',
    `model` VARCHAR(100) NOT NULL COMMENT 'Модель автомобіля',
    `year` YEAR NOT NULL COMMENT 'Рік випуску',
    `color` VARCHAR(50) DEFAULT NULL COMMENT 'Колір',
    `price` DECIMAL(10, 2) DEFAULT NULL COMMENT 'Ціна в USD',
    `mileage` INT UNSIGNED DEFAULT NULL COMMENT 'Пробіг в км',
    `description` TEXT DEFAULT NULL COMMENT 'Опис автомобіля',
    `visible` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = опублікований, 0 = не опублікований',
    `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата додавання',
    `author_id` INT UNSIGNED NOT NULL COMMENT 'ID користувача, який додав',
    INDEX `idx_visible` (`visible`),
    INDEX `idx_date` (`date`),
    INDEX `idx_author` (`author_id`),
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Тестові дані (автомобілі)
-- ===============================================

-- Опубліковані автомобілі (visible = 1) - адмін
INSERT INTO `automobiles` (`brand`, `model`, `year`, `color`, `price`, `mileage`, `description`, `visible`, `author_id`) VALUES
('Toyota', 'Camry', 2022, 'Срібний', 28500.00, 15000, 'Надійний сімейний седан в ідеальному стані. Повна комплектація, один власник.', 1, 1),
('BMW', 'X5', 2021, 'Чорний', 65000.00, 25000, 'Преміум позашляховик з панорамним дахом та шкіряним салоном. Сервісна книжка в наявності.', 1, 1),
('Mercedes-Benz', 'E-Class', 2023, 'Білий', 72000.00, 5000, 'Новий автомобіль бізнес-класу. Повний пакет опцій, гарантія виробника.', 1, 1),
('Honda', 'Civic', 2020, 'Синій', 22000.00, 35000, 'Економічний та надійний компактний седан. Регулярне ТО, без ДТП.', 1, 1),
('Audi', 'Q7', 2022, 'Сірий', 78000.00, 12000, 'Просторий 7-місний позашляховик преміум класу. Quattro, повна комплектація.', 1, 1);

-- Неопубліковані автомобілі (visible = 0) - звичайний користувач
INSERT INTO `automobiles` (`brand`, `model`, `year`, `color`, `price`, `mileage`, `description`, `visible`, `author_id`) VALUES
('Volkswagen', 'Golf', 2019, 'Червоний', 18500.00, 45000, 'Популярний хетчбек в доброму стані. Економічний двигун 1.4 TSI.', 0, 2),
('Tesla', 'Model 3', 2022, 'Білий', 45000.00, 8000, 'Електромобіль з автопілотом. Запас ходу 500 км. Зарядка в подарунок.', 0, 2);

-- ===============================================
-- Перевірка даних
-- ===============================================

-- Всі автомобілі
SELECT a.id, a.brand, a.model, a.year, a.visible, u.login as author 
FROM automobiles a 
JOIN users u ON a.author_id = u.id
ORDER BY a.date DESC;

-- Тільки опубліковані
SELECT * FROM automobiles WHERE visible = 1 ORDER BY date DESC;

-- Статистика
SELECT 
    COUNT(*) as total,
    SUM(visible = 1) as published,
    SUM(visible = 0) as unpublished
FROM automobiles;
