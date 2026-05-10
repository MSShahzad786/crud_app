<?php
// backend/api/products/create.php

require_once '../../config/headers.php';
require_once '../../config/database.php';
require_once 'Product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (
    empty($data['name'])     ||
    empty($data['category']) ||
    !isset($data['price'])   ||
    !isset($data['stock'])
) {
    http_response_code(422);
    echo json_encode(['message' => 'Name, category, price and stock are required.']);
    exit;
}

$db      = (new Database())->getConnection();
$product = new Product($db);

$product->name     = trim($data['name']);
$product->category = trim($data['category']);
$product->price    = (float) $data['price'];
$product->stock    = (int)   $data['stock'];
$product->status   = in_array($data['status'] ?? '', ['active','inactive']) ? $data['status'] : 'active';

if ($product->create()) {
    http_response_code(201);
    echo json_encode(['message' => 'Product created.', 'id' => $product->id]);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Unable to create product.']);
}
