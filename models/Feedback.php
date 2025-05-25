<?php
class Feedback
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Thêm đánh giá mới
    public function addFeedback($user_id, $product_id, $order_id, $content, $rating)
    {
        $query = "INSERT INTO feedback (user_id, product_id, order_id, content, rating) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$user_id, $product_id, $order_id, $content, $rating]);
    }

    // Thêm ảnh cho đánh giá
    public function addFeedbackMedia($feedback_id, $file_path, $file_size)
    {
        $query = "INSERT INTO feedback_media (feedback_id, file_path, file_size) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$feedback_id, $file_path, $file_size]);
    }

    // Lấy số lượng ảnh hiện tại của đánh giá
    public function getPhotoCount($feedback_id)
    {
        $query = "SELECT COUNT(*) FROM feedback_media WHERE feedback_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$feedback_id]);
        return $stmt->fetchColumn();
    }

    // Lấy danh sách đánh giá theo sản phẩm
    public function getFeedbackByProduct($product_id)
    {
        try {
            $query = "SELECT f.id, f.user_id, f.content, f.rating, f.created_at, u.username 
                      FROM feedback f
                      LEFT JOIN users u ON f.user_id = u.id
                      WHERE f.product_id = ?
                      ORDER BY f.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$product_id]);
            $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("getFeedbackByProduct executed: product_id=$product_id, results=" . count($feedbacks));

            foreach ($feedbacks as &$feedback) {
                $feedback['username'] = $feedback['username'] ?? 'Khách ẩn danh';
            }

            return $feedbacks;
        } catch (Exception $e) {
            error_log("Error in getFeedbackByProduct: " . $e->getMessage());
            return [];
        }
    }

    // Lấy ảnh theo feedback_id
    public function getMediaByFeedback($feedback_id)
    {
        $query = "SELECT id, file_path, file_size, created_at
                  FROM feedback_media
                  WHERE feedback_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$feedback_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Kiểm tra người dùng đã đánh giá đơn hàng chưa
    public function hasOrderFeedback($user_id, $order_id, $product_id)
    {
        $query = "SELECT COUNT(*) FROM feedback 
                  WHERE user_id = ? AND order_id = ? AND product_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id, $order_id, $product_id]);
        return $stmt->fetchColumn() > 0;
    }

    // Kiểm tra người dùng có quyền chỉnh sửa đánh giá
    public function canEditFeedback($feedback_id, $user_id)
    {
        $query = "SELECT COUNT(*) FROM feedback 
                  WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$feedback_id, $user_id]);
        return $stmt->fetchColumn() > 0;
    }

    // Cập nhật đánh giá
    public function updateFeedback($feedback_id, $content, $rating)
    {
        $query = "UPDATE feedback 
                  SET content = ?, rating = ?, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$content, $rating, $feedback_id]);
    }

    // Xóa ảnh của đánh giá
    public function deleteMediaByFeedback($feedback_id)
    {
        $query = "DELETE FROM feedback_media WHERE feedback_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$feedback_id]);
    }

    // Xóa đánh giá
    public function deleteFeedback($feedback_id)
    {
        // Xóa media trước
        $this->deleteMediaByFeedback($feedback_id);

        // Sau đó xóa feedback
        $query = "DELETE FROM feedback WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$feedback_id]);
    }

    public function getProductFeedbackStats($product_id)
    {
        try {
            // Lấy tổng số đánh giá và điểm trung bình
            $query = "SELECT COUNT(*) as total_reviews, 
                             AVG(rating) as average_rating,
                             SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                             SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                             SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                             SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                             SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                      FROM feedback
                      WHERE product_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$product_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Làm tròn điểm trung bình đến 1 chữ số thập phân
            $stats['average_rating'] = round($stats['average_rating'], 1);

            // Tính phần trăm cho mỗi mức sao
            $total = $stats['total_reviews'];
            if ($total > 0) {
                $stats['five_star_percent'] = round(($stats['five_star'] / $total) * 100);
                $stats['four_star_percent'] = round(($stats['four_star'] / $total) * 100);
                $stats['three_star_percent'] = round(($stats['three_star'] / $total) * 100);
                $stats['two_star_percent'] = round(($stats['two_star'] / $total) * 100);
                $stats['one_star_percent'] = round(($stats['one_star'] / $total) * 100);
            } else {
                $stats['five_star_percent'] = 0;
                $stats['four_star_percent'] = 0;
                $stats['three_star_percent'] = 0;
                $stats['two_star_percent'] = 0;
                $stats['one_star_percent'] = 0;
            }

            return $stats;
        } catch (Exception $e) {
            error_log("Error in getProductFeedbackStats: " . $e->getMessage());
            return null;
        }
    }

    // Lấy danh sách đánh giá của sản phẩm có phân trang
    public function getProductFeedbacks($product_id, $page = 1, $limit = 10)
    {
        try {
            // Bỏ phần kiểm tra product_id vì không cần thiết (đã được kiểm tra ở controller)
            $offset = ($page - 1) * $limit;

            // Truy vấn feedback
            $query = "SELECT f.id, f.user_id, f.content, f.rating, f.created_at, u.username
                    FROM feedback f
                    LEFT JOIN users u ON f.user_id = u.id
                    WHERE f.product_id = ?
                    ORDER BY f.created_at DESC
                    LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$product_id, $limit, $offset]);
            $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("getProductFeedbacks executed: product_id=$product_id, page=$page, limit=$limit, offset=$offset, results=" . count($feedbacks));

            foreach ($feedbacks as &$feedback) {
                $feedback['media'] = $this->getMediaByFeedback($feedback['id']);
                $feedback['username'] = $feedback['username'] ?? 'Khách ẩn danh';
                error_log("Feedback ID {$feedback['id']}: username={$feedback['username']}, rating={$feedback['rating']}, media_count=" . count($feedback['media']));
            }

            return $feedbacks;
        } catch (Exception $e) {
            error_log("Error in getProductFeedbacks: " . $e->getMessage());
            return [];
        }
    }
}
