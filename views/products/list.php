<?php 
$page_title = isset($category_name) && !empty($category_name) ? $category_name : (isset($_GET['search']) ? 'Kết quả tìm kiếm cho "' . htmlspecialchars($_GET['search']) . '"' : 'Tất cả sản phẩm');
include 'views/layouts/header.php'; 
?>

<!-- Page Header Banner -->
<div class="category-header position-relative mb-5">
    <div class="category-header-bg" style="background-color: var(--light-bg-color); height: 180px; position: relative; overflow: hidden;">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <h1 class="display-4 fw-bold text-uppercase fade-in"><?php echo $page_title; ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="position-absolute" style="top:0; right:0; bottom:0; left:0; background: linear-gradient(135deg, rgba(255,45,85,0.1) 0%, rgba(74,0,224,0.05) 100%);"></div>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <!-- Category Filter -->
            <div class="filter-card mb-4 hover-lift">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0 fw-bold">BỘ LỌC</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-uppercase fw-bold mb-3 border-bottom pb-2">Danh mục</h6>
                        <ul class="list-unstyled category-filter">
                            <li class="mb-2">
                                <a href="index.php?controller=product&action=list" class="text-decoration-none d-flex justify-content-between align-items-center filter-link <?php echo (!isset($_GET['category_id']) && !isset($_GET['is_sale'])) ? 'active' : ''; ?>">
                                    <span>Tất cả sản phẩm</span>
                                    <i class="fas fa-chevron-right small"></i>
                                </a>
                            </li>
                            <?php foreach($categories as $cat): ?>
                            <li class="mb-2">
                                <a href="index.php?controller=product&action=list&category_id=<?php echo $cat['id']; ?>" class="text-decoration-none d-flex justify-content-between align-items-center filter-link <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'active' : ''; ?>">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <i class="fas fa-chevron-right small"></i>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <li class="mb-2">
                                <a href="index.php?controller=product&action=list&is_sale=1" class="text-decoration-none d-flex justify-content-between align-items-center filter-link-sale <?php echo (isset($_GET['is_sale']) && $_GET['is_sale'] == 1) ? 'active' : ''; ?>">
                                    <span class="fw-bold">SALE</span>
                                    <i class="fas fa-fire small"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Price Filter -->
            <div class="filter-card hover-lift">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-bold mb-3 border-bottom pb-2">Khoảng giá</h6>
                        <form action="index.php" method="GET" class="price-filter-form">
                            <input type="hidden" name="controller" value="product">
                            <input type="hidden" name="action" value="list">
                            <?php if(isset($_GET['category_id'])): ?>
                            <input type="hidden" name="category_id" value="<?php echo intval($_GET['category_id']); ?>">
                            <?php endif; ?>
                            <?php if(isset($_GET['search'])): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                            <?php if(isset($_GET['is_sale'])): ?>
                            <input type="hidden" name="is_sale" value="<?php echo intval($_GET['is_sale']); ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="min_price" class="form-label small fw-bold">Từ:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo CURRENCY; ?></span>
                                    <input type="number" class="form-control" id="min_price" name="min_price" min="0" value="<?php echo isset($_GET['min_price']) ? intval($_GET['min_price']) : ''; ?>" placeholder="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="max_price" class="form-label small fw-bold">Đến:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo CURRENCY; ?></span>
                                    <input type="number" class="form-control" id="max_price" name="max_price" min="0" value="<?php echo isset($_GET['max_price']) ? intval($_GET['max_price']) : ''; ?>" placeholder="2.000.000">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">ÁP DỤNG</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product List -->
        <div class="col-lg-9">
            <!-- Sort Options -->
            <div class="products-toolbar d-flex flex-wrap justify-content-between align-items-center mb-4 py-2 border-bottom">
                <div class="toolbar-left">
                    <span class="text-muted">Hiển thị <?php echo count($products); ?> sản phẩm</span>
                </div>
                <div class="toolbar-right d-flex align-items-center">
                    <span class="me-2 text-nowrap">Sắp xếp:</span>
                    <select class="form-select form-select-sm border-0 bg-light px-3 py-2" id="sort-products" style="width: auto; min-width: 200px;">
                        <option value="default" selected>Mặc định</option>
                        <option value="price-low">Giá: Thấp đến cao</option>
                        <option value="price-high">Giá: Cao đến thấp</option>
                        <option value="newest">Mới nhất</option>
                    </select>
                </div>
            </div>
        
            <?php if(empty($products)): ?>
            <div class="alert alert-light shadow-sm p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-search fa-3x text-muted"></i>
                </div>
                <h4 class="fw-bold">Không tìm thấy sản phẩm nào</h4>
                <p class="text-muted">Vui lòng thử tìm kiếm với từ khóa khác hoặc duyệt qua các danh mục có sẵn.</p>
                <a href="index.php?controller=product&action=list" class="btn btn-primary mt-2">Xem tất cả sản phẩm</a>
            </div>
            <?php else: ?>
            <div class="row g-4" id="product-container">
                <?php foreach($products as $product): ?>
                <div class="col-6 col-md-4 mb-0 product-item" 
                     data-price="<?php echo ($product['is_sale'] == 1 && !empty($product['sale_price'])) ? $product['sale_price'] : $product['price']; ?>"
                     data-date="<?php echo strtotime($product['created_at']); ?>">
                    <div class="product-card">
                        <div class="position-relative overflow-hidden">
                            <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>" class="d-block">
                                <?php if(!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 320px;">
                                    <i class="fas fa-tshirt fa-4x text-secondary"></i>
                                </div>
                                <?php endif; ?>
                            </a>
                            
                            <?php if($product['is_sale'] == 1 && !empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                            <div class="sale-badge product-badge">SALE</div>
                            <?php else: ?>
                            <div class="new-badge product-badge">NEW</div>
                            <?php endif; ?>
                            
                            <div class="product-actions position-absolute start-0 bottom-0 end-0 bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                                <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>" class="btn btn-link text-dark px-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-link text-dark px-2 add-to-wishlist" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="btn btn-link text-dark px-2 add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="product-category text-uppercase mb-1"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
                                <h5 class="card-title">
                                    <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($product['name']); ?></a>
                                </h5>
                                <div class="price-block">
                                    <?php if($product['is_sale'] == 1 && !empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                    <span class="text-danger fw-bold"><?php echo CURRENCY . number_format($product['sale_price']); ?></span>
                                    <span class="text-muted text-decoration-line-through ms-2"><?php echo CURRENCY . number_format($product['price']); ?></span>
                                    <?php else: ?>
                                    <span class="fw-bold"><?php echo CURRENCY . number_format($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav aria-label="Product pagination" class="mt-5">
                <ul class="pagination pagination-modern justify-content-center">
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
