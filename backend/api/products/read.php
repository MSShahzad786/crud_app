<?php
// backend/api/products/read.php

require_once '../../config/headers.php';
require_once '../../config/database.php';
require_once 'Product.php';

$db      = (new Database())->getConnection();
$product = new Product($db);

$search   = $_GET['search']   ?? '';
$category = $_GET['category'] ?? '';
$status   = $_GET['status']   ?? '';
$sort_col = $_GET['sort_col'] ?? 'id';
$sort_dir = $_GET['sort_dir'] ?? 'ASC';
$page     = max(1, (int)($_GET['page']     ?? 1));
$per_page = max(1, min(100, (int)($_GET['per_page'] ?? 10)));

$result = $product->read($search, $category, $status, $sort_col, $sort_dir, $page, $per_page);

// Also return category list for the filter dropdown
$result['categories'] = $product->getCategories();

http_response_code(200);
echo json_encode($result);
