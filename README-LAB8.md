# Lab 8: Розгортання проєкту (10 балів)

## Опис лабораторної роботи

Lab 8 присвячена підготовці проєкту до **production deployment** — розгортанню на реальному хостингу. У цій лабораторній виконано налаштування безпеки, оптимізацію продуктивності, створено інструкції та файли конфігурації для швидкого розгортання.

**Оцінювання:** 10 балів  
**Гілка:** `lab-8-deployment`

---

## Реалізовані функції

### ✅ 1. Конфігурація для середовищ (Development / Production)

**Створено файли:**
- `.env.example` — шаблон змінних оточення для БД
- `.gitignore` — виключення .env та службових файлів із Git
- Модифіковано `config/database.php` — підтримка .env файлу

**Принцип роботи:**
1. Розробник копіює `.env.example` в `.env`
2. У `.env` вказує реальні креденшали БД (логін, пароль)
3. Файл `.env` **НЕ комітиться в Git** (додано в .gitignore)
4. `config/database.php` автоматично зчитує змінні з `.env`
5. Якщо `.env` немає — використовуються дефолтні значення (localhost, root, без пароля)

**Безпека:**
- Паролі БД не зберігаються в коді
- Локальні налаштування не потрапляють на Git
- Легко змінювати креденшали без редагування коду

---

### ✅ 2. Apache конфігурація (.htaccess)

**Створено файл:** `.htaccess` (~160 рядків)

**Реалізовані функції:**

#### 🔒 Безпека
- **Security Headers:** 
  - `X-Content-Type-Options: nosniff` — захист від MIME-sniffing
  - `X-Frame-Options: DENY` — захист від clickjacking
  - `X-XSS-Protection: 1; mode=block` — додатковий XSS захист
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Content-Security-Policy` — дозволені джерела контенту
  - `Permissions-Policy` — блокування доступу до геолокації, мікрофона, камери

- **Захист файлів та директорій:**
  - Вимкнено листинг директорій (`Options -Indexes`)
  - Заборонено доступ до `.env`, `.gitignore`, `README*.md`, `composer.json`
  - Заборонено доступ до папки `config/`
  - Заборонено доступ до SQL файлів у папці `database/`
  - Заборонено доступ до backup файлів (`.bak`, `.backup`, `.old`, `.tmp`)

- **PHP налаштування:**
  - `expose_php Off` — приховує версію PHP
  - `display_errors Off` — не показує помилки користувачам (лише в логи)
  - Session безпека: `httponly=1`, `samesite=Strict`

#### ⚡ Продуктивність
- **GZIP стиснення:** HTML, CSS, JS, XML, JSON (через `mod_deflate`)
- **Browser Caching:** Кешування статичних файлів
  - Зображення: 1 рік
  - Шрифти: 1 рік
  - CSS/JS: 1 місяць
  - HTML: без кешування (динамічний контент)

#### 🔗 Clean URLs (готові до використання)
- Видалення trailing slashes
- Редірект www → non-www (закоментовано, можна увімкнути)
- Force HTTPS (закоментовано, увімкнути після встановлення SSL)

---

### ✅ 3. Повний SQL експорт

**Створено файл:** `database/full_database.sql` (~200 рядків)

**Вміст:**
- Створення бази даних `phpp_portfolio` (якщо не існує)
- Видалення старих таблиць (з урахуванням FK constraints)
- Створення всіх таблиць:
  - `users` (2 тестові користувачі)
  - `categories` (6 категорій)
  - `automobiles` (7 автомобілів)
  - `comments` (7 коментарів)
- Тестові дані з реалістичним контентом
- Verification запити для перевірки імпорту

**Переваги:**
- Один файл для розгортання всієї БД
- Коректна послідовність створення з FK
- Тестові дані для негайної роботи
- Можна імпортувати через phpMyAdmin або CLI

---

### ✅ 4. Deployment Guide (інструкція розгортання)

Цей README містить **крок-за-кроком інструкції** для розгортання на різних типах хостингу.

---

## Інструкція розгортання на виробничий хостинг

### Варіант A: Хостинг із cPanel (zzz.com.ua, Hostinger, Bluehost)

#### Крок 1: Підготовка файлів
1. Завантажте всі файли проєкту (крім `.git`, `.env`, `.vscode`)
2. Переконайтесь, що `.htaccess` включено у завантаження

#### Крок 2: Завантаження через FTP
1. Завантажте FTP-клієнт (FileZilla рекомендується)
2. Підключіться до хостингу:
   - **Host:** ftp.yoursite.com (дізнайтесь у провайдера)
   - **Username:** ваш FTP логін
   - **Password:** ваш FTP пароль
   - **Port:** 21 (або 22 для SFTP)

3. Завантажте файли в папку `public_html` або `www`:
   ```
   public_html/
   ├── index.php
   ├── .htaccess
   ├── .env.example
   ├── config/
   ├── layout/
   ├── css/
   ├── js/
   ├── img/
   └── database/ (опціонально, для резервних копій)
   ```

#### Крок 3: Налаштування бази даних
1. Увійдіть у **cPanel**
2. Перейдіть до **MySQL Databases** або **phpMyAdmin**
3. Створіть нову базу даних:
   - Назва: `youruser_portfolio` (хостинг часто додає префікс)
4. Створіть користувача БД:
   - Логін: `youruser_admin`
   - Пароль: **використайте генератор паролів (мінімум 16 символів)**
5. Надайте користувачу **всі привілеї** на базу даних

#### Крок 4: Імпорт SQL
1. Відкрийте **phpMyAdmin**
2. Виберіть створену базу даних
3. Перейдіть на вкладку **Import**
4. Завантажте файл `database/full_database.sql`
5. Натисніть **Go**
6. Перевірте, що створилися 4 таблиці: users, categories, automobiles, comments

#### Крок 5: Налаштування .env
1. Через FTP або File Manager створіть файл `.env` у кореневій папці
2. Скопіюйте вміст із `.env.example`
3. Змініть значення:
   ```
   DB_HOST=localhost
   DB_NAME=youruser_portfolio
   DB_USER=youruser_admin
   DB_PASS=your_generated_password
   DB_CHARSET=utf8mb4
   ```
4. Збережіть файл (переконайтесь, що він `.env`, а не `.env.txt`)

#### Крок 6: Налаштування прав доступу (Permissions)
Через FTP або File Manager встановіть права:
- Папки: `755`
- PHP файли: `644`
- `.htaccess`: `644`
- `.env`: `600` (якщо дозволяє хостинг)

#### Крок 7: Увімкнення HTTPS (SSL)
1. У cPanel перейдіть до **SSL/TLS** або **Let's Encrypt**
2. Оберіть ваш домен та увімкніть SSL сертифікат
3. Після встановлення SSL відкрийте `.htaccess`
4. Розкоментуйте (видаліть `#`) рядки Force HTTPS:
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
   ```

#### Крок 8: Тестування
1. Відкрийте сайт у браузері: `https://yoursite.com`
2. Перевірте:
   - ✅ Головна сторінка завантажується
   - ✅ Вхід працює (admin / Password123)
   - ✅ Список автомобілів відображається
   - ✅ Створення автомобіля працює
   - ✅ Коментарі додаються
   - ✅ Категорії відображаються

