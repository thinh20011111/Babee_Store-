<?php
// Trang quản lý danh mục
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

require_once '../config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    error_log("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
    die("Lỗi hệ thống - Vui lòng kiểm tra nhật ký để biết chi tiết.");
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    echo "<div class='alert alert-danger'>Bạn không có quyền truy cập trang này.</div>";
    exit;
}

require_once '../models/Category.php';
try {
    $category = new Category($conn);
} catch (Exception $e) {
    error_log("Lỗi khởi tạo mô hình: " . $e->getMessage());
    die("Lỗi hệ thống - Vui lòng kiểm tra nhật ký để biết chi tiết.");
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

if ($action == 'delete' && isset($_GET['id'])) {
    $category->id = $_GET['id'];
    try {
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $category->name = trim($_POST['name'] ?? '');
        $category->description = trim($_POST['description'] ?? '');

        $category->image = $edit_category ? $edit_category->image : '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            $upload_dir = '../uploads/categories/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
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
                    if ($edit_category && $edit_category->image && file_exists('../' . $edit_category->image)) {
                        unlink('../' . $edit_category->image);
                    }
                } else {
                    throw new Exception("Tải ảnh lên thất bại.");
                }
            } else {
                throw new Exception("Lỗi tải ảnh: " . $file['error']);
            }
        }

        if (empty($category->name)) {
            throw new Exception("Tên danh mục là bắt buộc.");
        }

        if (isset($_POST['edit_id'])) {
            $category->id = intval($_POST['edit_id']);
            if ($category->update()) {
                $success_message = "Cập nhật danh mục thành công.";
                $category->readOne();
                $edit_category = $category;
            } else {
                $error_message = "Cập nhật danh mục thất bại.";
            }
        } else {
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục - Bảng điều khiển quản trị</title>
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
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
                                        <label for="image" class="form-label">Hình ảnh danh mục</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                                        <div class="form-text">Định dạng được chấp nhận: JPEG, PNG, GIF. Kích thước tối đa: 5MB</div>
                                        <?php if ($edit_category && $edit_category->image): ?>
                                        <div class="mt-2">
                                            <p>Hình ảnh hiện tại:</p>
                                            <img src="../<?php echo htmlspecialchars($edit_category->image); ?>" alt="Hình ảnh danh mục" class="img-thumbnail" style="max-width: 150px;">
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
                                                <th>Hình ảnh</th>
                                                <th>Sản phẩm</th>
                                                <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($categories)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Không tìm thấy danh mục</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                                <td><?php echo htmlspecialchars($cat['description'] ? substr($cat['description'], 0, 50) . '...' : ''); ?></td>
                                                <td>
                                                    <?php if ($cat['image']): ?>
                                                    <img src="../<?php echo htmlspecialchars($cat['image']); ?>" alt="Hình ảnh danh mục" class="img-thumbnail" style="max-width: 50px;">
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
        /* Same styles as before */
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>