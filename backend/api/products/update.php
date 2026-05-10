<?php
// backend/api/products/update.php

require_once '../../config/headers.php';
require_once '../../config/database.php';
require_once 'Product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (
    empty($data['id'])       ||
    empty($data['name'])     ||
    empty($data['category']) ||
    !isset($data['price'])   ||
    !isset($data['stock'])
) {
    http_response_code(422);
    echo json_encode(['message' => 'ID, name, category, price and stock are required.']);
    exit;
}

$db      = (new Database())->getConnection();
$product = new Product($db);

$product->id       = (int)   $data['id'];
$product->name     = trim($data['name']);
$product->category = trim($data['category']);
$product->price    = (float) $data['price'];
$product->stock    = (int)   $data['stock'];
$product->status   = in_array($data['status'] ?? '', ['active','inactive']) ? $data['status'] : 'active';

if ($product->update()) {
    http_response_code(200);
    echo json_encode(['message' => 'Product updated.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Unable to update product.']);
}
