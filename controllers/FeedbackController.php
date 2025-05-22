<?php
require_once 'models/Feedback.php';

class FeedbackController {
    private $db;
    private $feedback;
    private $upload_dir = 'uploads/photos/';
    private $max_file_size = 5000000; // 5MB

    public function __construct($db) {
        $this->db = $db;
        $this->feedback = new Feedback($db);
        // Tạo thư mục uploads/photos nếu chưa tồn tại
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }

    public function submitFeedback($request, $files) {
        try {
            // Kiểm tra đăng nhập
            if (!isset($_SESSION['user_id'])) {
                return ['success' => false, 'message' => 'Vui lòng đăng nhập để gửi đánh giá.'];
            }

            $user_id = $_SESSION['user_id'];
            $product_id = isset($request['product_id']) ? (int)$request['product_id'] : 0;
            $content = trim($request['content'] ?? '');
            $rating = isset($request['rating']) ? (int)$request['rating'] : 0;

            // Kiểm tra dữ liệu đầu vào
            if ($product_id <= 0) {
                return ['success' => false, 'message' => 'Sản phẩm không hợp lệ.'];
            }
            if (empty($content)) {
                return ['success' => false, 'message' => 'Nội dung đánh giá không được để trống.'];
            }
            if ($rating < 1 || $rating > 5) {
                return ['success' => false, 'message' => 'Điểm đánh giá phải từ 1 đến 5.'];
            }

            // Kiểm tra người dùng đã mua sản phẩm
            if (!$this->feedback->hasPurchased($user_id, $product_id)) {
                return ['success' => false, 'message' => 'Bạn cần mua sản phẩm này để gửi đánh giá.'];
            }

            // Bắt đầu transaction
            $this->db->beginTransaction();

            // Thêm đánh giá
            if (!$this->feedback->addFeedback($user_id, $product_id, $content, $rating)) {
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

                    if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                        throw new Exception("Lỗi khi tải lên ảnh: " . $name);
                    }

                    $file_size = $files['size'][$index];
                    if ($file_size > $this->max_file_size) {
                        throw new Exception("Ảnh " . $name . " vượt quá giới hạn 5MB.");
                    }

                    $file_type = mime_content_type($files['tmp_name'][$index]);
                    if (!in_array($file_type, ['image/jpeg', 'image/png', 'image/gif'])) {
                        throw new Exception("Chỉ hỗ trợ định dạng JPEG, PNG, GIF.");
                    }

                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_ext;
                    $file_path = $this->upload_dir . $file_name;

                    if (!move_uploaded_file($files['tmp_name'][$index], $file_path)) {
                        throw new Exception("Không thể lưu ảnh: " . $name);
                    }

                    if (!$this->feedback->addFeedbackMedia($feedback_id, $file_path, $file_size)) {
                        throw new Exception("Lỗi khi lưu thông tin ảnh.");
                    }
                    $photo_count++;
                }
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Đánh giá và ảnh đã được gửi thành công!'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Lấy danh sách đánh giá cho sản phẩm
    public function getFeedback($product_id) {
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
?>