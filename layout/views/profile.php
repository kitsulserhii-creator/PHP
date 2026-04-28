<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (empty($_SESSION['user_logged']) || empty($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$login = $_SESSION['user_logged'];

// Fetch user data from database
try {
    $user = dbFetchOne(
        "SELECT * FROM users WHERE id = ? LIMIT 1",
        [$userId]
    );
    
    if (!$user) {
        // User not found in database, logout
        unset($_SESSION['user_logged'], $_SESSION['user_id'], $_SESSION['user_admin']);
        header('Location: index.php?action=login');
        exit;
    }
} catch (PDOException $e) {
    error_log('Profile fetch error: ' . $e->getMessage());
    $user = null;
}
?>

<main class="content">
    <h2>Профіль користувача: <?=htmlspecialchars($login, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></h2>
    <?php if ($user): ?>
        <div class="info-card">
            <?php if ($user['admin']): ?>
                <p style="background: #3498db; color: white; padding: 8px 12px; border-radius: 5px; display: inline-block; font-weight: 600; margin-bottom: 15px;">
                    <i class="fas fa-shield-alt"></i> Адміністратор
                </p>
            <?php endif; ?>
            
            <p><strong>Ім'я:</strong> <?=htmlspecialchars($user['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?: '—'?></p>
            <p><strong>Електронна пошта:</strong> <?=htmlspecialchars($user['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></p>
            <p><strong>Про себе:</strong><br><?=nl2br(htmlspecialchars($user['about'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?: '—'?></p>
            <p><strong>Стать:</strong> <?= ($user['gender'] == 1) ? 'Чоловіча' : 'Жіноча' ?></p>
            <p><strong>Телефон:</strong> <?=htmlspecialchars($user['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?: '—'?></p>
            <p><strong>Дата народження:</strong> <?=htmlspecialchars($user['dob'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></p>
            <p><strong>Дата реєстрації:</strong> <?=htmlspecialchars($user['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></p>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="index.php?action=logout" style="display: inline-block; padding: 10px 20px; background: #e74c3c; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                Вийти
            </a>
        </div>
    <?php else: ?>
        <p>Інформація про користувача недоступна.</p>
    <?php endif; ?>
</main>
