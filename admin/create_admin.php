<?php
// মূল ফোল্ডার থেকে db.php কে কানেক্ট করা হচ্ছে
if (file_exists('../db.php')) {
    require_once '../db.php';
} elseif (file_exists('db.php')) {
    require_once 'db.php';
} else {
    die("<h2 style='color:red;'>ত্রুটি: db.php ফাইলটি খুঁজে পাওয়া যায়নি!</h2>");
}

// আপনি যে ইমেইল ও পাসওয়ার্ড দিয়ে এডমিন লগইন করতে চান
$admin_name     = "Super Admin";
$admin_email    = "admin@gmail.com";
$admin_phone    = "01700000000";
$admin_password = "admin123456"; // লগইন পাসওয়ার্ড

try {
    // ইমেইল আগে থেকেই আছে কিনা চেক করা
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    $existing = $stmt->fetch();

    $hash = password_hash($admin_password, PASSWORD_DEFAULT);

    if ($existing) {
        // একাউন্ট থাকলে তাকে এডমিন বানানো
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin', password = ?, username = ? WHERE email = ?");
        $stmt->execute([$hash, $admin_name, $admin_email]);
        echo "<h2 style='color: green;'>সফল! একাউন্টটি 'Admin' রোলে আপডেট করা হয়েছে।</h2>";
    } else {
        // নতুন এডমিন তৈরি করা
        $uid = uniqid('ADMIN_');
        $stmt = $pdo->prepare("INSERT INTO users (uid, username, email, phone, password, role, balance) VALUES (?, ?, ?, ?, ?, 'admin', 0)");
        $stmt->execute([$uid, $admin_name, $admin_email, $admin_phone, $hash]);
        echo "<h2 style='color: green;'>অভিনন্দন! নতুন এডমিন একাউন্ট সফলভাবে তৈরি হয়েছে।</h2>";
    }

    echo "<p><b>ইমেইল:</b> {$admin_email}</p>";
    echo "<p><b>পাসওয়ার্ড:</b> {$admin_password}</p>";
    echo "<p><a href='../login.php' style='display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;text-decoration:none;border-radius:8px;'>লগইন পেজে যান</a></p>";
    echo "<p style='color:red;'><b>সতর্কতা:</b> কাজ শেষ হলে এই <code>create_admin.php</code> ফাইলটি ডিলিট করে দিন।</p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error: " . $e->getMessage() . "</h2>";
}
?>