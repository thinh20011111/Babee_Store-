<?php 
$page_title = isset($category_name) && !empty($category_name) ? $category_name : (isset($_GET['search']) ? 'Kết quả tìm kiếm cho "' . htmlspecialchars($_GET['search']) . '"' : 'Tất cả sản phẩm');
include 'views/layouts/header.php'; 
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Danh mục</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item <?php echo (!isset($_GET['category_id'])) ? 'active' : ''; ?>">
                        <a href="index.php?controller=product&action=list" class="text-decoration-none <?php echo (!isset($_GET['category_id'])) ? 'text-white' : 'text-dark'; ?>">
                            Tất cả sản phẩm
                        </a>
                    </li>
                    <?php foreach($categories as $cat): ?>
                    <li class="list-group-item <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'active' : ''; ?>">
                        <a href="index.php?controller=product&action=list&category_id=<?php echo $cat['id']; ?>" class="text-decoration-none <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'text-white' : 'text-dark'; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <li class="list-group-item <?php echo (isset($_GET['is_sale']) && $_GET['is_sale'] == 1) ? 'active' : ''; ?>">
                        <a href="index.php?controller=product&action=list&is_sale=1" class="text-decoration-none <?php echo (isset($_GET['is_sale']) && $_GET['is_sale'] == 1) ? 'text-white' : 'text-danger'; ?>">
                            Sản phẩm giảm giá
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Khoảng giá</h5>
            </div>
            <div class="card-body">
                <form action="index.php" method="GET">
                    <input type="hidden" name="controller" value="product">
                    <input type="hidden" name="action" value="list">
                    <?php if(isset($_GET['category_id'])): ?>
                    <input type="hidden" name="category_id" value="<?php echo intval($_GET['category_id']); ?>">
                    <?php endif; ?>
                    <?php if(isset($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="min_price" class="form-label">Giá thấp nhất:</label>
                        <input type="number" class="form-control" id="min_price" name="min_price" min="0" value="<?php echo isset($_GET['min_price']) ? intval($_GET['min_price']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="max_price" class="form-label">Giá cao nhất:</label>
                        <input type="number" class="form-control" id="max_price" name="max_price" min="0" value="<?php echo isset($_GET['max_price']) ? intval($_GET['max_price']) : ''; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Lọc sản phẩm</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Product List -->
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><?php echo $page_title; ?></h2>
            <div class="d-flex align-items-center">
                <span class="me-2">Sắp xếp theo:</span>
                <select class="form-select form-select-sm" id="sort-products">
                    <option value="default" selected>Mặc định</option>
                    <option value="price-low">Giá: Thấp đến cao</option>
                    <option value="price-high">Giá: Cao đến thấp</option>
                    <option value="newest">Mới nhất</option>
                </select>
            </div>
        </div>
        
        <?php if(empty($products)): ?>
        <div class="alert alert-info">
            <p class="mb-0">Không tìm thấy sản phẩm nào. Vui lòng thử tìm kiếm hoặc chọn danh mục khác.</p>
        </div>
        <?php else: ?>
        <div class="row" id="product-container">
            <?php foreach($products as $product): ?>
            <div class="col-6 col-md-4 col-lg-4 mb-4 product-item" 
                 data-price="<?php echo ($product['is_sale'] == 1 && !empty($product['sale_price'])) ? $product['sale_price'] : $product['price']; ?>"
                 data-date="<?php echo strtotime($product['created_at']); ?>">
                <div class="product-card h-100">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="position-relative">
                            <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>">
                                <?php if(!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                <div class="card-img-top bg-light p-4 d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-tshirt fa-4x text-secondary"></i>
                                </div>
                                <?php endif; ?>
                            </a>
                            <?php if($product['is_sale'] == 1 && !empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                            <div class="product-badge bg-danger text-white position-absolute top-0 end-0 m-2 px-2 py-1 rounded">Giảm giá</div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="product-category text-muted small mb-1"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
                            <h5 class="card-title">
                                <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($product['name']); ?></a>
                            </h5>
                            <div class="price-block mb-3">
                                <?php if($product['is_sale'] == 1 && !empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                <span class="text-danger fw-bold"><?php echo CURRENCY . number_format($product['sale_price']); ?></span>
                                <span class="text-muted text-decoration-line-through ms-2"><?php echo CURRENCY . number_format($product['price']); ?></span>
                                <?php else: ?>
                                <span class="fw-bold"><?php echo CURRENCY . number_format($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-auto">
                                <button class="btn btn-primary btn-sm w-100 add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-cart me-1"></i> Thêm vào giỏ hàng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <nav aria-label="Product pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="index.php?controller=product&action=list<?php 
                        echo isset($_GET['category_id']) ? '&category_id=' . intval($_GET['category_id']) : ''; 
                        echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '';
                        echo isset($_GET['is_sale']) ? '&is_sale=' . intval($_GET['is_sale']) : '';
                        echo isset($_GET['min_price']) ? '&min_price=' . intval($_GET['min_price']) : '';
                        echo isset($_GET['max_price']) ? '&max_price=' . intval($_GET['max_price']) : '';
                        echo '&page=' . $i;
                    ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Recently Viewed Products -->
<section class="recently-viewed-section mt-5">
    <h3 class="mb-3">Đã xem gần đây</h3>
    <div class="row">
        <!-- This section would be populated with JavaScript based on browser storage -->
        <div class="col-12">
            <p class="text-muted">Bắt đầu duyệt các sản phẩm để xem các mục đã xem gần đây tại đây.</p>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
