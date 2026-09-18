<?php
// FILE: create_token.php
header('Content-Type: application/json');
include 'jwt_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $data['exp'] = time() + 600; // ১০ মিনিট মেয়াদ
    $token = JWT::encode($data);
    echo json_encode(['status' => 'success', 'token' => $token]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>