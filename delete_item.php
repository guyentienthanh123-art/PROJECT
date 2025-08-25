<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit;
}
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = $_POST['id'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (empty($item_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '項目IDがありません。']);
        exit;
    }

    try {
        $sql = "DELETE FROM japanese_items WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$item_id, $user_id]);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => '項目が正常に削除されました。']);
        } else {
            echo json_encode(['success' => false, 'message' => '項目の削除に失敗しました。']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '無効なリクエストメソッドです。']);
}
?>