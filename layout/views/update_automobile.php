<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// Only admin can update automobiles
if (empty($_SESSION['user_admin'])) {
    header('Location: index.php?action=automobiles');
    exit;
}

// Get and validate ID
$id = (int)($_GET['id'] ?? 0);

$errors = [];
$values = [
    'brand' => '', 'model' => '', 'year' => '', 'color' => '',
    'price' => '', 'mileage' => '', 'description' => '', 'visible' => '0', 'category_id' => ''
];

// Fetch categories for dropdown
try {
    $categories = dbFetchAll("SELECT id, name FROM categories ORDER BY name");
} catch (PDOException $e) {
    error_log('Fetch categories error: ' . $e->getMessage());
    $categories = [];
}

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch existing automobile data
if ($id <= 0) {
    $errorMessage = 'Невірний ID автомобіля.';
    $automobile = null;
} else {
    try {
        $automobile = dbFetchOne(
            "SELECT * FROM automobiles WHERE id = ? LIMIT 1",
            [$id]
        );
        
        if (!$automobile) {
            $errorMessage = 'Автомобіль з таким ID не знайдено.';
        } else {
            $errorMessage = null;
            // Populate form with existing data
            $values = [
                'brand' => $automobile['brand'],
                'model' => $automobile['model'],
                'year' => (string)$automobile['year'],
                'color' => $automobile['color'] ?? '',
                'price' => $automobile['price'] !== null ? (string)$automobile['price'] : '',
                'mileage' => $automobile['mileage'] !== null ? (string)$automobile['mileage'] : '',
                'description' => $automobile['description'] ?? '',
                'visible' => (string)$automobile['visible'],
                'category_id' => $automobile['category_id'] !== null ? (string)$automobile['category_id'] : ''
            ];
        }
    } catch (PDOException $e) {
        error_log('Fetch automobile error: ' . $e->getMessage());
        $automobile = null;
        $errorMessage = 'Помилка при завантаженні даних автомобіля.';
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $automobile) {
    // CSRF validation
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors['csrf'] = 'Невалідний запит. Спробуйте ще раз.';
    } else {
        $values['brand'] = trim($_POST['brand'] ?? '');
        $values['model'] = trim($_POST['model'] ?? '');
        $values['year'] = trim($_POST['year'] ?? '');
        $values['color'] = trim($_POST['color'] ?? '');
        $values['price'] = trim($_POST['price'] ?? '');
        $values['mileage'] = trim($_POST['mileage'] ?? '');
        $values['description'] = trim($_POST['description'] ?? '');
        $values['visible'] = isset($_POST['visible']) ? '1' : '0';
        $values['category_id'] = trim($_POST['category_id'] ?? '');
        
        // Validation (same as create)
        if ($values['brand'] === '') {
            $errors['brand'] = 'Марка автомобіля обов\'язкова.';
        } elseif (mb_strlen($values['brand']) > 100) {
            $errors['brand'] = 'Марка не повинна перевищувати 100 символів.';
        }
        
        if ($values['model'] === '') {
            $errors['model'] = 'Модель автомобіля обов\'язкова.';
        } elseif (mb_strlen($values['model']) > 100) {
            $errors['model'] = 'Модель не повинна перевищувати 100 символів.';
        }
        
        if ($values['year'] === '') {
            $errors['year'] = 'Рік випуску обов\'язковий.';
        } elseif (!preg_match('/^\d{4}$/', $values['year'])) {
            $errors['year'] = 'Рік має бути 4-значним числом.';
        } else {
            $year = (int)$values['year'];
            $currentYear = (int)date('Y');
            if ($year < 1900 || $year > $currentYear + 1) {
                $errors['year'] = "Рік має бути між 1900 та " . ($currentYear + 1) . ".";
            }
        }
        
        if ($values['color'] !== '' && mb_strlen($values['color']) > 50) {
            $errors['color'] = 'Колір не повинен перевищувати 50 символів.';
        }
        
        if ($values['price'] !== '') {
            if (!is_numeric($values['price']) || (float)$values['price'] < 0) {
                $errors['price'] = 'Ціна має бути додатним числом.';
            } elseif ((float)$values['price'] > 99999999.99) {
                $errors['price'] = 'Ціна занадто велика.';
            }
        }
        
        if ($values['mileage'] !== '') {
            if (!ctype_digit($values['mileage']) || (int)$values['mileage'] < 0) {
                $errors['mileage'] = 'Пробіг має бути цілим додатним числом.';
            }
        }
        
        if ($values['description'] !== '' && mb_strlen($values['description']) > 5000) {
            $errors['description'] = 'Опис не повинен перевищувати 5000 символів.';
        }
        
        if (empty($errors)) {
            try {
                // Update automobile in database
                dbQuery(
                    "UPDATE automobiles 
                     SET brand = ?, model = ?, year = ?, color = ?, price = ?, mileage = ?, description = ?, visible = ?, category_id = ?
                     WHERE id = ?",
                    [
                        $values['brand'],
                        $values['model'],
                        (int)$values['year'],
                        $values['color'] !== '' ? $values['color'] : null,
                        $values['price'] !== '' ? (float)$values['price'] : null,
                        $values['mileage'] !== '' ? (int)$values['mileage'] : null,
                        $values['description'] !== '' ? $values['description'] : null,
                        (int)$values['visible'],
                        $values['category_id'] !== '' ? (int)$values['category_id'] : null,
                        $id
                    ]
                );
                
                // Regenerate CSRF token
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                // Success message
                $_SESSION['success_message'] = 'Автомобіль успішно оновлено!';
                
                header('Location: index.php?action=view_automobile&id=' . $id);
                exit;
            } catch (PDOException $e) {
                error_log('Update automobile error: ' . $e->getMessage());
                $errors['db'] = 'Помилка при оновленні автомобіля. Спробуйте пізніше.';
            }
        }
    }
}
?>

