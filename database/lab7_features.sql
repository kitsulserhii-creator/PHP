-- ===============================================
-- Лабораторна робота №7: Додатковий функціонал
-- ===============================================
-- Варіант 1: Система коментарів
-- Варіант 5: Категорії автомобілів
-- ===============================================

USE phpp_portfolio;

-- ===============================================
-- Варіант 5: Таблиця категорій (categories)
-- ===============================================

CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Назва категорії',
    `description` TEXT DEFAULT NULL COMMENT 'Опис категорії',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Додавання поля category_id до таблиці automobiles
ALTER TABLE `automobiles` 
ADD COLUMN `category_id` INT UNSIGNED DEFAULT NULL AFTER `author_id`,
ADD CONSTRAINT `fk_automobile_category` 
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
ADD INDEX `idx_category` (`category_id`);

-- Тестові категорії
INSERT INTO `categories` (`name`, `description`) VALUES
('Седан', 'Класичні седани - комфортні автомобілі для сім\'ї та бізнесу'),
('Позашляховик', 'SUV та кросовери - для міста та бездоріжжя'),
('Хетчбек', 'Компактні та економічні міські автомобілі'),
('Електромобіль', 'Екологічні автомобілі на електричній тязі'),
('Преміум', 'Автомобілі преміум та люкс класу'),
('Спорткар', 'Спортивні автомобілі з високими динамічними характеристиками');

-- Оновлення існуючих автомобілів з категоріями
UPDATE `automobiles` SET `category_id` = 1 WHERE `brand` = 'Toyota' AND `model` = 'Camry';
UPDATE `automobiles` SET `category_id` = 2 WHERE `brand` = 'BMW' AND `model` = 'X5';
UPDATE `automobiles` SET `category_id` = 5 WHERE `brand` = 'Mercedes-Benz' AND `model` = 'E-Class';
UPDATE `automobiles` SET `category_id` = 1 WHERE `brand` = 'Honda' AND `model` = 'Civic';
UPDATE `automobiles` SET `category_id` = 2 WHERE `brand` = 'Audi' AND `model` = 'Q7';
UPDATE `automobiles` SET `category_id` = 3 WHERE `brand` = 'Volkswagen' AND `model` = 'Golf';
UPDATE `automobiles` SET `category_id` = 4 WHERE `brand` = 'Tesla' AND `model` = 'Model 3';

-- ===============================================
-- Варіант 1: Таблиця коментарів (comments)
-- ===============================================

CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `automobile_id` INT UNSIGNED NOT NULL COMMENT 'ID автомобіля',
    `user_id` INT UNSIGNED NOT NULL COMMENT 'ID користувача',
    `comment_text` TEXT NOT NULL COMMENT 'Текст коментаря',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата створення',
    INDEX `idx_automobile` (`automobile_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`automobile_id`) REFERENCES `automobiles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Тестові коментарі
INSERT INTO `comments` (`automobile_id`, `user_id`, `comment_text`) VALUES
(1, 2, 'Чудовий автомобіль! Дуже надійний та економічний. Раджу!'),
(1, 1, 'Дякуємо за відгук! Camry дійсно один з найкращих варіантів в своєму класі.'),
(2, 2, 'Скільки витрачає пального на 100 км в змішаному циклі?'),
(2, 1, 'В середньому 10-11 літрів. Для позашляховика цього класу - чудовий показник.'),
(3, 2, 'Шикарний автомобіль! Мрію про такий. Чи можна тест-драйв?'),
(5, 2, 'Чи є можливість розстрочки?'),
(1, 2, 'Ще одне питання - чи була участь у ДТП?');

-- ===============================================
-- Перевірка структури
-- ===============================================

DESCRIBE categories;
DESCRIBE comments;

-- Перевірка даних
SELECT * FROM categories ORDER BY name;

SELECT 
    c.id,
    c.comment_text,
    u.login as author,
    a.brand,
    a.model,
    c.created_at
FROM comments c
JOIN users u ON c.user_id = u.id
JOIN automobiles a ON c.automobile_id = a.id
ORDER BY c.created_at DESC;

-- Автомобілі з категоріями
SELECT 
    a.id,
    a.brand,
    a.model,
    cat.name as category,
    a.visible
FROM automobiles a
LEFT JOIN categories cat ON a.category_id = cat.id
ORDER BY a.date DESC;

-- Статистика коментарів по автомобілях
SELECT 
    a.brand,
    a.model,
    COUNT(c.id) as comments_count
FROM automobiles a
LEFT JOIN comments c ON a.id = c.automobile_id
GROUP BY a.id
ORDER BY comments_count DESC;