#### Крок 9: Зміна тестових паролів (ОБОВ'ЯЗКОВО!)
1. Увійдіть як `admin` / `Password123`
2. Перейдіть у профіль
3. Змініть пароль на сильний
4. Видаліть тестового користувача `user1` (або змініть пароль)

---

### Варіант B: VPS / Dedicated Server (Ubuntu/Debian)

#### Крок 1: Підключення до сервера
```bash
ssh root@your-server-ip
```

#### Крок 2: Встановлення LAMP stack
```bash
# Оновлення пакетів
apt update && apt upgrade -y

# Встановлення Apache
apt install apache2 -y
systemctl enable apache2
systemctl start apache2

# Встановлення MySQL
apt install mysql-server -y
mysql_secure_installation

# Встановлення PHP 8.1+
apt install php php-mysql php-mbstring php-xml php-curl libapache2-mod-php -y

# Перевірка версії PHP
php -v
```

#### Крок 3: Налаштування Apache
```bash
# Увімкнути mod_rewrite
a2enmod rewrite
a2enmod headers
a2enmod expires
a2enmod deflate

# Створити віртуальний хост
nano /etc/apache2/sites-available/portfolio.conf
```

Вставте конфігурацію:
```apache
<VirtualHost *:80>
    ServerName yoursite.com
    ServerAlias www.yoursite.com
    DocumentRoot /var/www/portfolio
    
    <Directory /var/www/portfolio>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/portfolio_error.log
    CustomLog ${APACHE_LOG_DIR}/portfolio_access.log combined
</VirtualHost>
```

