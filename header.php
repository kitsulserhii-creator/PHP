<?php
// Перевірка чи сесія вже запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Обробка перемикання режиму
if (isset($_GET['mode'])) {
    $_SESSION['mode'] = $_GET['mode'];
}

// За замовчуванням Professional режим
if (!isset($_SESSION['mode'])) {
    $_SESSION['mode'] = 'professional';
}

$mode = $_SESSION['mode'];
$isRealMe = ($mode === 'realme');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isRealMe ? '🎮 Real Me' : '💼 My Portfolio'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?php echo $mode; ?>">
    <header class="<?php echo $mode; ?>">
        <div class="header-content">
            <div class="title-section">
                <h1><?php echo $isRealMe ? 'Реальна версія' : 'Портфоліо'; ?></h1>
                <p><?php echo $isRealMe ? 'просто я, без прикрас' : 'Веб-розробка та програмування'; ?></p>
            </div>
            
            <!-- Стильний тумблер перемикання -->
            <div class="mode-toggle">
                <span class="mode-label <?php echo !$isRealMe ? 'active' : ''; ?>">Professional</span>
                <label class="switch">
                    <input type="checkbox" <?php echo $isRealMe ? 'checked' : ''; ?> 
                           onchange="window.location.href='?mode=' + (this.checked ? 'realme' : 'professional')">
                    <span class="slider round"></span>
                </label>
                <span class="mode-label <?php echo $isRealMe ? 'active' : ''; ?>">Real Me</span>
            </div>
        </div>
    </header>
