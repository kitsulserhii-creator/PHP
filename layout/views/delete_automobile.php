<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// Only admin can delete automobiles
if (empty($_SESSION['user_admin'])) {
    header('Location: index.php?action=automobiles');
    exit;
}

// Get and validate ID
$id = (int)($_GET['id'] ?? 0);

$errorMessage = null;
$successMessage = null;

if ($id <= 0) {
    $errorMessage = 'Невірний ID автомобіля.';
} else {
    try {
        // Check if automobile exists
        $automobile = dbFetchOne(
            "SELECT id, brand, model FROM automobiles WHERE id = ? LIMIT 1",
            [$id]
        );
        
        if (!$automobile) {
            $errorMessage = 'Автомобіль з таким ID не знайдено.';
        } else {
            // Delete automobile
            dbQuery("DELETE FROM automobiles WHERE id = ?", [$id]);
            
            $successMessage = sprintf(
                'Автомобіль "%s %s" (ID #%d) успішно видалено.',
                htmlspecialchars($automobile['brand'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars($automobile['model'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                $id
            );
        }
    } catch (PDOException $e) {
        error_log('Delete automobile error: ' . $e->getMessage());
        $errorMessage = 'Помилка при видаленні автомобіля. Спробуйте пізніше.';
    }
}
?>

<main class="content">
    <div class="delete-result">
        <?php if ($successMessage): ?>
            <div class="success-message-large">
                <i class="fas fa-check-circle"></i>
                <h2>Успішно видалено</h2>
                <p><?=$successMessage?></p>
            </div>
        <?php else: ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>Помилка видалення</h2>
                <p><?=htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></p>
            </div>
        <?php endif; ?>
        
        <div class="form-actions" style="margin-top: 30px;">
            <a href="index.php?action=automobiles" class="btn-primary">
                <i class="fas fa-arrow-left"></i> Повернутись до списку автомобілів
            </a>
        </div>
    </div>
</main>
