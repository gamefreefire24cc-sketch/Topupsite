<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// FILE: api/v1/order.php
require_once '../../db.php';
header('Content-Type: application/json');

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Missing API Token!']);
    exit;
}

$apiToken = $matches[1];

// রিসেলার ভ্যালিডেশন
$stmt = $pdo->prepare("SELECT * FROM resellers WHERE api_token = ? AND status = 'Active'");
$stmt->execute([$apiToken]);
$reseller = $stmt->fetch();

if (!$reseller) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Token!']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$packageId = (int)($input['package_id'] ?? 0);
$gameUid = trim($input['game_uid'] ?? '');
$customerRef = trim($input['customer_ref'] ?? 'API_ORDER');
$paymentMode = trim($input['payment_mode'] ?? 'wallet'); // 'wallet' অথবা 'main_gateway'
$sellingPrice = (int)($input['selling_price'] ?? 0); // রিসেলার তার কাস্টমারের কাছে কত টাকায় বিক্রি করেছে

if ($packageId <= 0 || empty($gameUid)) {
    echo json_encode(['status' => 'error', 'message' => 'Package ID and Game UID are required!']);
    exit;
}

// প্যাকেজ ও এর পাইকারি দাম (Wholesale Price) বের করা
$pkgStmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$pkgStmt->execute([$packageId]);
$package = $pkgStmt->fetch();

if (!$package) {
    echo json_encode(['status' => 'error', 'message' => 'Package not found!']);
    exit;
}

$wholesalePrice = (int)$package['price'];

// ----------------------------------------------------
// কেস ১: রিসেলার তার ওয়ালেট থেকে টাকা কেটে অর্ডার করছে
// ----------------------------------------------------
if ($paymentMode === 'wallet') {
    if ((int)$reseller['wallet_balance'] < $wholesalePrice) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient wallet balance! Please add money to your API wallet.']);
        exit;
    }

    // রিসেলারের ওয়ালেট থেকে পাইকারি দাম কেটে নেওয়া হলো
    $newBalance = (int)$reseller['wallet_balance'] - $wholesalePrice;
    $pdo->prepare("UPDATE resellers SET wallet_balance = ? WHERE uid = ?")->execute([$newBalance, $reseller['uid']]);

    $paidAmount = $wholesalePrice;
} 
// ----------------------------------------------------
// কেস ২: কাস্টমার আপনার মেইন গেটওয়েতে পেমেন্ট করেছে
// ----------------------------------------------------
elseif ($paymentMode === 'main_gateway') {
    // যেহেতু কাস্টমার আপনার গেটওয়েতে পেমেন্ট করেছে, তাই রিসেলারের ওয়ালেট থেকে টাকা কাটবে না।
    // বরং রিসেলার যে দামে বিক্রি করেছে ($sellingPrice) সেখান থেকে পাইকারি দাম বাদ দিয়ে বা পুরো বিক্রিত টাকা রিসেলারের ওয়ালেটে অ্যাড/যোগ হয়ে যাবে (আপনার বিজনেস মডেল অনুযায়ী)।
    // সহজ কথায়: আপনার সাইটে পেমেন্ট হওয়ায় রিসেলারের ব্যালেন্স পজিটিভ হবে (ব্যালেন্স বাড়বে)।
    
    $profitOrSale = $sellingPrice > 0 ? $sellingPrice : $wholesalePrice;
    $newBalance = (int)$reseller['wallet_balance'] + $profitOrSale;
    
    $pdo->prepare("UPDATE resellers SET wallet_balance = ? WHERE uid = ?")->execute([$newBalance, $reseller['uid']]);

    $paidAmount = $profitOrSale;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid payment mode!']);
    exit;
}

// মূল অর্ডার টেবিলে অর্ডার এন্ট্রি করা
$orderStmt = $pdo->prepare("INSERT INTO orders (userId, userName, product, package, price, walletUsed, directPaid, orderDetails, paymentMethod, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Reseller API', 'Pending')");
$orderStmt->execute([
    $reseller['uid'],
    "Reseller: " . $reseller['name'],
    $package['category'],
    $package['name'],
    $wholesalePrice,
    ($paymentMode === 'wallet' ? $wholesalePrice : 0),
    ($paymentMode === 'main_gateway' ? $paidAmount : 0),
    json_encode(['type' => 'UID', 'uid' => $gameUid, 'ref' => $customerRef, 'mode' => $paymentMode]),
]);

$orderId = $pdo->lastInsertId();

echo json_encode([
    'status' => 'success',
    'message' => 'Reseller order placed successfully!',
    'order_id' => $orderId,
    'wallet_balance' => $newBalance
]);
exit();