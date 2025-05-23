<?php
require_once 'models/Feedback.php';

class FeedbackController
{
    private $db;
    private $feedback;
    private $upload_dir = '/../Uploads/feedback';
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
        ob_start();

        try {
            if (!isset($_SESSION['user_id'])) {
                error_log("submitFeedback: User not logged in");
                return json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi đánh giá.']);
            }

            $user_id = (int)$_SESSION['user_id'];
            $product_id = isset($request['product_id']) ? (int)$request['product_id'] : 0;
            $order_id = isset($request['order_id']) ? (int)$request['order_id'] : 0;
            $content = trim($request['content'] ?? '');
            $rating = isset($request['rating']) ? (int)$request['rating'] : 0;

            error_log("submitFeedback called with: user_id=$user_id, product_id=$product_id, order_id=$order_id, rating=$rating, content=$content");

            if ($product_id <= 0 || $order_id <= 0 || strlen($content) < 10 || $rating < 1 || $rating > 5) {
                error_log("submitFeedback: Validation failed");
                return json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ. Nội dung ≥ 10 ký tự, điểm 1-5.']);
            }

            $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

            if ($this->feedback->hasOrderFeedback($user_id, $order_id, $product_id)) {
                error_log("submitFeedback: Already reviewed");
                return json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này trong đơn hàng.']);
            }

            $this->db->beginTransaction();
            $feedback_id = $this->feedback->addFeedback($user_id, $product_id, $order_id, $content, $rating);
            if (!$feedback_id) {
                throw new Exception('Không thể lưu đánh giá.');
            }

            if (!empty($files['images']['name'][0])) {
                $photo_count = 0;
                foreach ($files['images']['name'] as $index => $name) {
                    if (empty($name) || $photo_count >= 3 || $files['images']['error'][$index] !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    $file_size = $files['images']['size'][$index];
                    if ($file_size > $this->max_file_size) {
                        throw new Exception('Ảnh vượt quá giới hạn 5MB.');
                    }
                    $file_type = mime_content_type($files['images']['tmp_name'][$index]);
                    if (!in_array($file_type, ['image/jpeg', 'image/png', 'image/gif'])) {
                        throw new Exception('Chỉ hỗ trợ JPEG, PNG, GIF.');
                    }
                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_ext;
                    $file_path = $this->upload_dir . $file_name;
                    if (!move_uploaded_file($files['images']['tmp_name'][$index], $file_path)) {
                        throw new Exception('Không thể lưu ảnh.');
                    }
                    $this->feedback->addFeedbackMedia($feedback_id, $file_path, $file_size);
                    $photo_count++;
                    error_log("submitFeedback: Saved media $file_path");
                }
            }

            $this->db->commit();
            return json_encode(['success' => true, 'message' => 'Đánh giá đã được gửi thành công!']);
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("submitFeedback Error: " . $e->getMessage() . " at " . $e->getFile() . ':' . $e->getLine());
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        } finally {
            ob_end_clean();
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

            if (!$this->feedback->canEditFeedback($feedback_id, $user_id)) {
                return ['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa đánh giá này.'];
            }

            if (strlen($content) < 10) {
                return ['success' => false, 'message' => 'Nội dung đánh giá phải có ít nhất 10 ký tự.'];
            }
            if ($rating < 1 || $rating > 5) {
                return ['success' => false, 'message' => 'Điểm đánh giá phải từ 1 đến 5.'];
            }

            $this->db->beginTransaction();

            if (!$this->feedback->updateFeedback($feedback_id, $content, $rating)) {
                throw new Exception("Lỗi khi cập nhật đánh giá.");
            }

            if (!empty($files['photos']['name'][0])) {
                $this->feedback->deleteMediaByFeedback($feedback_id);

                $photo_count = 0;
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

            if (!$this->feedback->canEditFeedback($feedback_id, $user_id)) {
                return ['success' => false, 'message' => 'Bạn không có quyền xóa đánh giá này.'];
            }

            if (!$this->feedback->deleteFeedback($feedback_id)) {
                throw new Exception("Lỗi khi xóa đánh giá.");
            }

            return ['success' => true, 'message' => 'Đánh giá đã được xóa thành công!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

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
?>