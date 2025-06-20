<?php include 'app/views/shares/header.php'; ?>

<div class="container py-5">
    <!-- Banner Section -->
    <div id="bannerCarousel" class="carousel slide mb-5" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://template.canva.com/EAFqHtD5eto/2/0/800w-ShkFwAq8YDk.jpg" class="d-block w-100 rounded shadow-sm" alt="Banner 1">
            </div>
            <div class="carousel-item">
                <img src="https://template.canva.com/EAGLd7U_4sg/8/0/800w-GBiKE03PQ44.jpg" class="d-block w-100 rounded shadow-sm" alt="Banner 2">
            </div>
            <div class="carousel-item">
                <img src="https://template.canva.com/EAGQmlnTUhg/1/0/800w-uwqoOAelBwc.jpg" class="d-block w-100 rounded shadow-sm" alt="Banner 3">
            </div>
            <div class="carousel-item">
                <img src="https://template.canva.com/EAFjuLtNtvc/2/0/800w-JClygUx_-Ow.jpg" class="d-block w-100 rounded shadow-sm" alt="Banner 4">
            </div>
        </div>
        <a class="carousel-control-prev" href="#bannerCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#bannerCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
        <h1 class="display-5 font-weight-bold text-primary mb-3 mb-md-0">Danh Sách Sản Phẩm</h1>

        <div class="d-flex align-items-center">
            <select id="category-filter" class="form-control mr-2">
                <option value="">-- Chọn danh mục --</option>
                <!-- Categories will be loaded via API -->
            </select>

            <div id="add-product-button">
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a href="/webbanhang/product/add" class="btn btn-success btn-lg">
                        <i class="fas fa-plus-circle mr-2"></i>Thêm sản phẩm
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="products-container" class="row">
        <!-- Products will be loaded here via API -->
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    checkAuthAndLoadData();
    
    document.getElementById('category-filter').addEventListener('change', function() {
        loadProducts(this.value);
    });
});

