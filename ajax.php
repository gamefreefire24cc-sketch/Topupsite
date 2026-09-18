<?php
require_once 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $data['action'] ?? $_GET['action'] ?? '';

// ১. রেজিস্টার
if ($action === 'register') {
    $email = strtolower(trim($data['email'] ?? ''));
    $phone = trim($data['phone'] ?? '');
    $name = trim($data['name'] ?? '');
    $password = $data['password'] ?? '';

    if(empty($email) || empty($password) || empty($name)) {
        die(json_encode(['status' => 'error', 'message' => 'সবগুলো ঘর পূরণ করুন!']));
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die(json_encode(['status' => 'error', 'message' => 'এই ইমেইলটি ইতিমধ্যে ব্যবহৃত হয়েছে!']));
    }

    $uid = uniqid('UID_');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (uid, username, email, phone, password, role, balance) VALUES (?, ?, ?, ?, ?, 'user', 0)");
    $stmt->execute([$uid, $name, $email, $phone, $hash]);

    $_SESSION['user_uid'] = $uid;
    $_SESSION['user_role'] = 'user';
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    echo json_encode(['status' => 'success', 'message' => 'রেজিস্ট্রেশন সফল হয়েছে!']);
    exit;
}

// ২. লগইন
elseif ($action === 'login') {
    $email = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_uid'] = $user['uid'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];

        echo json_encode(['status' => 'success', 'role' => $user['role']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ভুল ইমেইল অথবা পাসওয়ার্ড!']);
    }
    exit;
}

// ৩. লগআউট
elseif ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    echo json_encode(['status' => 'success']);
    exit;
}

// ৪. ইউজার প্রোফাইল ডাটা
elseif ($action === 'get_profile') {
    if(!isset($_SESSION['user_uid'])) die(json_encode(['status'=>'error', 'message'=>'Not logged in']));
    $stmt = $pdo->prepare("SELECT uid, username, email, phone, balance, role FROM users WHERE uid = ?");
    $stmt->execute([$_SESSION['user_uid']]);
    $user = $stmt->fetch();
    echo json_encode(['status' => 'success', 'data' => $user]);
    exit;
}

// ৫. প্রোফাইল আপডেট
elseif ($action === 'update_profile') {
    if(!isset($_SESSION['user_uid'])) die(json_encode(['status'=>'error']));
    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');

    $stmt = $pdo->prepare("UPDATE users SET username = ?, phone = ? WHERE uid = ?");
    $stmt->execute([$name, $phone, $_SESSION['user_uid']]);
    $_SESSION['user_name'] = $name;

    echo json_encode(['status' => 'success', 'message' => 'প্রোফাইল আপডেট হয়েছে!']);
    exit;
}

// ৬. প্যাকেজ লিস্ট (ক্যাটাগরি অনুযায়ী)
elseif ($action === 'get_packages') {
    $category = $_GET['category'] ?? '';
    if(!empty($category)) {
        $stmt = $pdo->prepare("SELECT * FROM packages WHERE category = ? ORDER BY price ASC");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query("SELECT * FROM packages ORDER BY price ASC");
    }
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
    exit;
}

// ৭. সেটিংস ফেচ করা
elseif ($action === 'get_settings') {
    $key = $_GET['key'] ?? 'payment';
    $stmt = $pdo->prepare("SELECT setting_value FROM admin_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $res = $stmt->fetch();
    echo json_encode(json_decode($res['setting_value'] ?? '{}', true));
    exit;
}

// ৮. মেসেজ পাঠানো (Contact Page)
elseif ($action === 'send_message') {
    $stmt = $pdo->prepare("INSERT INTO messages (name, phone, email, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['name'] ?? '', $data['phone'] ?? '', $data['email'] ?? '', $data['message'] ?? '']);
    echo json_encode(['status' => 'success', 'message' => 'মেসেজ পাঠানো হয়েছে!']);
    exit;
}

// ৯. ইউজার হিস্ট্রি (Orders & Deposits)
elseif ($action === 'get_user_history') {
    if(!isset($_SESSION['user_uid'])) die(json_encode(['status'=>'error']));
    $uid = $_SESSION['user_uid'];

    $ord = $pdo->prepare("SELECT * FROM orders WHERE userId = ? ORDER BY id DESC");
    $ord->execute([$uid]);

    $dep = $pdo->prepare("SELECT * FROM deposit_requests WHERE uid = ? ORDER BY id DESC");
    $dep->execute([$uid]);

    echo json_encode([
        'status' => 'success',
        'orders' => $ord->fetchAll(),
        'deposits' => $dep->fetchAll()
    ]);
    exit;
}
// কুপন ভ্যালিডেশন চেক করা
elseif ($action === 'apply_coupon') {
    $code = strtoupper(trim($data['code'] ?? ''));
    $subtotal = (int)($data['subtotal'] ?? 0);

    if(empty($code)) {
        echo json_encode(['status' => 'error', 'message' => 'কুপন কোড দিন!']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'Active'");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if(!$coupon) {
        echo json_encode(['status' => 'error', 'message' => 'ভুল অথবা মেয়াদোত্তীর্ণ কুপন কোড!']);
        exit;
    }

    if($subtotal < $coupon['min_spend']) {
        echo json_encode(['status' => 'error', 'message' => "এই কুপন ব্যবহার করতে হলে সর্বনিম্ন ৳{$coupon['min_spend']} অর্ডার করতে হবে!"]);
        exit;
    }

    if($coupon['used_count'] >= $coupon['usage_limit']) {
        echo json_encode(['status' => 'error', 'message' => 'এই কুপনের ব্যবহারসীমা শেষ হয়ে গেছে!']);
        exit;
    }

    $discount = 0;
    if($coupon['discount_type'] === 'percent') {
        $discount = round(($subtotal * $coupon['discount_value']) / 100);
    } else {
        $discount = $coupon['discount_value'];
    }

    $discount = min($discount, $subtotal);
    $finalTotal = $subtotal - $discount;

    echo json_encode([
        'status' => 'success',
        'discount' => $discount,
        'finalTotal' => $finalTotal,
        'code' => $code,
        'message' => 'কুপন সফলভাবে অ্যাপ্লাই হয়েছে!'
    ]);
    exit;
}
echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
?>