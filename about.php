<?php
// Підключення заголовка сторінки
include 'header.php';

// Підключення бокового меню
include 'left_menu.php';

// Отримуємо поточний режим
$mode = $_SESSION['mode'];
$isRealMe = ($mode === 'realme');
?>

        <main class="content">
            <?php if ($isRealMe): ?>
                <!-- Real Me режим -->
                <h2>🎮 Хто я насправді?</h2>
                
                <div class="info-card">
                    <h3>💫 Привіт!</h3>
                    <p>Я звичайний студент, який любить:</p>
                    <p><strong>🎵 Музику:</strong> Слухаю все від класики до електроніки. Найбільше подобається <em>[твій улюблений виконавець]</em></p>
                    <p><strong>🎮 Ігри:</strong> CS:GO, Dota 2, та інколи щось одиночне для душі</p>
                    <p><strong>🍕 Їжу:</strong> Піца о 3 ночі - це мій vibe</p>
                </div>

                <div class="info-card">
                    <h3>🤔 Чому я тут?</h3>
                    <p>Бо треба здати лабу по PHP, але вирішив зробити це з приколом! 😄</p>
                    <p>Життя занадто коротке для нудних портфоліо. Тому я зробив цей перемикач - 
                    щоб показати, що я не лише про код і робочі речі.</p>
                </div>

                <div class="info-card">
                    <h3>🎯 Мої справжні цілі:</h3>
                    <p>✨ Зробити щось крутe, не як у всіх</p>
                    <p>🚀 Вивчити нові технології (коли є настрій)</p>
                    <p>☕ Пити каву і кодити під lofi</p>
                    <p>🎨 Створювати проєкти, які приносять радість</p>
                </div>

                <div class="info-card">
                    <h3>💭 Цитата дня:</h3>
                    <p><em>"Code is like humor. When you have to explain it, it's bad."</em> 😏</p>
                </div>

            <?php else: ?>
                <!-- Professional режим -->
                <h2>📋 Про мене</h2>
                
                <div class="info-card">
                    <h3>Освіта</h3>
                    <p><strong>Університет:</strong> [Твій університет]</p>
                    <p><strong>Спеціальність:</strong> Комп'ютерні науки / Інформаційні технології</p>
                    <p><strong>Курс:</strong> [Твій курс]</p>
                </div>

                <div class="info-card">
                    <h3>Технічні навички</h3>
                    <p><strong>Мови програмування:</strong> PHP, JavaScript, HTML/CSS, Python</p>
                    <p><strong>Фреймворки:</strong> Laravel, React, Bootstrap</p>
                    <p><strong>Бази даних:</strong> MySQL, PostgreSQL</p>
                    <p><strong>Інструменти:</strong> Git, Docker, VS Code</p>
                </div>

                <div class="info-card">
                    <h3>Досвід роботи</h3>
                    <p><strong>Full-stack розробник</strong> (стажування)</p>
                    <p>Розробка веб-додатків з використанням PHP та JavaScript. 
                    Участь у командних проєктах, code review, agile методології.</p>
                </div>

                <div class="info-card">
                    <h3>Проєкти</h3>
                    <p><strong>• E-commerce платформа:</strong> Повнофункціональний інтернет-магазин з системою оплати</p>
                    <p><strong>• Блог-платформа:</strong> CMS для створення та управління контентом</p>
                    <p><strong>• API сервіс:</strong> RESTful API для мобільних додатків</p>
                </div>

                <div class="info-card">
                    <h3>Контакти</h3>
                    <p><strong>Email:</strong> your.email@example.com</p>
                    <p><strong>GitHub:</strong> github.com/yourusername</p>
                    <p><strong>LinkedIn:</strong> linkedin.com/in/yourprofile</p>
                </div>

            <?php endif; ?>
        </main>

<?php
// Підключення футера сторінки
include 'footer.php';
?>
