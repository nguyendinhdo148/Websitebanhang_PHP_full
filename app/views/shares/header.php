<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .product-image {
            max-width: 100px;
            height: auto;
        }

        .user-greeting {
            margin-right: 15px;
            font-weight: 500;
        }

        .admin-badge {
            font-size: 0.7rem;
            vertical-align: middle;
        }
        
        .nav-item {
            position: relative;
        }
        
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.6rem;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/webbanhang/Product/list">Quản lý sản phẩm</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/webbanhang/Product/list">Danh sách sản phẩm</a>
                </li>

                <!-- Hiển thị khi đã đăng nhập (session hoặc JWT) -->
                <li class="nav-item" id="nav-add-product" style="display: none;">
                    <a class="nav-link" href="/webbanhang/Product/add">Thêm sản phẩm</a>
                </li>
                <li class="nav-item" id="nav-categories" style="display: none;">
                    <a class="nav-link" href="/webbanhang/Category/">Quản lý danh mục</a>
                </li>
                <li class="nav-item" id="nav-users" style="display: none;">
                    <a class="nav-link" href="/webbanhang/account/list">
                        <i class="fas fa-users"></i> Quản lý người dùng
                    </a>
                </li>

                <li class="nav-item" id="nav-cart" style="display: none;">
                    <a class="nav-link" href="/webbanhang/Product/cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge badge-danger cart-badge" id="cart-quantity">0</span>
                    </a>
                </li>
            </ul>

            <div class="navbar-nav" id="auth-section">
                <!-- Phần này sẽ được thay thế bằng JavaScript -->
                <a class="nav-link" href="/webbanhang/account/login" id="nav-login">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </a>
                <a class="nav-link" href="/webbanhang/account/register" id="nav-register">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </a>
                
                <div class="nav-item d-flex align-items-center" id="user-info" style="display: none;">
                    <span class="user-greeting" id="username-display"></span>
                    <a href="/webbanhang/account/profile" class="btn btn-outline-primary btn-sm mr-2">
                        <i class="fas fa-user-circle"></i> Hồ sơ
                    </a>
                    <a href="#" class="btn btn-outline-danger btn-sm" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">

<script>
// Hàm kiểm tra đăng nhập
function checkAuth() {
    const token = localStorage.getItem('jwtToken');
    const hasSession = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;
    
    if (token || hasSession) {
        // Ẩn nút đăng nhập/đăng ký
        document.getElementById('nav-login').style.display = 'none';
        document.getElementById('nav-register').style.display = 'none';
        
        // Hiển thị thông tin người dùng
        document.getElementById('user-info').style.display = 'flex';
        document.getElementById('nav-cart').style.display = 'block';
        
        // Lấy thông tin user
        if (hasSession) {
            // Sử dụng session
            const username = '<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['username']) : ''; ?>';
            const role = '<?php echo isset($_SESSION['user']) ? $_SESSION['user']['role'] : ''; ?>';
            
            document.getElementById('username-display').innerHTML = `Xin chào, ${username}${role === 'admin' ? ' <span class="badge badge-primary admin-badge">ADMIN</span>' : ''}`;
            
            if (role === 'admin') {
                document.getElementById('nav-add-product').style.display = 'block';
                document.getElementById('nav-categories').style.display = 'block';
                document.getElementById('nav-users').style.display = 'block';
            }
            
            // Cập nhật số lượng giỏ hàng từ session
            updateCartQuantity();
        } else if (token) {
            // Sử dụng JWT
            fetch('/webbanhang/account/me', {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.username) {
                    document.getElementById('username-display').innerHTML = `Xin chào, ${data.username}${data.role === 'admin' ? ' <span class="badge badge-primary admin-badge">ADMIN</span>' : ''}`;
                    
                    if (data.role === 'admin') {
                        document.getElementById('nav-add-product').style.display = 'block';
                        document.getElementById('nav-categories').style.display = 'block';
                        document.getElementById('nav-users').style.display = 'block';
                    }
                    
                    // Lấy số lượng giỏ hàng từ API
                    fetchCartQuantity(data.id);
                }
            });
        }
    }
}

// Hàm cập nhật số lượng giỏ hàng (cho session)
function updateCartQuantity() {
    <?php if (isset($_SESSION['user'])): ?>
        const quantity = <?php 
            if (isset($_SESSION['user'])) {
                require_once('app/config/database.php');
                require_once('app/models/CartModel.php');

                $db = (new Database())->getConnection();
                $cartModel = new CartModel($db);
                $userId = $_SESSION['user']['id'];
                $cart = $cartModel->getOrCreateCart($userId);
                $cartItems = $cartModel->getCartItems($cart['id']);

                $totalQuantity = 0;
                foreach ($cartItems as $item) {
                    $totalQuantity += $item['quantity'];
                }
                echo $totalQuantity;
            } else {
                echo '0';
            }
        ?>;
        document.getElementById('cart-quantity').textContent = quantity;
    <?php endif; ?>
}

// Hàm lấy số lượng giỏ hàng từ API (cho JWT)
function fetchCartQuantity(userId) {
    fetch(`/webbanhang/api/cart/quantity?userId=${userId}`, {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('jwtToken')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.quantity !== undefined) {
            document.getElementById('cart-quantity').textContent = data.quantity;
        }
    });
}

// Hàm đăng xuất
function logout() {
    // Xóa JWT token nếu có
    localStorage.removeItem('jwtToken');
    
    // Gọi API logout nếu sử dụng JWT
    fetch('/webbanhang/account/logout', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('jwtToken') || '',
            'Content-Type': 'application/json'
        }
    })
    .then(() => {
        // Chuyển hướng về trang login
        window.location.href = '/webbanhang/account/login';
    });
}

// Kiểm tra auth khi trang được tải
document.addEventListener("DOMContentLoaded", function() {
    checkAuth();
});
</script>