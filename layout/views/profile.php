<?php
if (empty($_SESSION['user_logged'])) {
    header('Location: index.php?action=login');
    exit;
}
$login = $_SESSION['user_logged'];
$user = $_SESSION['users'][$login] ?? null;
?>

<main class="content">
    <h2>Профіль користувача: <?=htmlspecialchars($login)?></h2>
    <?php if ($user): ?>
        <p><strong>Ім'я:</strong> <?=htmlspecialchars($user['name'])?:'—'?></p>
        <p><strong>Електронна пошта:</strong> <?=htmlspecialchars($user['email'])?></p>
        <p><strong>Про себе:</strong><br><?=nl2br(htmlspecialchars($user['about']))?:'—'?></p>
        <p><strong>Стать:</strong> <?= ($user['gender']==='1') ? 'Чоловіча' : 'Жіноча' ?></p>
        <p><strong>Телефон:</strong> <?=htmlspecialchars($user['phone'])?:'—'?></p>
        <p><strong>Дата народження:</strong> <?=htmlspecialchars($user['dob'])?></p>
        <p><a href="index.php?action=logout">Вийти</a></p>
    <?php else: ?>
        <p>Інформація про користувача недоступна.</p>
    <?php endif; ?>
</main>
