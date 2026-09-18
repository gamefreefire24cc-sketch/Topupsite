<?php
// FILE: api/v1/products.php
require_once '../../db.php';
header('Content-Type: application/json');

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
preg_match('/Bearer\s(\S+)/', $authHeader, $matches);
$apiToken = $matches[1] ?? '';

$stmt = $pdo->prepare("SELECT * FROM resellers WHERE api_token = ? AND status = 'Active'");
$stmt->execute([$apiToken]);
if (!$stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid API Token!']);
    exit;
}

// স্টক ও প্যাকেজ লিস্ট রিটার্ন করা
$packages = $pdo->query("SELECT id, category, name, price, tag FROM packages")->fetchAll();

echo json_encode([
    'status' => 'success',
    'data' => $packages
]);
exit();