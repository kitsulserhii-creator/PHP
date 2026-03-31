<?php
// Simple logout handler
unset($_SESSION['user_logged']);
header('Location: index.php');
exit;
