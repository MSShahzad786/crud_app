<?php
// backend/api/products/read_one.php

require_once '../../config/headers.php';
require_once '../../config/database.php';
require_once 'Product.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id < 1) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid ID.']);
    exit;
}

$db      = (new Database())->getConnection();
$product = new Product($db);
$row     = $product->readOne($id);

if ($row) {
    http_response_code(200);
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(['message' => 'Product not found.']);
}
