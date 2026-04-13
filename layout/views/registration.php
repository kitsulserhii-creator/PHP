<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

$errors = [];
$values = [
    'login' => '', 'email' => '', 'about' => '', 'name' => '', 'gender' => '', 'phone' => '', 'dob' => ''
];

// CSRF token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors['csrf'] = 'Невалідний запит. Спробуйте ще раз.';
    } else {
        $values['login'] = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $values['email'] = trim($_POST['email'] ?? '');
        $values['about'] = $_POST['about'] ?? '';
        $values['name'] = trim($_POST['name'] ?? '');
        $values['gender'] = isset($_POST['gender']) ? $_POST['gender'] : '';
        $values['phone'] = trim($_POST['phone'] ?? '');
        $values['dob'] = trim($_POST['dob'] ?? '');

        if ($values['login'] === '') {
            $errors['login'] = 'Логін обов\'язковий.';
        } elseif (!preg_match('/^[A-Za-z\p{Cyrillic}0-9_-]{4,}$/u', $values['login'])) {
            $errors['login'] = 'Логін: мінімум 4 символи; дозволені латиниця, кирилиця, цифри, _ та -.';
        }

        if ($password === '') {
            $errors['password'] = 'Пароль обов\'язковий.';
        } else {
            if (strlen($password) < 7) {
                $errors['password'] = 'Пароль має містити не менше 7 символів.';
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $errors['password'] = 'Пароль повинен містити принаймні одну велику літеру.';
            } elseif (!preg_match('/[a-z]/', $password)) {
                $errors['password'] = 'Пароль повинен містити принаймні одну малу літеру.';
            } elseif (!preg_match('/\d/', $password)) {
                $errors['password'] = 'Пароль повинен містити принаймні одну цифру.';
            }
        }

        if ($confirm === '') {
            $errors['confirm_password'] = 'Підтвердження пароля обов\'язкове.';
        } elseif ($password !== $confirm) {
            $errors['confirm_password'] = 'Паролі не співпадають.';
        }

        if ($values['email'] === '') {
            $errors['email'] = 'Електронна пошта обов\'язкова.';
        } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Невірний формат email.';
        }

        if ($values['about'] !== '') {
            $values['about'] = strip_tags($values['about']);
        }

        if ($values['name'] !== '') {
            if (mb_strlen($values['name']) > 255) {
                $errors['name'] = 'Ім\'я не повинно перевищувати 255 символів.';
            } elseif (!preg_match("/^[\p{L}\-']+([ \p{L}\-']+)*$/u", $values['name'])) {
                $errors['name'] = 'Ім\'я може містити лише літери, дефіс та апостроф.';
            }
        }

        if ($values['gender'] === '') {
            $errors['gender'] = 'Потрібно вибрати стать.';
        } elseif (!in_array($values['gender'], ['0','1'], true)) {
            $errors['gender'] = 'Неприпустиме значення статі.';
        }

        if ($values['phone'] !== '') {
            if (mb_strlen($values['phone']) > 30) {
                $errors['phone'] = 'Телефон не повинен перевищувати 30 символів.';
            } elseif (!preg_match('/^[0-9()+\s-]+$/', $values['phone'])) {
                $errors['phone'] = 'Телефон містить недопустимі символи.';
            }
        }

        if ($values['dob'] === '') {
            $errors['dob'] = 'Дата народження обов\'язкова.';
        } else {
            $ok = false;
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $values['dob'], $m)) {
                $ok = checkdate((int)$m[2], (int)$m[1], (int)$m[3]);
            }
            if (!$ok && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $values['dob'], $m)) {
                $ok = checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
            }
            if (!$ok && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $values['dob'], $m)) {
                $ok = checkdate((int)$m[1], (int)$m[2], (int)$m[3]);
            }
            if (!$ok) {
                $errors['dob'] = 'Неприпустимий формат дати.';
            }
        }

        if (empty($errors)) {
            try {
                // Check if login already exists
                $existing = dbFetchOne(
                    "SELECT id FROM users WHERE login = ? LIMIT 1",
                    [$values['login']]
                );
                
                if ($existing) {
                    $errors['login'] = 'Користувач з таким логіном вже існує.';
                } else {
                    // Hash password with BCRYPT, cost 12
                    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    // Insert user into database
                    dbQuery(
                        "INSERT INTO users (login, password_hash, email, admin, name, about, gender, phone, dob) 
                         VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?)",
                        [
                            $values['login'],
                            $hash,
                            $values['email'],
                            $values['name'],
                            $values['about'],
                            (int)$values['gender'],
                            $values['phone'],
                            $values['dob']
                        ]
                    );
                    
                    // Get the inserted user ID
                    $userId = (int)getDbConnection()->lastInsertId();
                    
                    // Auto-login after successful registration
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_logged'] = $values['login'];
                    $_SESSION['user_admin'] = false;
                    
                    // Regenerate CSRF token
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                    header('Location: index.php?action=profile');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Registration error: ' . $e->getMessage());
                $errors['db'] = 'Помилка під час реєстрації. Спробуйте пізніше.';
            }
        }
    }
}

?>

<main class="content">
    <h2>Реєстрація користувача</h2>

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

    <form method="post" action="index.php?action=registration" class="registration-form">
        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8')?>">
        
        <div class="form-grid">
            <div class="field field-half<?php if (isset($errors['login'])) echo ' error'; ?>">
                <label for="login">Логін</label>
                <input id="login" name="login" type="text" value="<?=htmlspecialchars($values['login'])?>">
            </div>

            <div class="field field-half<?php if (isset($errors['email'])) echo ' error'; ?>">
                <label for="email">Електронна пошта</label>
                <input id="email" name="email" type="email" value="<?=htmlspecialchars($values['email'])?>">
            </div>

            <div class="field field-half<?php if (isset($errors['password'])) echo ' error'; ?>">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password">
            </div>

            <div class="field field-half<?php if (isset($errors['confirm_password'])) echo ' error'; ?>">
                <label for="confirm_password">Повторіть пароль</label>
                <input id="confirm_password" name="confirm_password" type="password">
            </div>

            <div class="field field-full<?php if (isset($errors['about'])) echo ' error'; ?>">
                <label for="about">Про себе (необов'язково)</label>
                <textarea id="about" name="about"><?=htmlspecialchars($values['about'])?></textarea>
            </div>

            <div class="field field-half<?php if (isset($errors['name'])) echo ' error'; ?>">
                <label for="name">Ім'я (необов'язково)</label>
                <input id="name" name="name" type="text" value="<?=htmlspecialchars($values['name'])?>">
            </div>

            <div class="field field-half<?php if (isset($errors['gender'])) echo ' error'; ?>">
                <label>Стать</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="0" <?=($values['gender']==='0')?'checked':''?>> Жіноча</label>
                    <label><input type="radio" name="gender" value="1" <?=($values['gender']==='1')?'checked':''?>> Чоловіча</label>
                </div>
            </div>

            <div class="field field-half<?php if (isset($errors['phone'])) echo ' error'; ?>">
                <label for="phone">Телефон (необов'язково)</label>
                <input id="phone" name="phone" type="tel" value="<?=htmlspecialchars($values['phone'])?>">
            </div>

            <div class="field field-half<?php if (isset($errors['dob'])) echo ' error'; ?>">
                <label for="dob">Дата народження</label>
                <input id="dob" name="dob" type="text" value="<?=htmlspecialchars($values['dob'])?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit">Зареєструватися</button>
        </div>
    </form>
</main>
