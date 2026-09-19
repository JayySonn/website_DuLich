<?php
header('Content-Type: application/json');
include 'db.php'; 


$sql_tours = "SELECT TourName, Price, Duration, Description FROM Tours"; 
$res_tours = mysqli_query($conn, $sql_tours);
$tour_data = "";

if ($res_tours) {
    while($t = mysqli_fetch_assoc($res_tours)) {
        
        $tour_data .= "- Tên: " . $t['TourName'] . " | Giá: " . number_format($t['Price']) . "đ | Thời lượng: " . $t['Duration'] . "\n";
    }
}


$dataInput = json_decode(file_get_contents('php://input'), true);
$userMessage = $dataInput['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Bạn chưa nhập câu hỏi nè!']);
    exit;
}


$apiKey = "AIzaSyAzp8-jkcHhl7AI_bJYUei14xIpsj2g7ns"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;


$prompt = "Bạn là chuyên viên tư vấn của MienTay Travel. Dưới đây là TOÀN BỘ danh sách tour đang kinh doanh trên hệ thống của chúng tôi:\n\n" 
          . $tour_data . 
          "\nNhiệm vụ của bạn:\n
          1. Chỉ tư vấn dựa trên danh sách tour thật ở trên.\n
          2. Nếu khách hỏi tour không có, hãy báo hiện tại chưa có và gợi ý tour tương tự.\n
          3. Trả lời đầy đủ, chi tiết nhưng thân thiện.\n
          Khách hỏi: " . $userMessage;

$payload = [
    "contents" => [
        ["parts" => [["text" => $prompt]]]
    ]
];


$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['reply' => 'Lỗi kết nối: ' . $err]);
    exit;
}

$result = json_decode($response, true);


if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $botReply = $result['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['reply' => $botReply]);
} elseif (isset($result['error'])) {
    echo json_encode(['reply' => 'Lỗi Google: ' . $result['error']['message']]);
} else {
    echo json_encode(['reply' => 'AI đang bận, Sơn thử lại sau nhé!']);
}
?>