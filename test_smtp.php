<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'babeemoonstore@gmail.com';
    $mail->Password = 'hlsw gjpq smqt norf'; // Thay bằng mật khẩu ứng dụng
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('babeemoonstore@gmail.com', 'StreetStyle');
    $mail->addAddress('minhthinh06112001@gmail.com'); // Email thử nghiệm
    $mail->isHTML(true);
    $mail->Subject = 'Kiểm tra SMTP';
    $mail->Body = 'Đây là email kiểm tra từ PHPMailer.';
    $mail->send();
    echo 'Email đã được gửi thành công!';
} catch (Exception $e) {
    echo "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
}
?>