```bash
# Увімкнути сайт
a2ensite portfolio.conf
a2dissite 000-default.conf
systemctl reload apache2
```

#### Крок 4: Завантаження файлів
```bash
# Створити директорію
mkdir -p /var/www/portfolio

# Завантажити через Git
cd /var/www/portfolio
git clone https://github.com/kitsulserhii-creator/PHP.git .
git checkout lab-8-deployment

# Або через SCP з локального ПК:
# scp -r /path/to/project/* root@server-ip:/var/www/portfolio/
```

#### Крок 5: Налаштування прав
```bash
chown -R www-data:www-data /var/www/portfolio
chmod -R 755 /var/www/portfolio
chmod 600 /var/www/portfolio/.env
```

#### Крок 6: Створення БД
```bash
mysql -u root -p
```

У MySQL консолі:
```sql
CREATE DATABASE phpp_portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON phpp_portfolio.* TO 'portfolio_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Імпорт SQL:
```bash
mysql -u portfolio_user -p phpp_portfolio < /var/www/portfolio/database/full_database.sql
```

#### Крок 7: Налаштування .env
```bash
cd /var/www/portfolio
cp .env.example .env
nano .env
```

Змініть:
```
DB_HOST=localhost
DB_NAME=phpp_portfolio
DB_USER=portfolio_user
DB_PASS=strong_password_here
DB_CHARSET=utf8mb4
```

#### Крок 8: Налаштування SSL (Let's Encrypt)
```bash
# Встановити Certbot
apt install certbot python3-certbot-apache -y

# Отримати SSL сертифікат
certbot --apache -d yoursite.com -d www.yoursite.com

# Автоматичне оновлення
certbot renew --dry-run
```

#### Крок 9: Фінальна перевірка
```bash
# Перевірити Apache конфігурацію
apachectl configtest

# Перезавантажити Apache
systemctl restart apache2

# Перевірити логи
tail -f /var/log/apache2/portfolio_error.log
```

Відкрийте `https://yoursite.com` та протестуйте функціональність.

---

## Checklist розгортання

Використовуйте цей checklist для перевірки:

### Перед розгортанням
- [ ] Код протестовано локально
- [ ] Всі файли закомічено в Git
- [ ] `.env` додано в `.gitignore`
- [ ] Тестові паролі документовано

### Хостинг
- [ ] База даних створена
- [ ] Користувач БД створений з сильним паролем
- [ ] SQL імпортовано без помилок
- [ ] Файли завантажено через FTP/Git
- [ ] `.env` файл створено та налаштовано
- [ ] Права доступу встановлено коректно

### Безпека
- [ ] SSL сертифікат встановлено
- [ ] HTTPS редірект увімкнено в `.htaccess`
- [ ] Тестові паролі змінено на production
- [ ] `display_errors` вимкнено
- [ ] Security headers працюють (перевірка: securityheaders.com)
- [ ] Доступ до `.env`, `config/`, SQL файлів заблоковано

### Функціональність
- [ ] Головна сторінка відкривається
- [ ] Логін/реєстрація працює
- [ ] Список автомобілів відображається
- [ ] CRUD автомобілів працює
- [ ] Коментарі додаються
- [ ] Категорії відображаються
- [ ] Адміністративні функції працюють
- [ ] CSS та JS завантажуються

### Продуктивність
- [ ] GZIP стиснення працює
- [ ] Browser caching налаштовано
- [ ] Зображення оптимізовано (якщо є)
- [ ] Час завантаження сторінки <3 секунд

---

## Production vs Development: ключові відмінності

