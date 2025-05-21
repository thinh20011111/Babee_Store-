<?php
// Trang quản lý danh mục
// Kiểm tra truy cập trực tiếp
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Kết nối cơ sở dữ liệu
require_once '../config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    // Thiết lập mã hóa UTF-8
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    error_log("Lỗi kết nối cơ sở dữ liệu hoặc mã hóa: " . $e->getMessage());
    die("Lỗi hệ thống - Vui lòng kiểm tra nhật ký để biết chi tiết.");
}

// Hạn chế truy cập chỉ dành cho quản trị viên
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    echo "<div class='alert alert-danger'>Bạn không có quyền truy cập trang này.</div>";
    exit;
}

// Tải các mô hình cần thiết
require_once '../models/Category.php';

// Khởi tạo đối tượng
try {
    $category = new Category($conn);
} catch (Exception $e) {
    error_log("Lỗi khởi tạo mô hình: " . $e->getMessage());
    die("Lỗi hệ thống - Vui lòng kiểm tra nhật ký để biết chi tiết.");
}

// Xử lý hành động
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Xóa danh mục
if ($action == 'delete' && isset($_GET['id'])) {
    $category->id = $_GET['id'];
    try {
        // Kiểm tra xem danh mục có sản phẩm không
        if ($category->countProducts() > 0) {
            $error_message = "Không thể xóa danh mục vì danh mục chứa sản phẩm.";
        } elseif ($category->delete()) {
            $success_message = "Xóa danh mục thành công.";
        } else {
            $error_message = "Xóa danh mục thất bại.";
        }
    } catch (Exception $e) {
        $error_message = "Lỗi khi xóa danh mục: " . $e->getMessage();
        error_log("Lỗi xóa danh mục: " . $e->getMessage());
    }
}

// Sửa danh mục
$edit_category = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $category->id = $_GET['id'];
    try {
        if ($category->readOne()) {
            $edit_category = $category;
        } else {
            $error_message = "Không tìm thấy danh mục.";
        }
    } catch (Exception $e) {
        $error_message = "Lỗi khi tải danh mục: " . $e->getMessage();
        error_log("Lỗi đọc danh mục: " . $e->getMessage());
    }
}

// Xử lý biểu mẫu thêm/sửa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $category->name = trim($_POST['name'] ?? '');
        $category->description = trim($_POST['description'] ?? '');
        $category->parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

        // Xử lý tải ảnh lên
        $category->image = $edit_category ? $edit_category->image : '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/categories/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            // Tạo thư mục tải lên nếu chưa tồn tại
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("Không thể tạo thư mục uploads/categories.");
                }
            }

            // Kiểm tra quyền ghi thư mục
            if (!is_writable($upload_dir)) {
                throw new Exception("Thư mục uploads/categories không có quyền ghi.");
            }

            $file = $_FILES['image'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                if (!in_array($file['type'], $allowed_types)) {
                    throw new Exception("Chỉ chấp nhận hình ảnh định dạng JPEG, PNG và GIF.");
                }
                if ($file['size'] > $max_size) {
                    throw new Exception("Kích thước hình ảnh phải nhỏ hơn 5MB.");
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'category_' . time() . '_' . uniqid() . '.' . $ext;
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $category->image = 'uploads/categories/' . $filename;
                    // Xóa ảnh cũ nếu đang cập nhật
                    if ($edit_category && $edit_category->image && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $edit_category->image)) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $edit_category->image);
                    }
                } else {
                    error_log("Lỗi di chuyển file: Từ " . $file['tmp_name'] . " đến " . $destination);
                    throw new Exception("Không thể di chuyển file hình ảnh vào thư mục đích.");
                }
            } else {
                throw new Exception("Lỗi tải ảnh: Mã lỗi " . $file['error']);
            }
        }

        // Xác thực dữ liệu
        if (empty($category->name)) {
            throw new Exception("Tên danh mục là bắt buộc.");
        }

        if (isset($_POST['edit_id'])) {
            // Cập nhật danh mục hiện có
            $category->id = intval($_POST['edit_id']);
            if ($category->update()) {
                $success_message = "Cập nhật danh mục thành công.";
                $category->readOne();
                $edit_category = $category;
            } else {
                $error_message = "Cập nhật danh mục thất bại.";
            }
        } else {
            // Tạo danh mục mới
            if ($category->create()) {
                $success_message = "Tạo danh mục thành công.";
                $category = new Category($conn);
            } else {
                $error_message = "Tạo danh mục thất bại.";
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Lỗi lưu danh mục: " . $e->getMessage());
    }
}

// Lấy tất cả danh mục
$categories = [];
try {
    $stmt = $category->read();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }
} catch (Exception $e) {
    $error_message = "Lỗi khi tải danh mục: " . $e->getMessage();
    error_log("Lỗi lấy danh mục: " . $e->getMessage());
}

