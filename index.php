<?php
include_once __DIR__ . '/layout/header.php';

include_once __DIR__ . '/layout/left_menu.php';

$action = isset($_GET['action']) ? basename($_GET['action']) : 'main';
$viewFile = __DIR__ . '/layout/views/' . $action . '.php';
if (!is_file($viewFile)) {
    $viewFile = __DIR__ . '/layout/views/main.php';
}

include $viewFile;

include_once __DIR__ . '/layout/footer.php';
?>
