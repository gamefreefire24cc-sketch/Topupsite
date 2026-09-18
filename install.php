<?php
// ডাটাবেস ফাইলটি আপনার প্রজেক্ট ফোল্ডারেই তৈরি হবে
$db_file = __DIR__ . '/database.sqlite';

try {
    // ১. SQLite ডাটাবেসে কানেক্ট করা (ফাইল না থাকলে নিজে তৈরি করে নিবে)
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ২. সবগুলো টেবিল তৈরির কমান্ড (SQLite Syntax)
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      uid TEXT UNIQUE NOT NULL,
      username TEXT NOT NULL,
      email TEXT UNIQUE NOT NULL,
      phone TEXT NOT NULL,
      password TEXT NOT NULL,
      balance INTEGER DEFAULT 0,
      role TEXT DEFAULT 'user',
      joined DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS packages (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      category TEXT NOT NULL,
      mainCategory TEXT NOT NULL,
      name TEXT NOT NULL,
      price INTEGER NOT NULL,
      tag TEXT,
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS orders (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      userId TEXT NOT NULL,
      userName TEXT NOT NULL,
      product TEXT NOT NULL,
      package TEXT NOT NULL,
      price INTEGER NOT NULL,
      walletUsed INTEGER DEFAULT 0,
      directPaid INTEGER DEFAULT 0,
      orderDetails TEXT NOT NULL,
      paymentMethod TEXT NOT NULL,
      senderNumber TEXT,
      trxID TEXT,
      status TEXT DEFAULT 'Pending',
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS deposit_requests (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      uid TEXT NOT NULL,
      userName TEXT NOT NULL,
      method TEXT NOT NULL,
      amount INTEGER NOT NULL,
      senderNumber TEXT NOT NULL,
      trxID TEXT NOT NULL,
      status TEXT DEFAULT 'Pending',
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS pending_requests (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      uid TEXT NOT NULL,
      userName TEXT NOT NULL,
      method TEXT,
      amount INTEGER DEFAULT 0,
      senderNumber TEXT,
      trxID TEXT,
      pkgId TEXT,
      orderDetails TEXT,
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      phone TEXT NOT NULL,
      email TEXT NOT NULL,
      message TEXT NOT NULL,
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS admin_settings (
      setting_key TEXT PRIMARY KEY,
      setting_value TEXT NOT NULL
    );
    ";

    $pdo->exec($sql);

    // ৩. ডিফল্ট এডমিন সেটিংস যুক্ত করা
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO admin_settings (setting_key, setting_value) VALUES 
            ('payment', '{\"bkash\":\"01700000000\",\"bkash_active\":true,\"nagad_active\":true,\"rocket_active\":true}'),
            ('contact', '{\"phone\":\"01700000000\",\"email\":\"admin@offer.com\"}');
        ");
    }

    echo "<h2 style='color: green;'>Success! SQLite Database created successfully.</h2>";
    echo "<p>আপনার প্রজেক্ট ফোল্ডারে <b>database.sqlite</b> নামে একটি ফাইল তৈরি হয়েছে।</p>";
    echo "<p style='color: red;'>Security Warning: Please delete this <b>install.php</b> file!</p>";

} catch(PDOException $e) {
    die("<h2 style='color: red;'>Database Error: " . $e->getMessage() . "</h2>");
}
?>