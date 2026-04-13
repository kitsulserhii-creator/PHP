<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

$errors = [];
$values = ['login' => ''];

// CSRF token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Невалідний запит. Спробуйте ще раз.';
    } else {
        $values['login'] = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($values['login'] === '' || $password === '') {
            $errors[] = 'Вкажіть логін та пароль.';
        } else {
            try {
                // Fetch user from database
                $user = dbFetchOne(
                    "SELECT * FROM users WHERE login = ? LIMIT 1",
                    [$values['login']]
                );
                
                if (!$user || !password_verify($password, $user['password_hash'])) {
                    // Same error message to prevent username enumeration
                    $errors[] = 'Неправильний логін або пароль.';
                } else {
                    // Successful login
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_logged'] = $user['login'];
                    $_SESSION['user_admin'] = (bool)$user['admin'];
                    
                    // Regenerate CSRF token
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                    header('Location: index.php?action=profile');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Login error: ' . $e->getMessage());
                $errors[] = 'Помилка підключення до бази даних. Спробуйте пізніше.';
            }
        }
    }
}
?>

<main class="content">
    <h2>Вхід</h2>
    <?php if (!empty($errors)): ?>
        <div class="errors"><ul><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</li>'; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=login">
        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8')?>">
        
        <label for="login">Логін</label>
        <input id="login" name="login" type="text" value="<?=htmlspecialchars($values['login'], ENT_QUOTES, 'UTF-8')?>" required>

        <label for="password">Пароль</label>
        <input id="password" name="password" type="password" required>

        <button type="submit">Увійти</button>
    </form>
    
    <p style="margin-top: 15px;">
        Ще не маєте облікового запису? <a href="index.php?action=registration">Зареєструватись</a>
    </p>
</main>
