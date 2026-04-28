    <div class="container">
        <nav class="left-menu">
            <h2><?php echo $isRealMe ? 'Меню' : 'Навігація'; ?></h2>
            <ul>
                <li><a href="index.php?action=main"><i class="fas fa-home"></i> Головна</a></li>
                <li><a href="index.php?action=about"><i class="fas fa-info-circle"></i> Про сайт</a></li>
                <li><a href="index.php?action=automobiles"><i class="fas fa-car"></i> Автомобілі</a></li>
                
                <?php if (!empty($_SESSION['user_logged'])): ?>
                    <!-- Меню для авторизованих користувачів -->
                    <li style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.15);"></li>
                    <li><a href="index.php?action=profile"><i class="fas fa-user"></i> Профіль</a></li>
                    <li><a href="index.php?action=create_automobile"><i class="fas fa-plus-circle"></i> Додати авто</a></li>
                    
                    <?php if (!empty($_SESSION['user_admin'])): ?>
                        <!-- Меню тільки для адміністраторів -->
                        <li style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.2);">
                            <strong style="color: rgba(255,255,255,0.7); font-size: 0.85em; padding-left: 12px;">АДМІН</strong>
                        </li>
                    <?php endif; ?>
                    
                    <li><a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Вийти</a></li>
                <?php else: ?>
                    <!-- Меню для гостей -->
                    <li><a href="index.php?action=registration"><i class="fas fa-user-plus"></i> Реєстрація</a></li>
                    <li><a href="index.php?action=login"><i class="fas fa-sign-in-alt"></i> Увійти</a></li>
                <?php endif; ?>
            </ul>
        </nav>