<main class="content">
    <?php if ($automobile): ?>
        <h2>Редагувати автомобіль #<?=$id?></h2>
        
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <p>Знайдено помилки при заповненні форми:</p>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?=htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" action="index.php?action=update_automobile&id=<?=$id?>" class="automobile-form">
            <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8')?>">
            
            <div class="form-grid">
                <div class="field<?php if (isset($errors['brand'])) echo ' error'; ?>">
                    <label for="brand">Марка <span class="required">*</span></label>
                    <input id="brand" name="brand" type="text" value="<?=htmlspecialchars($values['brand'], ENT_QUOTES, 'UTF-8')?>" required>
                </div>
                
                <div class="field<?php if (isset($errors['model'])) echo ' error'; ?>">
                    <label for="model">Модель <span class="required">*</span></label>
                    <input id="model" name="model" type="text" value="<?=htmlspecialchars($values['model'], ENT_QUOTES, 'UTF-8')?>" required>
                </div>
                
                <div class="field<?php if (isset($errors['year'])) echo ' error'; ?>">
                    <label for="year">Рік випуску <span class="required">*</span></label>
                    <input id="year" name="year" type="text" value="<?=htmlspecialchars($values['year'], ENT_QUOTES, 'UTF-8')?>" required>
                </div>
                
                <div class="field<?php if (isset($errors['color'])) echo ' error'; ?>">
                    <label for="color">Колір</label>
                    <input id="color" name="color" type="text" value="<?=htmlspecialchars($values['color'], ENT_QUOTES, 'UTF-8')?>">
                </div>
                
                <div class="field<?php if (isset($errors['category_id'])) echo ' error'; ?>">
                    <label for="category_id">Категорія</label>
                    <select id="category_id" name="category_id">
                        <option value="">-- Не вибрано --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?=(int)$cat['id']?>" <?= $values['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?=htmlspecialchars($cat['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="field<?php if (isset($errors['price'])) echo ' error'; ?>">
                    <label for="price">Ціна (USD)</label>
                    <input id="price" name="price" type="text" value="<?=htmlspecialchars($values['price'], ENT_QUOTES, 'UTF-8')?>">
                </div>
                
                <div class="field<?php if (isset($errors['mileage'])) echo ' error'; ?>">
                    <label for="mileage">Пробіг (км)</label>
                    <input id="mileage" name="mileage" type="text" value="<?=htmlspecialchars($values['mileage'], ENT_QUOTES, 'UTF-8')?>">
                </div>
                
                <div class="field field-full<?php if (isset($errors['description'])) echo ' error'; ?>">
                    <label for="description">Опис</label>
                    <textarea id="description" name="description" rows="5"><?=htmlspecialchars($values['description'], ENT_QUOTES, 'UTF-8')?></textarea>
                </div>
                
                <div class="field field-full">
                    <label class="checkbox-label">
                        <input type="checkbox" name="visible" value="1" <?= $values['visible'] === '1' ? 'checked' : '' ?>>
                        <span>Опублікувати на сайті (visible = 1)</span>
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Зберегти зміни</button>
                <a href="index.php?action=view_automobile&id=<?=$id?>" class="btn-secondary">Скасувати</a>
            </div>
        </form>
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
