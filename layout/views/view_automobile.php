<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// Get and validate ID
$id = (int)($_GET['id'] ?? 0);

// CSRF token for comment form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process comment submission
$commentErrors = [];
$commentSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    if (empty($_SESSION['user_logged']) || empty($_SESSION['user_id'])) {
        $commentErrors[] = 'Ви повинні увійти, щоб залишити коментар.';
    } else {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            $commentErrors[] = 'Невалідний запит.';
        } else {
            $commentText = trim($_POST['comment_text'] ?? '');
            
            if ($commentText === '') {
                $commentErrors[] = 'Коментар не може бути порожнім.';
            } elseif (mb_strlen($commentText) > 2000) {
                $commentErrors[] = 'Коментар не повинен перевищувати 2000 символів.';
            } else {
                try {
                    dbQuery(
                        "INSERT INTO comments (automobile_id, user_id, comment_text) VALUES (?, ?, ?)",
                        [$id, (int)$_SESSION['user_id'], $commentText]
                    );
                    $commentSuccess = true;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } catch (PDOException $e) {
                    error_log('Add comment error: ' . $e->getMessage());
                    $commentErrors[] = 'Помилка при додаванні коментаря.';
                }
            }
        }
    }
}

// Handle comment deletion (admin only)
if (isset($_GET['delete_comment']) && !empty($_SESSION['user_admin'])) {
    $commentId = (int)$_GET['delete_comment'];
    try {
        dbQuery("DELETE FROM comments WHERE id = ?", [$commentId]);
        header('Location: index.php?action=view_automobile&id=' . $id);
        exit;
    } catch (PDOException $e) {
        error_log('Delete comment error: ' . $e->getMessage());
    }
}

if ($id <= 0) {
    $automobile = null;
    $errorMessage = 'Невірний ID автомобіля.';
} else {
    try {
        // Fetch automobile from database with category
        $automobile = dbFetchOne(
            "SELECT a.*, u.login as author_name, u.id as author_user_id, c.name as category_name
             FROM automobiles a 
             JOIN users u ON a.author_id = u.id 
             LEFT JOIN categories c ON a.category_id = c.id
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
            
            // Fetch comments for this automobile (newest first)
            $comments = dbFetchAll(
                "SELECT c.*, u.login as user_login 
                 FROM comments c 
                 JOIN users u ON c.user_id = u.id 
                 WHERE c.automobile_id = ? 
                 ORDER BY c.created_at DESC",
                [$id]
            );
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
                        <?php if ($automobile['category_name']): ?>
                        <tr>
                            <th>Категорія:</th>
                            <td><span class="category-badge"><?=htmlspecialchars($automobile['category_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></span></td>
                        </tr>
                        <?php endif; ?>
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
            
            <!-- ======================================== -->
            <!-- COMMENTS SECTION - Lab 7 Variant 1 -->
            <!-- ======================================== -->
            
            <div class="comments-section">
                <h3><i class="fas fa-comments"></i> Коментарі (<?=count($comments ?? [])?>)</h3>
                
                <!-- Add comment form -->
                <?php if (!empty($_SESSION['user_logged'])): ?>
                    <div class="add-comment-form">
                        <h4>Додати коментар</h4>
                        
                        <?php if ($commentSuccess): ?>
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i> Коментар успішно додано!
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($commentErrors)): ?>
                            <div class="errors">
                                <ul>
                                    <?php foreach ($commentErrors as $err): ?>
                                        <li><?=htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="index.php?action=view_automobile&id=<?=$id?>">
                            <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8')?>">
                            <input type="hidden" name="add_comment" value="1">
                            
                            <textarea name="comment_text" rows="4" placeholder="Напишіть ваш коментар..." required></textarea>
                            
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-paper-plane"></i> Відправити коментар
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="info-message">
                        <i class="fas fa-info-circle"></i> 
                        <a href="index.php?action=login">Увійдіть</a>, щоб залишити коментар.
                    </div>
                <?php endif; ?>
                
                <!-- Display comments -->
                <?php if (empty($comments)): ?>
                    <div class="no-comments">
                        <i class="fas fa-comment-slash"></i>
                        <p>Коментарів поки що немає. Будьте першим!</p>
                    </div>
                <?php else: ?>
                    <div class="comments-list">
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <div class="comment-author">
                                        <i class="fas fa-user-circle"></i>
                                        <strong><?=htmlspecialchars($comment['user_login'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></strong>
                                    </div>
                                    <div class="comment-meta">
                                        <span class="comment-date">
                                            <i class="fas fa-clock"></i>
                                            <?=htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8')?>
                                        </span>
                                        <?php if (!empty($_SESSION['user_admin'])): ?>
                                            <a href="index.php?action=view_automobile&id=<?=$id?>&delete_comment=<?=(int)$comment['id']?>" 
                                               class="comment-delete"
                                               onclick="return confirm('Видалити цей коментар?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="comment-text">
                                    <?=nl2br(htmlspecialchars($comment['comment_text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
