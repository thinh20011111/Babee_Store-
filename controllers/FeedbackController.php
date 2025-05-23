<?php
require_once 'models/Feedback.php';

class FeedbackController
{
    private $db;
    private $feedback;
    private $upload_dir = 'uploads/photos/';
    private $max_file_size = 5000000; // 5MB

    public function __construct($db)
    {
        $this->db = $db;
        $this->feedback = new Feedback($db);
        // Tạo thư mục uploads/photos nếu chưa tồn tại
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }

    public function submitFeedback($request, $files)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true / false,
            'message' => 'Thông báo phản hồi'
        ]);
        exit;
        try {
            // Kiểm tra đăng nhập
            if (!isset($_SESSION['user_id'])) {
                return ['success' => false, 'message' => 'Vui lòng đăng nhập để gửi đánh giá.'];
            }

            $user_id = $_SESSION['user_id'];
            $product_id = isset($request['product_id']) ? (int)$request['product_id'] : 0;
            $order_id = isset($request['order_id']) ? (int)$request['order_id'] : 0;
            $content = trim($request['content'] ?? '');
            $rating = isset($request['rating']) ? (int)$request['rating'] : 0;

            // Kiểm tra dữ liệu đầu vào
            if ($product_id <= 0 || $order_id <= 0) {
                return ['success' => false, 'message' => 'Dữ liệu không hợp lệ.'];
            }
            if (strlen($content) < 10) {
                return ['success' => false, 'message' => 'Nội dung đánh giá phải có ít nhất 10 ký tự.'];
            }
            if ($rating < 1 || $rating > 5) {
                return ['success' => false, 'message' => 'Điểm đánh giá phải từ 1 đến 5.'];
            }

            // Kiểm tra đã đánh giá chưa
            if ($this->feedback->hasOrderFeedback($user_id, $order_id, $product_id)) {
                return ['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này trong đơn hàng.'];
            }

            // Bắt đầu transaction
            $this->db->beginTransaction();

            // Thêm đánh giá
            if (!$this->feedback->addFeedback($user_id, $product_id, $order_id, $content, $rating)) {
                throw new Exception("Lỗi khi lưu đánh giá.");
            }

            // Lấy feedback_id mới nhất
            $feedback_id = $this->db->lastInsertId();

            // Xử lý upload ảnh
            if (!empty($files['photos']['name'][0])) {
                $photo_count = $this->feedback->getPhotoCount($feedback_id);

                foreach ($files['photos']['name'] as $index => $name) {
                    if ($photo_count >= 3) {
                        throw new Exception("Tối đa 3 ảnh được phép.");
                    }

                    if ($files['photos']['error'][$index] !== UPLOAD_ERR_OK) {
                        throw new Exception("Lỗi khi tải lên ảnh: " . $name);
                    }

                    $file_size = $files['photos']['size'][$index];
                    if ($file_size > $this->max_file_size) {
                        throw new Exception("Ảnh " . $name . " vượt quá giới hạn 5MB.");
                    }

                    $file_type = mime_content_type($files['photos']['tmp_name'][$index]);
                    if (!in_array($file_type, ['image/jpeg', 'image/png', 'image/gif'])) {
                        throw new Exception("Chỉ hỗ trợ định dạng JPEG, PNG, GIF.");
                    }

                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_ext;
                    $file_path = $this->upload_dir . $file_name;

                    if (!move_uploaded_file($files['photos']['tmp_name'][$index], $file_path)) {
                        throw new Exception("Không thể lưu ảnh: " . $name);
                    }

                    if (!$this->feedback->addFeedbackMedia($feedback_id, $file_path, $file_size)) {
                        throw new Exception("Lỗi khi lưu thông tin ảnh.");
                    }
                    $photo_count++;
                }
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Đánh giá đã được gửi thành công!'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateFeedback($request, $files)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                return ['success' => false, 'message' => 'Vui lòng đăng nhập.'];
            }

            $user_id = $_SESSION['user_id'];
            $feedback_id = isset($request['feedback_id']) ? (int)$request['feedback_id'] : 0;
            $content = trim($request['content'] ?? '');
            $rating = isset($request['rating']) ? (int)$request['rating'] : 0;

            // Kiểm tra quyền chỉnh sửa
            if (!$this->feedback->canEditFeedback($feedback_id, $user_id)) {
                return ['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa đánh giá này.'];
            }

            // Kiểm tra dữ liệu
            if (strlen($content) < 10) {
                return ['success' => false, 'message' => 'Nội dung đánh giá phải có ít nhất 10 ký tự.'];
            }
            if ($rating < 1 || $rating > 5) {
                return ['success' => false, 'message' => 'Điểm đánh giá phải từ 1 đến 5.'];
            }

            $this->db->beginTransaction();

            // Cập nhật đánh giá
            if (!$this->feedback->updateFeedback($feedback_id, $content, $rating)) {
                throw new Exception("Lỗi khi cập nhật đánh giá.");
            }

            // Xử lý ảnh mới nếu có
            if (!empty($files['photos']['name'][0])) {
                // Xóa ảnh cũ
                $this->feedback->deleteMediaByFeedback($feedback_id);

                $photo_count = 0;
                foreach ($files['photos']['name'] as $index => $name) {
                    if ($photo_count >= 3) {
                        throw new Exception("Tối đa 3 ảnh được phép.");
                    }

                    // Kiểm tra và lưu ảnh mới
                    if ($files['photos']['error'][$index] !== UPLOAD_ERR_OK) {
                        throw new Exception("Lỗi khi tải lên ảnh: " . $name);
                    }

                    $file_size = $files['photos']['size'][$index];
                    if ($file_size > $this->max_file_size) {
                        throw new Exception("Ảnh " . $name . " vượt quá giới hạn 5MB.");
                    }

                    $file_type = mime_content_type($files['photos']['tmp_name'][$index]);
                    if (!in_array($file_type, ['image/jpeg', 'image/png', 'image/gif'])) {
                        throw new Exception("Chỉ hỗ trợ định dạng JPEG, PNG, GIF.");
                    }

                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_ext;
                    $file_path = $this->upload_dir . $file_name;

                    if (!move_uploaded_file($files['photos']['tmp_name'][$index], $file_path)) {
                        throw new Exception("Không thể lưu ảnh: " . $name);
                    }

                    if (!$this->feedback->addFeedbackMedia($feedback_id, $file_path, $file_size)) {
                        throw new Exception("Lỗi khi lưu thông tin ảnh.");
                    }
                    $photo_count++;
                }
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Đánh giá đã được cập nhật thành công!'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteFeedback($request)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                return ['success' => false, 'message' => 'Vui lòng đăng nhập.'];
            }

            $user_id = $_SESSION['user_id'];
            $feedback_id = isset($request['feedback_id']) ? (int)$request['feedback_id'] : 0;

            // Kiểm tra quyền xóa
            if (!$this->feedback->canEditFeedback($feedback_id, $user_id)) {
                return ['success' => false, 'message' => 'Bạn không có quyền xóa đánh giá này.'];
            }

            // Xóa đánh giá và ảnh
            if (!$this->feedback->deleteFeedback($feedback_id)) {
                throw new Exception("Lỗi khi xóa đánh giá.");
            }

            return ['success' => true, 'message' => 'Đánh giá đã được xóa thành công!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Lấy danh sách đánh giá cho sản phẩm
    public function getFeedback($product_id)
    {
        try {
            $feedbacks = $this->feedback->getFeedbackByProduct($product_id);
            foreach ($feedbacks as &$feedback) {
                $feedback['media'] = $this->feedback->getMediaByFeedback($feedback['id']);
            }
            return ['success' => true, 'data' => $feedbacks];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
