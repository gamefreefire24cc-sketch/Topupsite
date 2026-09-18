<?php
require_once '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_uid']) || $_SESSION['user_role'] !== 'admin') {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized!']));
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $data['action'] ?? $_GET['action'] ?? '';

// ড্যাশবোর্ড স্ট্যাটস (ওয়ালেট ব্যালেন্স সহ)
if ($action === 'stats') {
    $ordersCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
    $depCount = $pdo->query("SELECT COUNT(*) FROM deposit_requests WHERE status = 'Pending'")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $revenue = $pdo->query("SELECT COALESCE(SUM(price), 0) FROM orders WHERE status = 'Completed'")->fetchColumn() ?: 0;
    $walletBalance = $pdo->query("SELECT COALESCE(SUM(balance), 0) FROM users")->fetchColumn() ?: 0;

    echo json_encode([
        'status' => 'success',
        'orders' => $ordersCount,
        'deposits' => $depCount,
        'users' => $usersCount,
        'revenue' => $revenue,
        'wallet_balance' => $walletBalance
    ]);
    exit;
}

// ব্যানার যোগ করা
elseif ($action === 'add_banner') {
    $img = trim($data['image'] ?? '');
    $link = trim($data['link'] ?? '');
    if(empty($img)) die(json_encode(['status'=>'error', 'message'=>'Image URL is required!']));

    $stmt = $pdo->prepare("INSERT INTO banners (image, link) VALUES (?, ?)");
    $stmt->execute([$img, $link]);
    echo json_encode(['status' => 'success']);
    exit;
}
// কুপন যোগ
elseif ($action === 'add_coupon') {
    $code = strtoupper(trim($data['code'] ?? ''));
    $type = $data['type'] ?? 'percent';
    $value = (int)($data['value'] ?? 0);
    $min = (int)($data['min'] ?? 0);
    $limit = (int)($data['limit'] ?? 100);

    if(empty($code) || $value <= 0) {
        die(json_encode(['status' => 'error', 'message' => 'সব সঠিক তথ্য দিন!']));
    }

    $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_spend, usage_limit) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$code, $type, $value, $min, $limit]);
        echo json_encode(['status' => 'success']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'এই কুপন কোডটি ইতিমধ্যে রয়েছে!']);
    }
    exit;
}
// কুপন ডিলিট
elseif ($action === 'delete_coupon') {
    $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([(int)$data['id']]);
    echo json_encode(['status' => 'success']);
    exit;
}
// ব্যানার ডিলিট করা
elseif ($action === 'delete_banner') {
    $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->execute([(int)$data['id']]);
    echo json_encode(['status' => 'success']);
    exit;
}

// নোটিশ সেভ করা
elseif ($action === 'save_notice') {
    $notice = trim($data['notice'] ?? '');
    $stmt = $pdo->prepare("INSERT INTO admin_settings (setting_key, setting_value) VALUES ('notice', ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value");
    $stmt->execute([$notice]);
    echo json_encode(['status' => 'success']);
    exit;
}
// নতুন রিসেলার তৈরি
elseif ($action === 'add_reseller') {
    $name = trim($data['name'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));

    if(empty($name) || empty($email)) {
        die(json_encode(['status' => 'error', 'message' => 'সব ফিল্ড পূরণ করুন!']));
    }

    $uid = uniqid('RES_');
    $apiToken = bin2hex(random_bytes(24)); // সিকিউর ইউনিক API টোকেন জেনারেট

    $stmt = $pdo->prepare("INSERT INTO resellers (uid, name, email, api_token, wallet_balance) VALUES (?, ?, ?, ?, 0)");
    try {
        $stmt->execute([$uid, $name, $email, $apiToken]);
        echo json_encode(['status' => 'success']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'এই ইমেইলটি ইতিমধ্যে ব্যবহৃত হয়েছে!']);
    }
    exit;
}
// অর্ডার স্ট্যাটাস আপডেট ও রিফান্ড
elseif ($action === 'update_order_status') {
    $id = (int)$data['id'];
    $status = $data['status'];

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if ($order && $status === 'Rejected' && $order['status'] === 'Pending') {
        $refund = (int)$order['walletUsed'];
        if ($refund > 0) {
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE uid = ?")->execute([$refund, $order['userId']]);
        }
    }

    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Order status updated!']);
    exit;
}

