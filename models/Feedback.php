<?php
class Feedback {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Thêm đánh giá mới
    public function addFeedback($user_id, $product_id, $content, $rating) {
        $query = "INSERT INTO feedback (user_id, product_id, content, rating) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$user_id, $product_id, $content, $rating]);
    }

    // Thêm ảnh cho đánh giá
    public function addFeedbackMedia($feedback_id, $file_path, $file_size) {
        $query = "INSERT INTO feedback_media (feedback_id, file_path, file_size) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$feedback_id, $file_path, $file_size]);
    }

    // Lấy số lượng ảnh hiện tại của đánh giá
    public function getPhotoCount($feedback_id) {
        $query = "SELECT COUNT(*) FROM feedback_media WHERE feedback_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$feedback_id]);
        return $stmt->fetchColumn();
    }

    // Lấy danh sách đánh giá theo sản phẩm
    public function getFeedbackByProduct($product_id) {
        $query = "SELECT f.id, f.user_id, f.content, f.rating, f.created_at, u.username
                  FROM feedback f
                  JOIN user u ON f.user_id = u.id
                  WHERE f.product_id = ?
                  ORDER BY f.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy ảnh theo feedback_id
    public function getMediaByFeedback($feedback_id) {
        $query = "SELECT id, file_path, file_size, created_at
                  FROM feedback_media
                  WHERE feedback_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$feedback_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Kiểm tra người dùng đã mua sản phẩm
    public function hasPurchased($user_id, $product_id) {
        $query = "SELECT COUNT(*) 
                  FROM orders o
                  JOIN order_details od ON o.id = od.order_id
                  WHERE o.user_id = ? AND od.product_id = ? AND o.status = 'completed'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id, $product_id]);
        return $stmt->fetchColumn() > 0;
    }
}
?>