<?php
// Prevent direct access
if (!defined('ADMIN_INCLUDED')) {
    header("Location: ../index.php");
    exit;
}

// Restrict to admins
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Bạn không có quyền truy cập trang này.";
    header("Location: index.php?page=dashboard");
    exit;
}

require_once '../models/User.php';
try {
    // Verify database connection
    if (!isset($conn)) {
        throw new Exception("Database connection not available.");
    }
    $GLOBALS['debug_logs'][] = "Database connection verified in users.php.";

    // Instantiate User model
    $user_model = new User($conn);
    $GLOBALS['debug_logs'][] = "User model instantiated successfully.";

    // Pagination
    $items_per_page = 10;
    $page = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
    
    // Search
    $keywords = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    if ($keywords !== '') {
        $GLOBALS['debug_logs'][] = "Performing search with keywords: '$keywords' on page $page.";
        $users_stmt = $user_model->search($keywords, $items_per_page, $page);
        $total_users = $user_model->countSearch($keywords);
        $GLOBALS['debug_logs'][] = "Search query executed. Total users found: $total_users.";
    } else {
        $GLOBALS['debug_logs'][] = "Fetching users for page $page without search.";
        $users_stmt = $user_model->read($items_per_page, $page);
        $total_users = $user_model->countAll();
        $GLOBALS['debug_logs'][] = "Read query executed. Total users: $total_users.";
    }
    
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_pages = ceil($total_users / $items_per_page);
    
    $GLOBALS['debug_logs'][] = "Users fetched successfully: " . count($users) . " users on page $page.";
} catch (PDOException $e) {
    $GLOBALS['debug_logs'][] = "PDO error in users.php: " . $e->getMessage();
    error_log("PDO error in users.php: " . $e->getMessage());
    $error_message = "Lỗi cơ sở dữ liệu khi tải danh sách người dùng.";
} catch (Exception $e) {
    $GLOBALS['debug_logs'][] = "General error in users.php: " . $e->getMessage();
    error_log("General error in users.php: " . $e->getMessage());
    $error_message = "Lỗi khi tải danh sách người dùng.";
}
?>

<div class="container-fluid">
    <div class="card p-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 fw-bold text-primary">Quản lý Người dùng</h2>
            <a href="index.php?page=user-add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Thêm Người dùng
            </a>
        </div>
        
        <!-- Search Form -->
        <form class="mb-4" method="GET" action="">
            <input type="hidden" name="page" value="users">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm theo tên, email, hoặc tên đầy đủ" 
                       value="<?php echo htmlspecialchars($keywords ?? ''); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i> Tìm kiếm
                </button>
            </div>
        </form>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (!isset($users) || empty($users)): ?>
            <div class="alert alert-info">Không tìm thấy người dùng nào.</div>
        <?php else: ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Tên đầy đủ</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['role'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A'); ?></td>
                            <td>
                                <a href="index.php?page=user-edit&id=<?php echo $user['id'] ?? ''; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="index.php?page=user-delete&id=<?php echo $user['id'] ?? ''; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Bạn có chắc muốn xóa người dùng này?');">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="User pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=users&search=<?php echo urlencode($keywords ?? ''); ?>&page_num=<?php echo $page - 1; ?>">Trước</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=users&search=<?php echo urlencode($keywords ?? ''); ?>&page_num=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=users&search=<?php echo urlencode($keywords ?? ''); ?>&page_num=<?php echo $page + 1; ?>">Sau</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .table .btn {
        margin-right: 5px;
    }
    .card {
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 10px;
        font-size: 0.95rem;
    }
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
        outline: none;
    }
    .input-group .btn {
        border-radius: 8px;
    }
    .pagination .page-link {
        border-radius: 5px;
        margin: 0 3px;
    }
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }
</style>