<?php include __DIR__ . '/../shares/header.php'; ?>
<h1>Sửa danh mục</h1>
<div id="message"></div>
<form id="edit-category-form">
    <input type="hidden" id="id" name="id" value="<?php echo $category->id; ?>">
    <div class="form-group">
        <label for="name">Tên danh mục:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
</form>
<a href="/webbanhang/Category" class="btn btn-secondary mt-2">Quay lại danh sách danh mục</a>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const id = document.getElementById('id').value;
    // Lấy dữ liệu danh mục từ API
    fetch(`/webbanhang/api/category/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data && data.name) {
                document.getElementById('name').value = data.name;
            }
        });

    document.getElementById('edit-category-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('name').value;
        fetch(`/webbanhang/api/category/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name })
        })
        .then(res => res.json())
        .then(data => {
            console.log('API edit category response:', data); // log dữ liệu trả về
            const msg = document.getElementById('message');
            if (data.message === 'Category updated successfully') {
                msg.innerHTML = '<div class="alert alert-success">Cập nhật thành công!</div>';
                setTimeout(() => window.location.href = '/webbanhang/Category', 1000);
            } else {
                msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Cập nhật danh mục thất bại'}</div>`;
            }
        })
        .catch(() => {
            document.getElementById('message').innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra!</div>';
        });
    });
});
</script>
<?php include __DIR__ . '/../shares/footer.php'; ?>
