<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// Only logged-in users can create automobiles
if (empty($_SESSION['user_logged']) || empty($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

$errors = [];
$values = [
    'brand' => '', 'model' => '', 'year' => '', 'color' => '',
    'price' => '', 'mileage' => '', 'description' => '', 'category_id' => ''
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $values['category_id'] = trim($_POST['category_id'] ?? '');
        
        // Validation
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
                // Determine visibility: admin = 1 (published), regular user = 0 (unpublished)
                $visible = !empty($_SESSION['user_admin']) ? 1 : 0;
                
                // Insert automobile into database
                dbQuery(
                    "INSERT INTO automobiles (brand, model, year, color, price, mileage, description, visible, author_id, category_id) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $values['brand'],
                        $values['model'],
                        (int)$values['year'],
                        $values['color'] !== '' ? $values['color'] : null,
                        $values['price'] !== '' ? (float)$values['price'] : null,
                        $values['mileage'] !== '' ? (int)$values['mileage'] : null,
                        $values['description'] !== '' ? $values['description'] : null,
                        $visible,
                        (int)$_SESSION['user_id'],
                        $values['category_id'] !== '' ? (int)$values['category_id'] : null
                    ]
                );
                
                // Regenerate CSRF token
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                // Success message
                $_SESSION['success_message'] = 'Автомобіль успішно додано!';
                
                header('Location: index.php?action=automobiles');
                exit;
            } catch (PDOException $e) {
                error_log('Create automobile error: ' . $e->getMessage());
                $errors['db'] = 'Помилка при додаванні автомобіля. Спробуйте пізніше.';
            }
        }
    }
}
?>

<main class="content">
    <h2>Додати новий автомобіль</h2>
    
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
    
    <form method="post" action="index.php?action=create_automobile" class="automobile-form">
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
                <input id="year" name="year" type="text" placeholder="2022" value="<?=htmlspecialchars($values['year'], ENT_QUOTES, 'UTF-8')?>" required>
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
                <input id="price" name="price" type="text" placeholder="25000.00" value="<?=htmlspecialchars($values['price'], ENT_QUOTES, 'UTF-8')?>">
            </div>
            
            <div class="field<?php if (isset($errors['mileage'])) echo ' error'; ?>">
                <label for="mileage">Пробіг (км)</label>
                <input id="mileage" name="mileage" type="text" placeholder="50000" value="<?=htmlspecialchars($values['mileage'], ENT_QUOTES, 'UTF-8')?>">
            </div>
            
            <div class="field field-full<?php if (isset($errors['description'])) echo ' error'; ?>">
                <label for="description">Опис</label>
                <textarea id="description" name="description" rows="5"><?=htmlspecialchars($values['description'], ENT_QUOTES, 'UTF-8')?></textarea>
            </div>
        </div>
        
        <div class="form-info">
            <?php if (!empty($_SESSION['user_admin'])): ?>
                <p><i class="fas fa-info-circle"></i> Як адміністратор, ваш автомобіль буде опублікований одразу (visible = 1).</p>
            <?php else: ?>
                <p><i class="fas fa-info-circle"></i> Ваш автомобіль буде додано, але не опублікований (visible = 0). Адміністратор має його схвалити.</p>
            <?php endif; ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Додати автомобіль</button>
            <a href="index.php?action=automobiles" class="btn-secondary">Скасувати</a>
        </div>
    </form>
</main>
