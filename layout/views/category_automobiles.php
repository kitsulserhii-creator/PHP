<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// Get and validate category ID
$categoryId = (int)($_GET['category_id'] ?? 0);

if ($categoryId <= 0) {
    $category = null;
    $errorMessage = 'Невірний ID категорії.';
} else {
    try {
        // Fetch category info
        $category = dbFetchOne(
            "SELECT * FROM categories WHERE id = ? LIMIT 1",
            [$categoryId]
        );
        
        if (!$category) {
            $errorMessage = 'Категорію не знайдено.';
        } else {
            $errorMessage = null;
            
            // Fetch automobiles in this category
            if (!empty($_SESSION['user_admin'])) {
                // Admin sees all
                $automobiles = dbFetchAll(
                    "SELECT a.*, u.login as author_name 
                     FROM automobiles a 
                     JOIN users u ON a.author_id = u.id 
                     WHERE a.category_id = ?
                     ORDER BY a.date DESC",
                    [$categoryId]
                );
            } else {
                // Regular users see only published
                $automobiles = dbFetchAll(
                    "SELECT a.*, u.login as author_name 
                     FROM automobiles a 
                     JOIN users u ON a.author_id = u.id 
                     WHERE a.category_id = ? AND a.visible = 1
                     ORDER BY a.date DESC",
                    [$categoryId]
                );
            }
        }
    } catch (PDOException $e) {
        error_log('Fetch category automobiles error: ' . $e->getMessage());
        $category = null;
        $errorMessage = 'Помилка при завантаженні даних.';
    }
}
?>

<main class="content">
    <?php if ($category): ?>
        <div class="category-header">
            <h2><i class="fas fa-tag"></i> <?=htmlspecialchars($category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></h2>
            <?php if ($category['description']): ?>
                <p class="category-description"><?=htmlspecialchars($category['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></p>
            <?php endif; ?>
        </div>
        
        <?php if (empty($automobiles)): ?>
            <div class="info-card">
                <p><i class="fas fa-info-circle"></i> В цій категорії поки що немає автомобілів.</p>
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
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="automobiles-stats">
                <p>Всього автомобілів: <strong><?=count($automobiles)?></strong></p>
            </div>
        <?php endif; ?>
        
        <div class="form-actions" style="margin-top: 20px;">
            <a href="index.php?action=automobiles" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Всі автомобілі
            </a>
        </div>
    <?php else: ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>Помилка</h2>
            <p><?=htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></p>
            <a href="index.php?action=automobiles" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Всі автомобілі
            </a>
        </div>
    <?php endif; ?>
</main>
