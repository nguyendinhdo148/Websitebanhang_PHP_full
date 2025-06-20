<?php include __DIR__ . '/../shares/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6 font-weight-bold text-primary">Quản lý Danh mục</h1>
        <a href="/webbanhang/Category/add" class="btn btn-success btn-lg" id="add-category-btn">
            <i class="fas fa-plus-circle mr-2"></i>Thêm mới
        </a>
    </div>

    <div id="category-list" class="row"></div>
    <div id="message"></div>
    <div id="no-category-message" class="alert alert-info text-center py-4 d-none">
        <i class="fas fa-info-circle fa-3x mb-3 text-info"></i>
        <h3 class="alert-heading">Chưa có danh mục nào</h3>
        <p>Hãy bắt đầu bằng cách thêm danh mục mới</p>
        <a href="/webbanhang/Category/add" class="btn btn-info mt-2">
            <i class="fas fa-plus mr-1"></i> Thêm danh mục đầu tiên
        </a>
    </div>
</div>

<script>
function loadCategories() {
    fetch('/webbanhang/api/category')
        .then(res => res.json())
        .then(categories => {
            console.log('API categories:', categories); // Log dữ liệu trả về từ API
            const list = document.getElementById('category-list');
            const noMsg = document.getElementById('no-category-message');
            list.innerHTML = '';
            if (!categories.length) {
                noMsg.classList.remove('d-none');
                return;
            }
            noMsg.classList.add('d-none');
            categories.forEach(category => {
                list.innerHTML += `
                <div class="col-md-4 mb-4">
                    <div class="card category-card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h3 class="card-title font-weight-bold text-dark mb-3">
                                    <a href="/webbanhang/Category/productsByCategory/${category.id}" class="text-decoration-none text-dark">
                                        <i class="fas fa-folder mr-2"></i>
                                        ${category.name}
                                    </a>
                                </h3>
                                <span class="badge badge-pill badge-primary">ID: ${category.id}</span>
                            </div>
                            <div class="category-actions mt-3">
                                <a href="/webbanhang/Category/edit/${category.id}" class="btn btn-outline-warning btn-sm mr-2">
                                    <i class="fas fa-edit mr-1"></i>Sửa
                                </a>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteCategory(${category.id})">
                                    <i class="fas fa-trash-alt mr-1"></i>Xóa
                                </button>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 text-right">
                            <small class="text-muted">Cập nhật lần cuối: ${new Date().toLocaleDateString('vi-VN')}</small>
                        </div>
                    </div>
                </div>
                `;
            });
        });
}

function deleteCategory(id) {
    if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
        fetch(`/webbanhang/api/category/${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(data => {
                console.log('API delete category response:', data); // log dữ liệu trả về
                const msg = document.getElementById('message');
                if (data.message === 'Category deleted successfully') {
                    msg.innerHTML = '<div class="alert alert-success">Xóa danh mục thành công!</div>';
                    loadCategories();
                } else {
                    msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Không thể xóa danh mục'}</div>`;
                }
            })
            .catch(() => {
                document.getElementById('message').innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra!</div>';
            });
    }
}

document.addEventListener('DOMContentLoaded', loadCategories);
</script>

<style>
    .category-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: rgba(0,123,255,0.2);
    }
    .category-card .card-title a:hover {
        color: #007bff !important;
        text-decoration: underline !important;
    }
    .category-actions .btn {
        transition: all 0.2s ease;
    }
</style>

<?php include __DIR__ . '/../shares/footer.php'; ?>