// ডিপোজিট স্ট্যাটাস আপডেট
elseif ($action === 'update_deposit_status') {
    $id = (int)$data['id'];
    $status = $data['status'];

    $stmt = $pdo->prepare("SELECT * FROM deposit_requests WHERE id = ?");
    $stmt->execute([$id]);
    $dep = $stmt->fetch();

    if ($dep && $status === 'Approved' && $dep['status'] === 'Pending') {
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE uid = ?")->execute([$dep['amount'], $dep['uid']]);
    }

    $pdo->prepare("UPDATE deposit_requests SET status = ? WHERE id = ?")->execute([$status, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Deposit updated!']);
    exit;
}

// প্যাকেজ যোগ
elseif ($action === 'add_package') {
    $stmt = $pdo->prepare("INSERT INTO packages (category, mainCategory, name, price, tag) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$data['category'], $data['mainCategory'], $data['name'], $data['price'], $data['tag'] ?? '']);
    echo json_encode(['status' => 'success']);
    exit;
}

// প্যাকেজ ডিলিট
elseif ($action === 'delete_package') {
    $pdo->prepare("DELETE FROM packages WHERE id = ?")->execute([(int)$data['id']]);
    echo json_encode(['status' => 'success']);
    exit;
}
// ইউজার ওয়ালেট ব্যালেন্স ম্যানুয়াল অ্যাডজাস্টমেন্ট
elseif ($action === 'adjust_user_balance') {
    $uid = trim($data['uid'] ?? '');
    $type = $data['type'] ?? 'add';
    $amount = (int)($data['amount'] ?? 0);

    if(empty($uid) || $amount <= 0) {
        die(json_encode(['status' => 'error', 'message' => 'সঠিক তথ্য প্রদান করুন!']));
    }

    // ইউজারের বর্তমান ব্যালেন্স চেক করা
    $stmt = $pdo->prepare("SELECT balance, username FROM users WHERE uid = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();

    if(!$user) {
        die(json_encode(['status' => 'error', 'message' => 'ইউজার খুঁজে পাওয়া যায়নি!']));
    }

    $currentBal = (int)$user['balance'];
    $newBal = $currentBal;

    if($type === 'add') {
        $newBal = $currentBal + $amount;
    } elseif($type === 'subtract') {
        $newBal = max(0, $currentBal - $amount); // ব্যালেন্স যেন মাইনাস না হয়
    } elseif($type === 'set') {
        $newBal = $amount;
    }

    // ডাটাবেস আপডেট
    $update = $pdo->prepare("UPDATE users SET balance = ? WHERE uid = ?");
    $update->execute([$newBal, $uid]);

    echo json_encode([
        'status' => 'success', 
        'message' => "{$user['username']}-এর ব্যালেন্স সফলভাবে আপডেট করা হয়েছে! নতুন ব্যালেন্স: ৳{$newBal}"
    ]);
    exit;
}
// সেটিংস সেভ
elseif ($action === 'save_settings') {
    $key = $data['key'] ?? 'payment';
    $val = json_encode($data['settings']);
    $pdo->prepare("INSERT INTO admin_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value")->execute([$key, $val]);
    echo json_encode(['status' => 'success', 'message' => 'Settings saved!']);
    exit;
}
// প্রোডাক্ট (গেম/ক্যাটাগরি) যোগ
elseif ($action === 'add_product') {
    $name = trim($data['name'] ?? '');
    $category = strtoupper(trim($data['category'] ?? ''));
    $image = trim($data['image'] ?? '');
    $code = trim($data['code'] ?? '');
    $input_type = trim($data['input_type'] ?? 'UID');

    if(empty($name) || empty($category) || empty($image) || empty($code)) {
        die(json_encode(['status'=>'error', 'message'=>'সব ফিল্ড পূরণ করুন!']));
    }

    $stmt = $pdo->prepare("SELECT id FROM products WHERE code = ?");
    $stmt->execute([$code]);
    if($stmt->fetch()) {
        die(json_encode(['status'=>'error', 'message'=>'এই Unique Code টি ইতিমধ্যে ব্যবহৃত! অন্য কোড দিন।']));
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, category, image, code, input_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $category, $image, $code, $input_type]);
    
    echo json_encode(['status' => 'success']);
    exit;
}

// প্রোডাক্ট ডিলিট
elseif ($action === 'delete_product') {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([(int)$data['id']]);
    echo json_encode(['status' => 'success']);
    exit;
}
// মেসেজ ডিলিট
elseif ($action === 'delete_message') {
    $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([(int)$data['id']]);
    echo json_encode(['status' => 'success']);
    exit;
}
?>