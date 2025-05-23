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
            header('Content-Type: application/json; charset=UTF-8');

            if (!isset($_SESSION['user_id'])) {
                error_log("submitFeedback: User not logged in");
                die(json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi đánh giá.']));
            }

            $user_id = (int)$_SESSION['user_id'];
            $product_id = isset($request['product_id']) ? (int)$request['product_id'] : 0;
            $order_id = isset($request['order_id']) ? (int)$request['order_id'] : 0;
            $content = trim($request['content'] ?? '');
            $rating = isset($request['rating']) ? (int)$request['rating'] : 0;

            error_log("submitFeedback called with: user_id=$user_id, product_id=$product_id, order_id=$order_id, rating=$rating, content=$content");

            if ($product_id <= 0 || $order_id <= 0) {
                error_log("submitFeedback: Invalid product_id ($product_id) or order_id ($order_id)");
                die(json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']));
            }
            if (strlen($content) < 10) {
                error_log("submitFeedback: Content too short");
                die(json_encode(['success' => false, 'message' => 'Nội dung đánh giá phải có ít nhất 10 ký tự.']));
            }
            if ($rating < 1 || $rating > 5) {
                error_log("submitFeedback: Invalid rating ($rating)");
                die(json_encode(['success' => false, 'message' => 'Điểm đánh giá phải từ 1 đến 5.']));
            }

            $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

            if ($this->feedback->hasOrderFeedback($user_id, $order_id, $product_id)) {
                error_log("submitFeedback: Already reviewed (user_id=$user_id, order_id=$order_id, product_id=$product_id)");
                die(json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này trong đơn hàng.']));
            }

            $this->db->beginTransaction();

            $feedback_id = $this->feedback->addFeedback($user_id, $product_id, $order_id, $content, $rating);
            if (!$feedback_id) {
                throw new Exception('Không thể lưu đánh giá.');
            }
            error_log("submitFeedback: Feedback saved with ID $feedback_id");

            if (!empty($files['images']['name'][0])) {
                $photo_count = 0;
                foreach ($files['images']['name'] as $index => $name) {
                    if (empty($name)) continue;
                    if ($photo_count >= 3) {
                        throw new Exception('Chỉ được phép tải lên tối đa 3 ảnh.');
                    }

                    if ($files['images']['error'][$index] !== UPLOAD_ERR_OK) {
                        error_log("submitFeedback: Upload error for file $name: " . $files['images']['error'][$index]);
                        throw new Exception('Lỗi khi tải lên ảnh: ' . $name);
                    }

                    $file_size = $files['images']['size'][$index];
                    if ($file_size > $this->max_file_size) {
                        error_log("submitFeedback: File $name exceeds size limit ($file_size bytes)");
                        throw new Exception('Ảnh ' . $name . ' vượt quá giới hạn 5MB.');
                    }

                    $file_type = mime_content_type($files['images']['tmp_name'][$index]);
                    if (!in_array($file_type, ['image/jpeg', 'image/png', 'image/gif'])) {
                        error_log("submitFeedback: Invalid file type for $name: $file_type");
                        throw new Exception('Chỉ hỗ trợ định dạng JPEG, PNG, GIF.');
                    }

                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_ext;
                    $file_path = $this->upload_dir . $file_name;

                    if (!is_writable($this->upload_dir)) {
                        error_log("submitFeedback: Upload directory $this->upload_dir is not writable");
                        throw new Exception('Thư mục tải lên không khả dụng.');
                    }

                    if (!move_uploaded_file($files['images']['tmp_name'][$index], $file_path)) {
                        error_log("submitFeedback: Failed to move uploaded file $name to $file_path");
                        throw new Exception('Không thể lưu ảnh: ' . $name);
                    }

                    if (!$this->feedback->addFeedbackMedia($feedback_id, $file_path, $file_size)) {
                        error_log("submitFeedback: Failed to save media for feedback $feedback_id");
                        throw new Exception('Lỗi khi lưu thông tin ảnh.');
                    }
                    $photo_count++;
                    error_log("submitFeedback: Saved media for feedback $feedback_id: $file_path");
                }
            }

            $this->db->commit();
            $response = json_encode(['success' => true, 'message' => 'Đánh giá đã được gửi thành công!']);
            error_log("submitFeedback: Response: $response");
            die($response);
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $error_message = $e->getMessage();
            error_log("submitFeedback Error: $error_message in " . $e->getFile() . " on line " . $e->getLine());
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(['success' => false, 'message' => $error_message]));
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