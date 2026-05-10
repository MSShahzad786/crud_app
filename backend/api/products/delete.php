<?php
// backend/api/products/delete.php

require_once '../../config/headers.php';
require_once '../../config/database.php';
require_once 'Product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id   = isset($data['id']) ? (int) $data['id'] : 0;

if ($id < 1) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid ID.']);
    exit;
}

$db      = (new Database())->getConnection();
$product = new Product($db);

if ($product->delete($id)) {
    http_response_code(200);
    echo json_encode(['message' => 'Product deleted.']);
} else {
    http_response_code(404);
    echo json_encode(['message' => 'Product not found.']);
}
