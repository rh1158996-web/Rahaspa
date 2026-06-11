<?php
require_once 'includes/db.php';

try {
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Check if admin exists
    $stmt = $pdo->query("SELECT * FROM admins WHERE username = 'admin'");
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = 'admin'")->execute([$password_hash]);
        echo "<h1>تم إعادة تعيين كلمة مرور لوحة التحكم بنجاح!</h1>";
        echo "<p>اسم المستخدم: <strong>admin</strong></p>";
        echo "<p>كلمة المرور: <strong>admin123</strong></p>";
        echo "<a href='admin/index.php'>اضغط هنا للذهاب إلى لوحة التحكم</a>";
    } else {
        $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES ('admin', ?)")->execute([$password_hash]);
        echo "<h1>تم إنشاء حساب لوحة التحكم بنجاح!</h1>";
        echo "<p>اسم المستخدم: <strong>admin</strong></p>";
        echo "<p>كلمة المرور: <strong>admin123</strong></p>";
        echo "<a href='admin/index.php'>اضغط هنا للذهاب إلى لوحة التحكم</a>";
    }
} catch (PDOException $e) {
    echo "حدث خطأ في قاعدة البيانات: " . $e->getMessage();
}
?>
