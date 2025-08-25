<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit;
}

$user_id = $_SESSION['user_id'];
$type = $_POST['type'] ?? 'vocabulary';
$category = trim($_POST['category'] ?? '');
$level = $_POST['level'] ?? 'studying';
$japanese_text = trim($_POST['japanese_text'] ?? '');
$reading = trim($_POST['reading'] ?? '');
$meaning = trim($_POST['meaning'] ?? '');
$example = trim($_POST['example_sentence'] ?? '');

if (empty($japanese_text) || empty($meaning)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '日本語と意味は必須です。']);
    exit;
}

try {
    $sql = "
      INSERT INTO japanese_items
        (user_id, type, category, level, japanese_text, reading, meaning, example_sentence)
      VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $user_id, $type,
        $category === '' ? null : $category,
        $level,
        $japanese_text,
        $reading === '' ? null : $reading,
        $meaning,
        $example === '' ? null : $example
    ]);

    echo json_encode(['success' => true, 'message' => '項目が正常に追加されました。']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
}
?>