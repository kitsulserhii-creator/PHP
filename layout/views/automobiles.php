<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// Display success message if any
$successMessage = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

// Fetch automobiles from database
try {
    if (!empty($_SESSION['user_admin'])) {
        // Admin sees all automobiles
        $automobiles = dbFetchAll(
            "SELECT a.*, u.login as author_name, c.name as category_name
             FROM automobiles a 
             JOIN users u ON a.author_id = u.id 
             LEFT JOIN categories c ON a.category_id = c.id
             ORDER BY a.date DESC"
        );
    } else {
        // Regular users and guests see only published automobiles
        $automobiles = dbFetchAll(
            "SELECT a.*, u.login as author_name, c.name as category_name
             FROM automobiles a 
             JOIN users u ON a.author_id = u.id 
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.visible = 1 
             ORDER BY a.date DESC"
        );
    }
    
    // Fetch all categories for sidebar
    $categories = dbFetchAll("SELECT id, name FROM categories ORDER BY name");
} catch (PDOException $e) {
    error_log('Fetch automobiles error: ' . $e->getMessage());
    $automobiles = [];
    $categories = [];
}
?>

<main class="content">
    <!-- Categories sidebar -->
    <?php if (!empty($categories)): ?>
        <div class="categories-sidebar">
            <h3><i class="fas fa-tags"></i> Категорії</h3>
            <ul class="categories-list-sidebar">
                <li><a href="index.php?action=automobiles" class="category-link-all"><i class="fas fa-th"></i> Всі категорії</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="index.php?action=category_automobiles&category_id=<?=(int)$cat['id']?>">
                            <i class="fas fa-tag"></i> <?=htmlspecialchars($cat['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="automobiles-main-content">
    <div class="content-header">
        <h2>Автомобілі</h2>
        <?php if (!empty($_SESSION['user_logged'])): ?>
            <a href="index.php?action=create_automobile" class="btn-primary">
                <i class="fas fa-plus"></i> Додати автомобіль
            </a>
        <?php endif; ?>
    </div>
    
    <?php if ($successMessage): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> <?=htmlspecialchars($successMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($automobiles)): ?>
        <div class="info-card">
            <p><i class="fas fa-info-circle"></i> Наразі немає автомобілів для відображення.</p>
            <?php if (!empty($_SESSION['user_logged'])): ?>
                <p>Будьте першим, хто додасть автомобіль!</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="automobiles-grid">
            <?php foreach ($automobiles as $auto): ?>
                <div class="automobile-card <?= $auto['visible'] ? '' : 'unpublished' ?>">
                    <?php if (!$auto['visible']): ?>
                        <div class="unpublished-badge">
                            <i class="fas fa-eye-slash"></i> Не опубліковано
                        </div>
                    <?php endif; ?>
                    
                    <div class="auto-header">
                        <h3><?=htmlspecialchars($auto['brand'] . ' ' . $auto['model'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></h3>
                        <span class="auto-year"><?=htmlspecialchars((string)$auto['year'], ENT_QUOTES, 'UTF-8')?></span>
                    </div>
                    
                    <div class="auto-details">
                        <?php if ($auto['category_name']): ?>
                            <p>
                                <i class="fas fa-tag"></i> <strong>Категорія:</strong> 
                                <a href="index.php?action=category_automobiles&category_id=<?=(int)$auto['category_id']?>" class="category-link">
                                    <?=htmlspecialchars($auto['category_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>
                                </a>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($auto['color']): ?>
                            <p><i class="fas fa-palette"></i> <strong>Колір:</strong> <?=htmlspecialchars($auto['color'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></p>
                        <?php endif; ?>
                        
                        <?php if ($auto['price']): ?>
                            <p><i class="fas fa-dollar-sign"></i> <strong>Ціна:</strong> $<?=number_format((float)$auto['price'], 2)?></p>
                        <?php endif; ?>
                        
                        <?php if ($auto['mileage']): ?>
                            <p><i class="fas fa-tachometer-alt"></i> <strong>Пробіг:</strong> <?=number_format((int)$auto['mileage'])?> км</p>
                        <?php endif; ?>
                        
                        <p><i class="fas fa-user"></i> <strong>Автор:</strong> <?=htmlspecialchars($auto['author_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></p>
                        <p><i class="fas fa-calendar"></i> <strong>Додано:</strong> <?=htmlspecialchars($auto['date'], ENT_QUOTES, 'UTF-8')?></p>
                    </div>
                    
                    <?php if ($auto['description']): ?>
                        <div class="auto-description">
                            <?=htmlspecialchars(mb_substr($auto['description'], 0, 150), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>
                            <?= mb_strlen($auto['description']) > 150 ? '...' : '' ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="auto-actions">
                        <a href="index.php?action=view_automobile&id=<?=(int)$auto['id']?>" class="btn-view">
                            <i class="fas fa-eye"></i> Переглянути
                        </a>
                        
                        <?php if (!empty($_SESSION['user_admin'])): ?>
                            <a href="index.php?action=update_automobile&id=<?=(int)$auto['id']?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Редагувати
                            </a>
                            <a href="index.php?action=delete_automobile&id=<?=(int)$auto['id']?>" 
                               class="btn-delete"
                               onclick="return confirm('Ви впевнені, що хочете видалити <?=htmlspecialchars($auto['brand'] . ' ' . $auto['model'], ENT_QUOTES, 'UTF-8')?>?');">
                                <i class="fas fa-trash"></i> Видалити
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="automobiles-stats">
            <p>Всього автомобілів: <strong><?=count($automobiles)?></strong></p>
            <?php if (!empty($_SESSION['user_admin'])): ?>
                <?php
                    $published = count(array_filter($automobiles, fn($a) => $a['visible']));
                    $unpublished = count($automobiles) - $published;
                ?>
                <p>Опубліковано: <strong><?=$published?></strong> | Не опубліковано: <strong><?=$unpublished?></strong></p>
            <?php endif; ?>
        </div>
    </div> <!-- .automobiles-main-content -->
</main>
