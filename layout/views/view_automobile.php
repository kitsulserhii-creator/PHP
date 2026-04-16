<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// Get and validate ID
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $automobile = null;
    $errorMessage = 'Невірний ID автомобіля.';
} else {
    try {
        // Fetch automobile from database
        $automobile = dbFetchOne(
            "SELECT a.*, u.login as author_name, u.id as author_user_id 
             FROM automobiles a 
             JOIN users u ON a.author_id = u.id 
             WHERE a.id = ? 
             LIMIT 1",
            [$id]
        );
        
        if (!$automobile) {
            $errorMessage = 'Автомобіль з таким ID не знайдено.';
        } elseif (!$automobile['visible'] && empty($_SESSION['user_admin'])) {
            // Non-admin users cannot view unpublished automobiles
            $automobile = null;
            $errorMessage = 'Цей автомобіль ще не опублікований.';
        } else {
            $errorMessage = null;
        }
    } catch (PDOException $e) {
        error_log('View automobile error: ' . $e->getMessage());
        $automobile = null;
        $errorMessage = 'Помилка при завантаженні даних автомобіля.';
    }
}
?>

<main class="content">
    <?php if ($automobile): ?>
        <div class="automobile-detail">
            <div class="detail-header">
                <h2><?=htmlspecialchars($automobile['brand'] . ' ' . $automobile['model'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></h2>
                <?php if (!$automobile['visible']): ?>
                    <span class="badge-unpublished">
                        <i class="fas fa-eye-slash"></i> Не опубліковано
                    </span>
                <?php else: ?>
                    <span class="badge-published">
                        <i class="fas fa-check-circle"></i> Опубліковано
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="detail-content">
                <div class="detail-section">
                    <h3><i class="fas fa-info-circle"></i> Основна інформація</h3>
                    <table class="detail-table">
                        <tr>
                            <th>Марка:</th>
                            <td><?=htmlspecialchars($automobile['brand'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></td>
                        </tr>
                        <tr>
                            <th>Модель:</th>
                            <td><?=htmlspecialchars($automobile['model'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></td>
                        </tr>
                        <tr>
                            <th>Рік випуску:</th>
                            <td><?=htmlspecialchars((string)$automobile['year'], ENT_QUOTES, 'UTF-8')?></td>
                        </tr>
                        <?php if ($automobile['color']): ?>
                        <tr>
                            <th>Колір:</th>
                            <td><?=htmlspecialchars($automobile['color'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($automobile['price']): ?>
                        <tr>
                            <th>Ціна:</th>
                            <td class="price-large">$<?=number_format((float)$automobile['price'], 2)?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($automobile['mileage']): ?>
                        <tr>
                            <th>Пробіг:</th>
                            <td><?=number_format((int)$automobile['mileage'])?> км</td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <?php if ($automobile['description']): ?>
                <div class="detail-section">
                    <h3><i class="fas fa-file-alt"></i> Опис</h3>
                    <div class="description-text">
                        <?=nl2br(htmlspecialchars($automobile['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="detail-section">
                    <h3><i class="fas fa-info"></i> Метаінформація</h3>
                    <table class="detail-table">
                        <tr>
                            <th>Автор:</th>
                            <td><?=htmlspecialchars($automobile['author_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></td>
                        </tr>
                        <tr>
                            <th>Дата додавання:</th>
                            <td><?=htmlspecialchars($automobile['date'], ENT_QUOTES, 'UTF-8')?></td>
                        </tr>
                        <tr>
                            <th>ID запису:</th>
                            <td>#<?=(int)$automobile['id']?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="detail-actions">
                <a href="index.php?action=automobiles" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Повернутись до списку
                </a>
                
                <?php if (!empty($_SESSION['user_admin'])): ?>
                    <a href="index.php?action=update_automobile&id=<?=(int)$automobile['id']?>" class="btn-primary">
                        <i class="fas fa-edit"></i> Редагувати
                    </a>
                    <a href="index.php?action=delete_automobile&id=<?=(int)$automobile['id']?>" 
                       class="btn-danger"
                       onclick="return confirm('Ви впевнені, що хочете видалити цей автомобіль?');">
                        <i class="fas fa-trash"></i> Видалити
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>Помилка</h2>
            <p><?=htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></p>
            <a href="index.php?action=automobiles" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Повернутись до списку
            </a>
        </div>
    <?php endif; ?>
</main>
