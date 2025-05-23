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
}
