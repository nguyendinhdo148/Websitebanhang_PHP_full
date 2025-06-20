<?php
class ProductModel
{
    private $conn;
    private $table_name = "product";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getProducts($searchTerm = '', $minPrice = null, $maxPrice = null)
    {
        $query = "
            SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category_name,
                   CASE 
                       WHEN p.image IS NOT NULL AND p.image != '' 
                       THEN CONCAT('/webbanhang/public/uploads/products/', p.image)
                       ELSE NULL
                   END as image_url
            FROM " . $this->table_name . " p
            LEFT JOIN category c ON p.category_id = c.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($searchTerm)) {
            $query .= " AND (p.name LIKE :searchTerm OR p.description LIKE :searchTerm)";
            $params[':searchTerm'] = "%$searchTerm%";
        }

        if ($minPrice !== null) {
            $query .= " AND p.price >= :minPrice";
            $params[':minPrice'] = $minPrice;
        }

        if ($maxPrice !== null) {
            $query .= " AND p.price <= :maxPrice";
            $params[':maxPrice'] = $maxPrice;
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getProductById($id)
    {
        $query = "
            SELECT p.*, c.name AS category_name
            FROM " . $this->table_name . " p
            LEFT JOIN category c ON p.category_id = c.id
            WHERE p.id = :id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getProductsByCategory($categoryId, $searchTerm = '', $minPrice = null, $maxPrice = null)
    {
        $query = "
            SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category_name,
                   CASE 
                       WHEN p.image IS NOT NULL AND p.image != '' 
                       THEN CONCAT('/webbanhang/public/uploads/products/', p.image)
                       ELSE NULL
                   END as image_url
            FROM " . $this->table_name . " p
            LEFT JOIN category c ON p.category_id = c.id
            WHERE p.category_id = :category_id
        ";

        $params = [':category_id' => $categoryId];

        if (!empty($searchTerm)) {
            $query .= " AND (p.name LIKE :searchTerm OR p.description LIKE :searchTerm)";
            $params[':searchTerm'] = "%$searchTerm%";
        }

        if ($minPrice !== null) {
            $query .= " AND p.price >= :minPrice";
            $params[':minPrice'] = $minPrice;
        }

        if ($maxPrice !== null) {
            $query .= " AND p.price <= :maxPrice";
            $params[':maxPrice'] = $maxPrice;
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function addProduct($name, $description, $price, $category_id, $image)
    {
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        }

        if (empty($description)) {
            $errors['description'] = 'Mô tả không được để trống';
        }

        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = 'Giá sản phẩm không hợp lệ';
        }

        if (!is_numeric($category_id)) {
            $errors['category_id'] = 'ID danh mục không hợp lệ';
        }

        if (count($errors) > 0) {
            return $errors;
        }

        $query = "
            INSERT INTO " . $this->table_name . " (name, description, price, category_id, image)
            VALUES (:name, :description, :price, :category_id, :image)
        ";
        $stmt = $this->conn->prepare($query);

        // Clean data
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = floatval($price);
        $category_id = intval($category_id);
        $image = htmlspecialchars(strip_tags($image));

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image);

        return $stmt->execute();
    }

    public function updateProduct($id, $name, $description, $price, $category_id, $image = null)
    {
        if ($image) {
            $query = "
                UPDATE " . $this->table_name . "
                SET name = :name, description = :description, price = :price, category_id = :category_id, image = :image
                WHERE id = :id
            ";
        } else {
            $query = "
                UPDATE " . $this->table_name . "
                SET name = :name, description = :description, price = :price, category_id = :category_id
                WHERE id = :id
            ";
        }

        $stmt = $this->conn->prepare($query);

        // Clean data
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = floatval($price);
        $category_id = intval($category_id);
        $id = intval($id);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);

        if ($image) {
            $image = htmlspecialchars(strip_tags($image));
            $stmt->bindParam(':image', $image);
        }

        return $stmt->execute();
    }

    public function deleteProduct($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $id = intval($id);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
