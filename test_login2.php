<?php
require_once 'C:\xampp\htdocs\serenity\includes\db.php';
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Admin found: " . ($admin ? "Yes" : "No") . "\n";
if ($admin) {
    echo "Hash in DB: " . $admin['password_hash'] . "\n";
    echo "Password verify: " . (password_verify('admin123', $admin['password_hash']) ? "Success" : "Failed") . "\n";
}
?>