| Параметр | Development (localhost) | Production (hosting) |
|----------|-------------------------|----------------------|
| **DB Host** | localhost | localhost (або IP) |
| **DB User** | root | створений користувач |
| **DB Password** | порожній | сильний пароль |
| **display_errors** | On | **Off** |
| **error_reporting** | E_ALL | E_ALL (в логи) |
| **HTTPS** | HTTP (опціонально) | **HTTPS (обов'язково)** |
| **.htaccess** | мінімальний | повний захист |
| **Session cookies** | httponly | **httponly + secure** |
| **Security headers** | опціонально | **обов'язково** |
| **Backup** | не потрібен | регулярні автобекапи |

---

## Рекомендації з безпеки

### 🔒 Обов'язкові заходи
1. **Ніколи не використовуйте дефолтні паролі** (admin/Password123) на production
2. **Завжди використовуйте HTTPS** — HTTP передає паролі відкритим текстом
3. **Тримайте .env поза Git** — .gitignore створено саме для цього
4. **Регулярно оновлюйте PHP** — старі версії мають вразливості
5. **Робіть бекапи БД щодня** — через cron або хостинг інструменти

### 🛡️ Додаткові заходи
- Використовуйте **fail2ban** на VPS для блокування brute-force атак
- Налаштуйте **rate limiting** для login endpoint (можна через .htaccess)
- Увімкніть **automatic updates** для критичних патчів безпеки
- Використовуйте **Web Application Firewall** (CloudFlare, Sucuri)
- Додайте **CAPTCHA** на форми login/registration (Google reCAPTCHA)

---

## Моніторинг та підтримка

### Логи
- **Apache error log:** `/var/log/apache2/error.log` (VPS) або cPanel Error Log (shared)
- **PHP error log:** налаштовується в `php.ini` або `.htaccess`
- **Application logs:** можна додати custom logger у `config/logger.php`

### Моніторинг uptime
- **UptimeRobot** (безкоштовно) — перевіряє доступність сайту кожні 5 хвилин
- **Google Analytics** — відстежує відвідувачів
- **Server monitoring:** Zabbix, Prometheus + Grafana (для VPS)

### Backup strategy
- **Automatic daily backups** через cPanel або VPS cron
- **Git pushes** = code backup (не містить БД!)
- **SQL exports** щотижня через phpMyAdmin
- **Off-site backups** на Google Drive / Dropbox

---

## Troubleshooting (вирішення проблем)

### Проблема: "500 Internal Server Error"
**Причини:**
- Синтаксична помилка в `.htaccess`
- PHP помилка в коді
- Права доступу некоректні

**Рішення:**
1. Перевірте `error_log` Apache
2. Тимчасово увімкніть `display_errors` у `.htaccess`:
   ```apache
   php_flag display_errors On
   ```
3. Перевірте права: папки `755`, файли `644`

---

### Проблема: "Database connection failed"
**Причини:**
- Невірні креденшали в `.env`
- Користувач БД не має прав
- MySQL сервер не запущено

**Рішення:**
1. Перевірте `.env` файл:
   ```bash
   cat /var/www/portfolio/.env
   ```
2. Спробуйте підключитись через CLI:
   ```bash
   mysql -u portfolio_user -p phpp_portfolio
   ```
3. Перевірте статус MySQL:
   ```bash
   systemctl status mysql
   ```

---

### Проблема: CSS/JS не завантажуються
**Причини:**
- Шляхи до файлів некоректні
- `.htaccess` блокує доступ
- MIME types не налаштовані

**Рішення:**
1. Перевірте в браузері (F12) → Network tab → подивіться 404 помилки
2. Перевірте шляхи в `layout/header.php`:
   ```php
   <link rel="stylesheet" href="css/style.css">
   ```
3. Переконайтесь, що `.htaccess` не блокує статичні файли

---

### Проблема: "Session не працює"
**Причини:**
- Папка `/tmp` заповнена або немає прав
- `session.save_path` некоректний

**Рішення:**
1. Перевірте `php.ini`:
   ```ini
   session.save_path = "/var/lib/php/sessions"
   ```
2. Створіть папку та встановіть права:
   ```bash
   mkdir -p /var/lib/php/sessions
   chown www-data:www-data /var/lib/php/sessions
   chmod 700 /var/lib/php/sessions
   ```

---

## Оцінювання Lab 8

| Критерій | Опис | Бали |
|----------|------|------|
| **Конфігурація середовищ** | .env файл, gitignore, config/database.php підтримка | 2 |
| **.htaccess налаштування** | Security headers, clean URLs, GZIP, caching | 3 |
| **SQL експорт** | Повний дамп БД з даними, готовий до імпорту | 1 |
| **Deployment guide** | Інструкції cPanel, VPS, troubleshooting | 3 |
| **Документація** | README з checklist, security recommendations | 1 |
| **РАЗОМ** | | **10** |

---

## Висновок

Lab 8 завершує підготовку проєкту до production використання. Всі критичні аспекти розгортання враховано:
- ✅ Безпека (headers, HTTPS, env variables)
- ✅ Продуктивність (caching, GZIP)
- ✅ Зручність (one-click SQL import, env configuration)
- ✅ Документація (step-by-step guides, troubleshooting)

Проєкт готовий до розгортання на будь-якому хостингу з підтримкою PHP 8.1+ та MySQL 5.7+.

**Дата виконання:** 2024  
**Гілка:** `lab-8-deployment`
