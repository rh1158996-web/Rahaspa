<?php
require_once 'includes/db.php';
$h = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->query("UPDATE admins SET password_hash = '$h' WHERE username = 'admin'");
echo "Hash updated to: " . $h;
?>
