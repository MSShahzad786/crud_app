<?php
// backend/api/products/Product.php

class Product {
    private PDO $db;

    // Table columns
    public int    $id;
    public string $name;
    public string $category;
    public float  $price;
    public int    $stock;
    public string $status;
    public string $created_at;
    public string $updated_at;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // ── READ (with search, filter, sort, pagination) ──────────────────────────
    public function read(
        string $search   = '',
        string $category = '',
        string $status   = '',
        string $sort_col = 'id',
        string $sort_dir = 'ASC',
        int    $page     = 1,
        int    $per_page = 10
    ): array {
        $allowed_cols = ['id','name','category','price','stock','status','created_at'];
        $sort_col     = in_array($sort_col, $allowed_cols, true) ? $sort_col : 'id';
        $sort_dir     = strtoupper($sort_dir) === 'DESC' ? 'DESC' : 'ASC';

        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $conditions[] = '(name LIKE :search OR category LIKE :search2)';
            $params[':search']  = "%{$search}%";
            $params[':search2'] = "%{$search}%";
        }
        if ($category !== '') {
            $conditions[] = 'category = :category';
            $params[':category'] = $category;
        }
        if ($status !== '') {
            $conditions[] = 'status = :status';
            $params[':status'] = $status;
        }

        $where  = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $offset = ($page - 1) * $per_page;

        // Total count
        $countSql  = "SELECT COUNT(*) FROM products {$where}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Paginated rows
        $sql  = "SELECT * FROM products {$where} ORDER BY {$sort_col} {$sort_dir} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'       => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $per_page,
            'last_page'  => (int) ceil($total / $per_page),
        ];
    }

    // ── READ ONE ──────────────────────────────────────────────────────────────
    public function readOne(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ── CATEGORIES ────────────────────────────────────────────────────────────
    public function getCategories(): array {
        $stmt = $this->db->query('SELECT DISTINCT category FROM products ORDER BY category');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ── CREATE ────────────────────────────────────────────────────────────────
    public function create(): bool {
        $sql = 'INSERT INTO products (name, category, price, stock, status)
                VALUES (:name, :category, :price, :stock, :status)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'     => htmlspecialchars(strip_tags($this->name)),
            ':category' => htmlspecialchars(strip_tags($this->category)),
            ':price'    => $this->price,
            ':stock'    => $this->stock,
            ':status'   => $this->status,
        ]);
        $this->id = (int) $this->db->lastInsertId();
        return true;
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────
    public function update(): bool {
        $sql = 'UPDATE products
                SET name=:name, category=:category, price=:price, stock=:stock, status=:status
                WHERE id=:id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'     => htmlspecialchars(strip_tags($this->name)),
            ':category' => htmlspecialchars(strip_tags($this->category)),
            ':price'    => $this->price,
            ':stock'    => $this->stock,
            ':status'   => $this->status,
            ':id'       => $this->id,
        ]);
        return $stmt->rowCount() > 0;
    }

    // ── DELETE ────────────────────────────────────────────────────────────────
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
