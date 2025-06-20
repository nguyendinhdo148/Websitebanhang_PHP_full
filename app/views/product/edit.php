<?php include __DIR__ . '/../shares/header.php'; ?>

<h1>Sửa sản phẩm</h1>
<div id="message"></div>
<form id="edit-product-form">
    <input type="hidden" id="id" name="id" value="<?php echo $product->id; ?>">
    <div class="form-group">
        <label for="name">Tên sản phẩm:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" class="form-control" required></textarea>
    </div>
    <div class="form-group">
        <label for="price">Giá:</label>
        <input type="number" id="price" name="price" class="form-control" step="0.01" required>
    </div>
    <div class="form-group">
        <label for="category_id">Danh mục:</label>
        <select id="category_id" name="category_id" class="form-control" required>
            <!-- Danh mục sẽ được load từ API -->
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
</form>
<a href="/webbanhang/Product" class="btn btn-secondary mt-2">Quay lại danh sách sản phẩm</a>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const productId = <?php echo $product->id; ?>;
    // Load product data
    fetch(`/webbanhang/api/product/${productId}`)
        .then(res => res.json())
        .then(data => {
            console.log('API get product response:', data); // log dữ liệu trả về
            document.getElementById('id').value = data.id;
            document.getElementById('name').value = data.name;
            document.getElementById('description').value = data.description;
            document.getElementById('price').value = data.price;
            // category_id sẽ được set sau khi load danh mục
        });

    // Load categories
    fetch('/webbanhang/api/category')
        .then(res => res.json())
        .then(data => {
            console.log('API get categories response:', data); // log dữ liệu trả về
            const select = document.getElementById('category_id');
            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
            // Set selected category
            fetch(`/webbanhang/api/product/${productId}`)
                .then(res => res.json())
                .then(product => {
                    console.log('API get product for category select:', product); // log dữ liệu trả về
                    select.value = product.category_id;
                });
        });

    document.getElementById('edit-product-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });
        fetch(`/webbanhang/api/product/${jsonData.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(jsonData)
        })
        .then(res => res.json())
        .then(data => {
            console.log('API edit product response:', data); // log dữ liệu trả về
            const msg = document.getElementById('message');
            if (data.message === 'Product updated successfully') {
                msg.innerHTML = '<div class="alert alert-success">Cập nhật sản phẩm thành công!</div>';
                setTimeout(() => window.location.href = '/webbanhang/Product', 1000);
            } else {
                msg.innerHTML = `<div class="alert alert-danger">${data.errors ? Object.values(data.errors).join('<br>') : 'Cập nhật sản phẩm thất bại'}</div>`;
            }
        })
        .catch(error => {
            console.error('API edit product error:', error);
        });
    });
});
</script>

<?php include __DIR__ . '/../shares/footer.php'; ?>