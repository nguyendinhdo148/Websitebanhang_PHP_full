<?php include __DIR__ . '/../shares/header.php'; ?>

<div class="container py-5">
    <h1 class="mb-4">Sản phẩm theo danh mục</h1>
    <?php if (!empty($category)): ?>
        <h2 class="mb-3">Danh mục: <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?></h2>
    <?php endif; ?>

    <?php if (!empty($products)): ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-img-top p-3" style="height:220px;display:flex;align-items:center;justify-content:center;">
                            <?php if (!empty($product->image)): ?>
                                <img src="/webbanhang/public/uploads/products/<?php echo htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8'); ?>"
                                     class="img-fluid" style="max-height:200px;object-fit:contain;"
                                     alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php else: ?>
                                <img src="https://placehold.co/300x200?text=No+Image"
                                     class="img-fluid"
                                     alt="No image">
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="mt-auto">
                                <span class="badge badge-info">Giá: <?php echo number_format($product->price, 0, ',', '.'); ?> VND</span>
                                <a href="/webbanhang/Product/detail/<?php echo $product->id; ?>" class="btn btn-primary btn-sm mt-2">Chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-box-open fa-3x mb-3 text-info"></i>
            <h3 class="alert-heading">Không có sản phẩm nào trong danh mục này</h3>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../shares/footer.php'; ?>
