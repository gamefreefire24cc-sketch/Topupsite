<?php
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_file = __DIR__ . '/database.sqlite';

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // ১. ব্যানার টেবিল তৈরি
    $pdo->exec("CREATE TABLE IF NOT EXISTS banners (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        image TEXT NOT NULL,
        link TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // ২. প্রোডাক্ট টেবিল তৈরি
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL,
        image TEXT NOT NULL,
        code TEXT UNIQUE NOT NULL,
        input_type TEXT DEFAULT 'UID',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // ৩. ইউজার্স টেবিল তৈরি
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uid TEXT UNIQUE NOT NULL,
        username TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        phone TEXT NOT NULL,
        password TEXT NOT NULL,
        balance INTEGER DEFAULT 0,
        role TEXT DEFAULT 'user',
        joined DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // ৪. প্যাকেজেস টেবিল তৈরি
    $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category TEXT NOT NULL,
        mainCategory TEXT NOT NULL,
        name TEXT NOT NULL,
        price INTEGER NOT NULL,
        tag TEXT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // ৫. অর্ডার্স টেবিল তৈরি
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
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
    )");

    // ৬. ডিপোজিট রিকোয়েস্ট টেবিল তৈরি
    $pdo->exec("CREATE TABLE IF NOT EXISTS deposit_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uid TEXT NOT NULL,
        userName TEXT NOT NULL,
        method TEXT NOT NULL,
        amount INTEGER NOT NULL,
        senderNumber TEXT NOT NULL,
        trxID TEXT NOT NULL,
        status TEXT DEFAULT 'Pending',
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // ৭. পেন্ডিং রিকোয়েস্ট টেবিল তৈরি
    $pdo->exec("CREATE TABLE IF NOT EXISTS pending_requests (
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
    )");

    // ৮. মেসেজেস টেবিল তৈরি
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT NOT NULL,
        message TEXT NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // ৯. এডমিন সেটিংস টেবিল তৈরি
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_settings (
        setting_key TEXT PRIMARY KEY,
        setting_value TEXT NOT NULL
    )");

    // ১০. রিসেলার্স টেবিল তৈরি (নতুন যুক্ত করা হয়েছে)
    $pdo->exec("CREATE TABLE IF NOT EXISTS resellers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uid TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        api_token TEXT UNIQUE NOT NULL,
        wallet_balance INTEGER DEFAULT 0,
        status TEXT DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // ১১. কুপনস টেবিল তৈরি (নতুন যুক্ত করা হয়েছে)
    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE NOT NULL,
        discount_type TEXT NOT NULL,
        discount_value INTEGER NOT NULL,
        min_spend INTEGER DEFAULT 0,
        usage_limit INTEGER DEFAULT 100,
        used_count INTEGER DEFAULT 0,
        status TEXT DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // টেবিল খালি থাকলে প্রাথমিক ব্যানার ডাটা যুক্ত করা
    $checkBanners = $pdo->query("SELECT COUNT(*) FROM banners")->fetchColumn();
    if ($checkBanners == 0) {
        $pdo->exec("INSERT INTO banners (image, link) VALUES 
            ('https://i.postimg.cc/HxszRHqm/Generated-Image-February-21-2026-10-09PM.png', 'https://t.me/+EvHphCT_f5BiMjE1'),
            ('https://i.ibb.co.com/mC5DVV3N/20260306-201020.jpg', 'https://t.me/+EvHphCT_f5BiMjE1'),
            ('https://i.ibb.co.com/Mk3R5qfD/Generated-Image-March-06-2026-8-15-PM.png', 'https://t.me/+EvHphCT_f5BiMjE1')");
    }

    // টেবিল খালি থাকলে প্রাথমিক প্রোডাক্ট ডাটা যুক্ত করা
    $checkProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($checkProducts == 0) {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, image, code, input_type) VALUES (?, ?, ?, ?, ?)");
        $initialProducts = [
            ["WEEKLY OFFER", "SPECIAL OFFER", "https://admin.offertopup.com/products/1753834804.jpg", "offer_weekly", "UID"],
            ["MONTHLY OFFER", "SPECIAL OFFER", "https://admin.offertopup.com/products/1753834812.jpg", "offer_monthly", "UID"],
            ["Free Fire TopUp (BD)", "FREE FIRE", "https://admin.offertopup.com/products/1753834576.jpg", "ff_diamond", "UID"],
            ["Weekly/Monthly Membership", "FREE FIRE", "https://admin.offertopup.com/products/1753834607.jpg", "ff_membership", "UID"],
            ["Free Fire Airdrop", "FREE FIRE", "https://admin.offertopup.com/products/1753834633.jpg", "ff_airdrop", "UID"],
            ["Weekly Lite (BD)", "FREE FIRE", "https://admin.offertopup.com/products/1753834679.jpg", "ff_lite", "UID"],
            ["Level Up Pass", "FREE FIRE", "https://admin.offertopup.com/products/1753834828.jpg", "ff_levelup", "UID"],
            ["Indonesia Server (UID)", "FREE FIRE", "https://admin.offertopup.com/products/1753834935.jpg", "ff_indonesia", "UID"],
            ["E-Football", "INGAME TOPUP", "https://admin.offertopup.com/products/1765198798.jpg", "efootball", "LOGIN"],
            ["Clash Of clans", "INGAME TOPUP", "https://admin.offertopup.com/products/1765198858.jpg", "coc", "LOGIN"],
            ["Clash Royale", "INGAME TOPUP", "https://admin.offertopup.com/products/1765198951.jpg", "clash_royale", "LOGIN"],
            ["YOUTUBE PREMIUM", "SUBSCRIPTION", "https://admin.offertopup.com/products/1753835965.jpg", "sub_youtube", "LOGIN"],
            ["CHATGPT PLUS", "SUBSCRIPTION", "https://admin.offertopup.com/products/1753835981.jpg", "sub_chatgpt", "LOGIN"],
            ["SPOTIFY PREMIUM", "SUBSCRIPTION", "https://admin.offertopup.com/products/1753835988.jpg", "sub_spotify", "LOGIN"],
            ["NETFLIX", "SUBSCRIPTION", "https://upload.wikimedia.org/wikipedia/commons/7/75/Netflix_icon.svg", "sub_netflix", "LOGIN"]
        ];
        foreach ($initialProducts as $p) {
            $stmt->execute($p);
        }
    }

    // ডিফল্ট এডমিন সেটিংস যুক্ত করা
    $checkSettings = $pdo->query("SELECT COUNT(*) FROM admin_settings")->fetchColumn();
    if ($checkSettings == 0) {
        $pdo->exec("
            INSERT INTO admin_settings (setting_key, setting_value) VALUES 
            ('payment', '{\"bkash\":\"01700000000\",\"bkash_active\":true,\"nagad_active\":true,\"rocket_active\":true}'),
            ('contact', '{\"phone\":\"01700000000\",\"email\":\"admin@offer.com\"}');
        ");
    }

} catch(PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Database Connection Failed: ' . $e->getMessage()]));
}
?>