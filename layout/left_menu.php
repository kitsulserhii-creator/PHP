    <div class="container">
        <nav class="left-menu">
            <h2><?php echo $isRealMe ? 'Меню' : 'Навігація'; ?></h2>
            <ul>
                <li><a href="index.php?action=main">Головна</a></li>
                <li><a href="index.php?action=about">Про сайт</a></li>
                <?php if (!empty($_SESSION['user_logged'])): ?>
                    <li><a href="index.php?action=profile">Профіль</a></li>
                    <li><a href="index.php?action=logout">Вийти</a></li>
                <?php else: ?>
                    <li><a href="index.php?action=registration">Реєстрація</a></li>
                    <li><a href="index.php?action=login">Увійти</a></li>
                <?php endif; ?>
            </ul>
        </nav>