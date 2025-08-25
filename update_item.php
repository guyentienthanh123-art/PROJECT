<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit();
}

$user_id = $_SESSION['user_id'];

$item_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$type = $_POST['type'] ?? null;
$category = trim($_POST['category'] ?? '');
$level = $_POST['level'] ?? null;
$japanese_text = trim($_POST['japanese_text'] ?? '');
$reading = trim($_POST['reading'] ?? '');
$meaning = trim($_POST['meaning'] ?? '');
$example = trim($_POST['example_sentence'] ?? '');

if (empty($item_id) || empty($japanese_text) || empty($meaning)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '情報が不完全です。']);
    exit();
}

try {
    $sql = "UPDATE japanese_items SET type = ?, category = ?, level = ?, japanese_text = ?, reading = ?, meaning = ?, example_sentence = ? WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $type,
        $category === '' ? null : $category,
        $level,
        $japanese_text,
        $reading === '' ? null : $reading,
        $meaning,
        $example === '' ? null : $example,
        $item_id,
        $user_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {

        echo json_encode(['success' => false, 'message' => '更新する項目が見つかりませんでした。']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
}
?>