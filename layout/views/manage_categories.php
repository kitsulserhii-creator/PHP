<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// Only admin can manage categories
if (empty($_SESSION['user_admin'])) {
    header('Location: index.php?action=automobiles');
    exit;
}

$errors = [];
$successMessage = null;

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Невалідний запит.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($name === '') {
            $errors[] = 'Назва категорії обов\'язкова.';
        } elseif (mb_strlen($name) > 100) {
            $errors[] = 'Назва не повинна перевищувати 100 символів.';
        } else {
            try {
                dbQuery(
                    "INSERT INTO categories (name, description) VALUES (?, ?)",
                    [$name, $description !== '' ? $description : null]
                );
                $successMessage = 'Категорію успішно додано!';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $errors[] = 'Категорія з такою назвою вже існує.';
                } else {
                    error_log('Add category error: ' . $e->getMessage());
                    $errors[] = 'Помилка при додаванні категорії.';
                }
            }
        }
    }
}

// Handle category deletion
if (isset($_GET['delete']) && $_GET['delete'] !== '') {
    $catId = (int)$_GET['delete'];
    try {
        dbQuery("DELETE FROM categories WHERE id = ?", [$catId]);
        $successMessage = 'Категорію успішно видалено!';
    } catch (PDOException $e) {
        error_log('Delete category error: ' . $e->getMessage());
        $errors[] = 'Помилка при видаленні категорії.';
    }
}

// Fetch all categories
try {
    $categories = dbFetchAll("SELECT * FROM categories ORDER BY name");
} catch (PDOException $e) {
    error_log('Fetch categories error: ' . $e->getMessage());
    $categories = [];
}
?>

<main class="content">
    <h2>Управління категоріями автомобілів</h2>
    
    <?php if ($successMessage): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> <?=htmlspecialchars($successMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?=htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="categories-management">
        <!-- Add category form -->
        <div class="add-category-form">
            <h3>Додати нову категорію</h3>
            <form method="post" action="index.php?action=manage_categories">
                <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8')?>">
                <input type="hidden" name="add_category" value="1">
                
                <div class="form-field">
                    <label for="name">Назва категорії <span class="required">*</span></label>
                    <input id="name" name="name" type="text" required>
                </div>
                
                <div class="form-field">
                    <label for="description">Опис (опціонально)</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus"></i> Додати категорію
                </button>
            </form>
        </div>
        
        <!-- Categories list -->
        <div class="categories-list">
            <h3>Існуючі категорії (<?=count($categories)?>)</h3>
            
            <?php if (empty($categories)): ?>
                <div class="info-message">
                    <i class="fas fa-info-circle"></i> Категорій поки що немає.
                </div>
            <?php else: ?>
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Назва</th>
                            <th>Опис</th>
                            <th>Автомобілів</th>
                            <th>Дата створення</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <?php
                                // Count automobiles in this category
                                $count = dbFetchOne(
                                    "SELECT COUNT(*) as cnt FROM automobiles WHERE category_id = ?",
                                    [$cat['id']]
                                );
                                $autoCount = (int)($count['cnt'] ?? 0);
                            ?>
                            <tr>
                                <td>#<?=(int)$cat['id']?></td>
                                <td><strong><?=htmlspecialchars($cat['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></strong></td>
                                <td><?=htmlspecialchars($cat['description'] ?? '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></td>
                                <td><?=$autoCount?></td>
                                <td><?=htmlspecialchars($cat['created_at'], ENT_QUOTES, 'UTF-8')?></td>
                                <td>
                                    <a href="index.php?action=manage_categories&delete=<?=(int)$cat['id']?>" 
                                       class="btn-delete-small"
                                       onclick="return confirm('Видалити категорію <?=htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8')?>?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="form-actions" style="margin-top: 30px;">
        <a href="index.php?action=automobiles" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Назад до автомобілів
        </a>
    </div>
</main>
