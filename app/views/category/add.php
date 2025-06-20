<?php include __DIR__ . '/../shares/header.php'; ?>
<h1>Thêm danh mục mới</h1>
<div id="message"></div>
<form id="add-category-form">
    <div class="form-group">
        <label for="name">Tên danh mục:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Thêm danh mục</button>
</form>
<a href="/webbanhang/Category" class="btn btn-secondary mt-2">Quay lại danh sách danh mục</a>
<script>
document.getElementById('add-category-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const name = document.getElementById('name').value;
    fetch('/webbanhang/api/category', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name })
    })
    .then(res => res.json())
    .then(data => {
        console.log('API add category response:', data); // log dữ liệu trả về
        const msg = document.getElementById('message');
        if (data.message === 'Category created successfully') {
            msg.innerHTML = '<div class="alert alert-success">Thêm danh mục thành công!</div>';
            setTimeout(() => window.location.href = '/webbanhang/Category', 1000);
        } else {
            msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Thêm danh mục thất bại'}</div>`;
        }
    })
    .catch(() => {
        document.getElementById('message').innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra!</div>';
    });
});
</script>
<?php include __DIR__ . '/../shares/footer.php'; ?>
