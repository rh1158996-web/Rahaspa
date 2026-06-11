<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT password_hash FROM admins WHERE username='admin'");
$row = $stmt->fetch();
$hash = $row['password_hash'];
echo "Hash length: " . strlen($hash) . "\n";
echo "Hash: " . $hash . "\n";
echo "Verify result: ";
echo password_verify('admin123', $hash) ? "MATCH OK ✓" : "FAIL ✗";
echo "\n";
?>
