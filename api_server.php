<?php
require_once 'db.php';
header('Content-Type: application/json');

function check_fraud_trx($pdo, $trxID) {
    if (empty($trxID) || strlen($trxID) < 8) return "TrxID খুবই ছোট! সঠিক TrxID দিন।";
    
    $stmt = $pdo->prepare("SELECT id FROM deposit_requests WHERE trxID = ?");
    $stmt->execute([$trxID]);
    if ($stmt->fetch()) return "এই TrxID টি আগেই ডিপোজিটে ব্যবহার করা হয়েছে!";

    $stmt = $pdo->prepare("SELECT id FROM orders WHERE trxID = ?");
    $stmt->execute([$trxID]);
    if ($stmt->fetch()) return "এই TrxID টি অন্য একটি অর্ডারে আগেই ব্যবহার করা হয়েছে!";

    return "OK";
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid Request!']));
}

$action = $input['action'];
$uid = $_SESSION['user_uid'] ?? $input['uid'] ?? null;
$userName = $_SESSION['user_name'] ?? $input['userName'] ?? 'User';
$appliedCoupon = strtoupper(trim($input['couponCode'] ?? ''));

if(!$uid) {
    die(json_encode(['status' => 'error', 'message' => 'অনুগ্রহ করে আগে লগইন করুন!']));
}

// কুপন ব্যবহার করা হয়ে থাকলে তার ইউজ কাউন্ট বাড়িয়ে দেওয়ার ফাংশন
function handleCouponUsage($pdo, $couponCode) {
    if (!empty($couponCode)) {
        $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$couponCode]);
    }
}

// ১. ওয়ালেট দিয়ে অর্ডার
if ($action === 'process_wallet_order') {
    $price = (int)($input['price'] ?? 0);
    $product = $input['productName'] ?? '';
    $package = $input['pkgName'] ?? '';
    $details = is_array($input['orderDetails']) ? json_encode($input['orderDetails']) : ($input['orderDetails'] ?? '');

    $stmt = $pdo->prepare("SELECT balance FROM users WHERE uid = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();

    if(!$user || (int)$user['balance'] < $price) {
        die(json_encode(['status' => 'error', 'message' => 'আপনার ওয়ালেটে পর্যাপ্ত ব্যালেন্স নেই!']));
    }

    // ব্যালেন্স কাটা
    $newBal = (int)$user['balance'] - $price;
    $pdo->prepare("UPDATE users SET balance = ? WHERE uid = ?")->execute([$newBal, $uid]);

    $stmt = $pdo->prepare("INSERT INTO orders (userId, userName, product, package, price, walletUsed, directPaid, orderDetails, paymentMethod, status) VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'Wallet', 'Pending')");
    $stmt->execute([$uid, $userName, $product, $package, $price, $price, $details]);

    handleCouponUsage($pdo, $appliedCoupon);

    echo json_encode(['status' => 'success', 'message' => 'অর্ডার সফলভাবে গ্রহণ করা হয়েছে!']);
    exit;
}

// ২. সরাসরি পেমেন্ট অর্ডার (Direct Payment)
elseif ($action === 'process_direct_order') {
    $price = (int)($input['payable'] ?? 0);
    $product = $input['productName'] ?? '';
    $package = $input['packageName'] ?? '';
    $method = strtoupper($input['method'] ?? 'DIRECT');
    $sender = $input['senderNumber'] ?? '';
    $trx = strtoupper(trim($input['trxID'] ?? ''));
    $details = is_array($input['orderDetails']) ? json_encode($input['orderDetails']) : ($input['orderDetails'] ?? '');

    $analysis = check_fraud_trx($pdo, $trx);
    if ($analysis !== "OK") die(json_encode(['status' => 'error', 'message' => $analysis]));

    $stmt = $pdo->prepare("INSERT INTO orders (userId, userName, product, package, price, walletUsed, directPaid, orderDetails, paymentMethod, senderNumber, trxID, status) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$uid, $userName, $product, $package, $price, $price, $details, $method, $sender, $trx]);

    handleCouponUsage($pdo, $appliedCoupon);

    echo json_encode(['status' => 'success', 'message' => 'অর্ডার সাবমিট হয়েছে, অ্যাডমিন ভেরিফাই করবে।']);
    exit;
}

// ৪. অ্যাড মানি (Add Money / Deposit)
elseif ($action === 'add_money') {
    $amount = (int)($input['amount'] ?? 0);
    $method = strtoupper($input['method'] ?? '');
    $sender = $input['senderNumber'] ?? '';
    $trx = strtoupper(trim($input['trxID'] ?? ''));

    if($amount < 10) die(json_encode(['status' => 'error', 'message' => 'সর্বনিম্ন ডিপোজিট ১০ টাকা।']));

    $analysis = check_fraud_trx($pdo, $trx);
    if ($analysis !== "OK") die(json_encode(['status' => 'error', 'message' => $analysis]));

    $stmt = $pdo->prepare("INSERT INTO deposit_requests (uid, userName, method, amount, senderNumber, trxID, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$uid, $userName, $method, $amount, $sender, $trx]);

    echo json_encode(['status' => 'success', 'message' => 'ডিপোজিট রিকোয়েস্ট সাবমিট হয়েছে!']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Action Not Allowed']);
?>