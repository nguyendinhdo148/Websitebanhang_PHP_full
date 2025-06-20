<?php
require_once('app/config/database.php');
require_once('app/models/CategoryModel.php');

class CategoryApiController
{
    private $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
        header('Content-Type: application/json');
    }

    // Lấy tất cả danh mục (GET /api/category)
    public function index()
    {
        $categories = $this->categoryModel->getCategories();
        echo json_encode($categories);
    }

    // Lấy 1 danh mục theo ID (GET /api/category/{id})
    public function show($id)
    {
        $category = $this->categoryModel->getCategoryById($id);
        if ($category) {
            echo json_encode($category);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Category not found']);
        }
    }

    // Thêm danh mục mới (POST /api/category)
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'] ?? '';

        $result = $this->categoryModel->addCategory($name);
        if ($result === true) {
            http_response_code(201);
            echo json_encode(['message' => 'Category created successfully']);
        } else {
            http_response_code(400);
            echo json_encode(is_array($result) ? $result : ['message' => 'Failed to create category']);
        }
    }

    // Cập nhật danh mục (PUT /api/category/{id})
    public function update($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'] ?? '';

        if ($this->categoryModel->updateCategory($id, $name)) {
            echo json_encode(['message' => 'Category updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Failed to update category']);
        }
    }

    // Xoá danh mục (DELETE /api/category/{id})
    public function destroy($id)
    {
        if ($this->categoryModel->deleteCategory($id)) {
            echo json_encode(['message' => 'Category deleted successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Failed to delete category']);
        }
    }
}
