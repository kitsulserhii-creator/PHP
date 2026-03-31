<?php
$errors = [];
$values = ['login' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['login'] = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($values['login'] === '' || $password === '') {
        $errors[] = 'Вкажіть логін та пароль.';
    } else {
        $users = $_SESSION['users'] ?? [];
        if (!isset($users[$values['login']])) {
            $errors[] = 'Неправильний логін або пароль.';
        } else {
            $user = $users[$values['login']];
            if (!password_verify($password, $user['password_hash'])) {
                $errors[] = 'Неправильний логін або пароль.';
            } else {
                $_SESSION['user_logged'] = $values['login'];
                header('Location: index.php?action=profile');
                exit;
            }
        }
    }
}
?>

<main class="content">
    <h2>Вхід</h2>
    <?php if (!empty($errors)): ?>
        <div class="errors"><ul><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=login">
        <label for="login">Логін</label>
        <input id="login" name="login" value="<?=htmlspecialchars($values['login'])?>">

        <label for="password">Пароль</label>
        <input id="password" name="password" type="password">

        <button type="submit">Увійти</button>
    </form>
</main>