// Lấy tất cả danh mục cho danh sách thả xuống parent_id
$parent_categories = [];
try {
    $stmt = $category->read();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $parent_categories[] = $row;
    }
} catch (Exception $e) {
    error_log("Lỗi lấy danh mục cha: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục - Bảng điều khiển quản trị</title>
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous">
</head>
<body>
    <div class="d-flex">
        <!-- Thanh bên -->
        <?php include 'sidebar.php'; ?>

        <!-- Nội dung chính -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid px-4">
                <h1 class="mt-4">Quản lý danh mục</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Danh mục</li>
                </ol>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Biểu mẫu danh mục -->
                    <div class="col-lg-5">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-folder me-1"></i>
                                <?php echo $edit_category ? 'Sửa' : 'Thêm mới'; ?> danh mục
                            </div>
                            <div class="card-body">
                                <form action="index.php?page=categories<?php echo $edit_category ? '&action=edit&id=' . $edit_category->id : ''; ?>" method="POST" enctype="multipart/form-data">
                                    <?php if ($edit_category): ?>
                                    <input type="hidden" name="edit_id" value="<?php echo $edit_category->id; ?>">
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_category ? htmlspecialchars($edit_category->name) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Mô tả</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo $edit_category ? htmlspecialchars($edit_category->description) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="parent_id" class="form-label">Danh mục cha</label>
                                        <select class="form-select" id="parent_id" name="parent_id">
                                            <option value="">Không có</option>
                                            <?php foreach ($parent_categories as $parent): ?>
                                                <?php if (!$edit_category || $parent['id'] != $edit_category->id): ?>
                                                <option value="<?php echo $parent['id']; ?>" <?php echo ($edit_category && isset($edit_category->parent_id) && $edit_category->parent_id == $parent['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($parent['name']); ?>
                                                </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Chọn danh mục cha (không bắt buộc)</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">Hình ảnh danh mục</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                                        <div class="form-text">Định dạng được chấp nhận: JPEG, PNG, GIF. Kích thước tối đa: 5MB</div>
                                        <?php if ($edit_category && $edit_category->image): ?>
                                        <div class="mt-2">
                                            <p>Hình ảnh hiện tại:</p>
                                            <img src="/<?php echo htmlspecialchars($edit_category->image); ?>" alt="Hình ảnh danh mục" class="img-thumbnail" style="max-width: 150px;">
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary"><?php echo $edit_category ? 'Cập nhật' : 'Thêm'; ?> danh mục</button>
                                        <?php if ($edit_category): ?>
                                        <a href="index.php?page=categories" class="btn btn-secondary">Hủy sửa</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách danh mục -->
                    <div class="col-lg-7">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-list me-1"></i>
                                Danh sách danh mục
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Tên</th>
                                                <th>Mô tả</th>
                                                <th>Danh mục cha</th>
                                                <th>Hình ảnh</th>
                                                <th>Sản phẩm</th>
                                                <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($categories)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Không tìm thấy danh mục</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                                <td><?php echo htmlspecialchars($cat['description'] ? substr($cat['description'], 0, 50) . '...' : ''); ?></td>
                                                <td><?php echo $cat['parent_id'] ? htmlspecialchars($category->getNameById($cat['parent_id'])) : 'Không có'; ?></td>
                                                <td>
                                                    <?php if ($cat['image']): ?>
                                                    <img src="/<?php echo htmlspecialchars($cat['image']); ?>" alt="Hình ảnh danh mục" class="img-thumbnail" style="max-width: 50px;">
                                                    <?php else: ?>
                                                    Không có hình ảnh
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $category->id = $cat['id']; echo $category->countProducts(); ?></td>
                                                <td>
                                                    <a href="index.php?page=categories&action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-primary btn-sm me-1">
                                                        <i class="fas fa-edit"></i> Sửa
                                                    </a>
                                                    <a href="index.php?page=categories&action=delete&id=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* General Improvements */
        .container-fluid {
            padding: 20px;
        }

        .card {
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 15px;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Styling */
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-control, .form-select, .form-check-input {
            border-radius: 5px;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
            border-color: #007bff;
        }

        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .d-grid .btn {
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        /* Table Styling */
        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        .table thead th {
            background-color: #343a40;
            color: white;
            font-weight: 500;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table td {
            vertical-align: middle;
            padding: 12px;
        }

        /* Action Buttons */
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.9rem;
            border-radius: 4px;
        }

        .btn-sm i {
            font-size: 0.9rem;
        }

        .btn-sm.me-1 {
            margin-right: 8px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .row {
                flex-direction: column;
            }

            .col-lg-5, .col-lg-7 {
                width: 100%;
                max-width: 100%;
            }

            .col-lg-5 {
                margin-bottom: 20px;
            }

            .table-responsive {
                border: none;
            }

            .table td, .table th {
                font-size: 0.9rem;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding: 15px;
            }

            h1.mt-4 {
                font-size: 1.5rem;
            }

            .card-header {
                font-size: 1rem;
                padding: 12px;
            }

            .card-body {
                padding: 15px;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control, .form-select {
                font-size: 0.9rem;
            }

            .d-grid .btn {
                font-size: 0.9rem;
                padding: 8px;
            }
        }

        /* Sidebar Styling */
        .sidebar {
            min-height: 100vh;
            position: sticky;
            top: 0;
        }

        .sidebar .nav-link {
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            background-color: #007bff;
            color: white !important;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }
    </style>

    <!-- Thư viện JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>