function checkAuthAndLoadData() {
    const token = localStorage.getItem('jwtToken');
    const hasSession = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;
    
    if (!token && !hasSession) {
        alert('Vui lòng đăng nhập');
        window.location.href = '/webbanhang/account/login';
        return;
    }
    
    // Nếu sử dụng JWT, kiểm tra token hợp lệ
    if (token && !hasSession) {
        fetch('/webbanhang/account/verify-token', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            if (!response.ok) {
                localStorage.removeItem('jwtToken');
                window.location.href = '/webbanhang/account/login';
                throw new Error('Token không hợp lệ');
            }
            return response.json();
        })
        .then(data => {
            // Nếu là admin thì hiển thị nút thêm sản phẩm
            if (data.role === 'admin') {
                document.getElementById('add-product-button').innerHTML = `
                    <a href="/webbanhang/product/add" class="btn btn-success btn-lg">
                        <i class="fas fa-plus-circle mr-2"></i>Thêm sản phẩm
                    </a>
                `;
            }
            loadProducts();
            loadCategories();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    } else {
        // Sử dụng session
        loadProducts();
        loadCategories();
    }
}

function loadCategories() {
    const token = localStorage.getItem('jwtToken');
    
    fetch('/webbanhang/api/category', {
        headers: token ? {
            'Authorization': 'Bearer ' + token
        } : {}
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(categories => {
        const select = document.getElementById('category-filter');
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            select.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading categories:', error);
        alert('Không thể tải danh mục sản phẩm. Vui lòng thử lại sau.');
    });
}

function loadProducts(categoryId = '') {
    const token = localStorage.getItem('jwtToken');
    let url = '/webbanhang/api/product';
    if (categoryId) {
        url += `?category_id=${categoryId}`;
    }

    const container = document.getElementById('products-container');
    container.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Đang tải...</span></div></div>';

    fetch(url, {
        headers: token ? {
            'Authorization': 'Bearer ' + token
        } : {}
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(products => {
        container.innerHTML = products.length ? '' : createEmptyMessage();
        products.forEach(product => {
            container.innerHTML += createProductCard(product);
        });
    })
    .catch(error => {
        console.error('Error loading products:', error);
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Không thể tải sản phẩm. Vui lòng thử lại sau.<br>
                    <span style="font-size:0.9em;color:#888;">${error.message}</span>
                </div>
            </div>
        `;
    });
}

function createProductCard(product) {
    const token = localStorage.getItem('jwtToken');
    const isAdmin = <?php echo (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') ? 'true' : 'false'; ?> || 
                   (token ? /* Kiểm tra role từ token nếu cần */ false : false);
    
    let imgSrc = product.image_url && product.image_url !== '/webbanhang/public/uploads/' 
        ? product.image_url 
        : (product.image ? `/webbanhang/public/uploads/${product.image}` : 'https://placehold.co/300x200?text=No+Image');
    
    return `
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card product-card h-100 shadow-sm border-0">
                <div class="card-img-top product-image-container p-3">
                    <img src="${imgSrc}"
                         class="product-image img-fluid"
                         alt="${product.name}"
                         onerror="this.src='https://placehold.co/300x200?text=No+Image'">
                </div>
                
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h3 class="card-title h5 font-weight-bold mb-0 flex-grow-1">
                            <a href="/webbanhang/Product/detail/${product.id}" class="text-decoration-none text-dark">
                                ${product.name}
                            </a>
                        </h3>
                        <span class="badge badge-pill badge-info ml-2">ID: ${product.id}</span>
                    </div>
                    
                    <div class="mb-2">
                        <span class="badge badge-secondary">
                            <i class="fas fa-tag mr-1"></i>
                            ${product.category_name}
                        </span>
                    </div>
                    
                    <div class="product-description mb-3">
                        ${product.description.length > 120 ? 
                          product.description.substring(0, 120) + '...' : 
                          product.description}
                    </div>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-danger font-weight-bold mb-0">
                                ${Number(product.price).toLocaleString('vi-VN')} VND
                            </h5>
                            ${isAdmin ? createAdminActions(product.id) : ''}
                        </div>

                        <div class="d-flex">
                            <a href="/webbanhang/Product/detail/${product.id}" 
                               class="btn btn-info btn-sm flex-grow-1 mr-2">
                               <i class="fas fa-info-circle mr-1"></i> Chi tiết
                            </a>
                            <button onclick="addToCart(${product.id})" 
                               class="btn btn-primary btn-sm flex-grow-1">
                               <i class="fas fa-cart-plus mr-1"></i> Mua ngay
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createAdminActions(productId) {
    return `
        <div class="product-actions">
            <a href="/webbanhang/Product/edit/${productId}" 
               class="btn btn-sm btn-outline-warning mr-1"
               title="Sửa sản phẩm">
               <i class="fas fa-edit"></i>
            </a>
            <button onclick="deleteProduct(${productId})"
                    class="btn btn-sm btn-outline-danger"
                    title="Xóa sản phẩm">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    `;
}

function createEmptyMessage() {
    const token = localStorage.getItem('jwtToken');
    const isAdmin = <?php echo (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') ? 'true' : 'false'; ?> || 
                   (token ? /* Kiểm tra role từ token nếu cần */ false : false);
    
    return `
        <div class="col-12">
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-box-open fa-3x mb-3 text-info"></i>
                <h3 class="alert-heading">Không có sản phẩm nào</h3>
                <p>Hãy bắt đầu bằng cách thêm sản phẩm mới</p>
                ${isAdmin ? `
                    <a href="/webbanhang/product/add" class="btn btn-info mt-2">
                        <i class="fas fa-plus mr-1"></i> Thêm sản phẩm đầu tiên
                    </a>
                ` : ''}
            </div>
        </div>
    `;
}

function deleteProduct(id) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        const token = localStorage.getItem('jwtToken');
        
        fetch(`/webbanhang/api/product/${id}`, {
            method: 'DELETE',
            headers: token ? {
                'Authorization': 'Bearer ' + token
            } : {}
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.message === 'Product deleted successfully') {
                loadProducts(document.getElementById('category-filter').value);
            } else {
                alert('Xóa sản phẩm thất bại');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Xóa sản phẩm thất bại: ' + error.message);
        });
    }
}

function addToCart(productId) {
    const token = localStorage.getItem('jwtToken');
    const hasSession = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;
    
    if (!token && !hasSession) {
        alert('Vui lòng đăng nhập để thêm vào giỏ hàng');
        window.location.href = '/webbanhang/account/login';
        return;
    }
    
    const url = '/webbanhang/api/cart/add';
    const method = 'POST';
    const headers = {
        'Content-Type': 'application/json'
    };
    
    if (token) {
        headers['Authorization'] = 'Bearer ' + token;
    }
    
    const body = JSON.stringify({
        product_id: productId,
        quantity: 1
    });
    
    fetch(url, {
        method: method,
        headers: headers,
        body: body
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        alert('Đã thêm sản phẩm vào giỏ hàng');
        updateCartUI();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Thêm vào giỏ hàng thất bại: ' + error.message);
    });
}

function updateCartUI() {
    // Cập nhật số lượng trên icon giỏ hàng trong header
    const cartBadge = document.querySelector('#nav-cart .cart-badge');
    if (cartBadge) {
        // Gọi API hoặc tính toán lại số lượng
        // Ví dụ đơn giản:
        const current = parseInt(cartBadge.textContent) || 0;
        cartBadge.textContent = current + 1;
    }
}
</script>

<style>
    .carousel img {
        max-height: 300px;
        object-fit: cover;
    }

    .product-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .product-image-container {
        height: 220px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }

    .product-image {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .product-card:hover .product-image {
        transform: scale(1.05);
    }

    .product-description {
        color: #6c757d;
        font-size: 0.9rem;
        line-height: 1.5;
        min-height: 60px;
    }

    .product-actions .btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    
    .card-body {
        padding: 1.25rem;
    }
</style>

<?php include 'app/views/shares/footer.php'; ?>