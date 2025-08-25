<?php
// セッションを開始
session_start();

// データベース接続ファイルを読み込む
require_once 'db_connect.php'; 

// POSTリクエストかどうかを確認
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);

    // ユーザー名が空でないかチェック
    if (empty($username)) {
        $_SESSION['error_message'] = "ユーザー名を入力してください。";
        header("Location: index.html");
        exit;
    }

    try {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
        } else {
            $sql = "INSERT INTO users (username) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
        }

        $_SESSION['username'] = $username;

        header("Location: dashboard.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "データベースエラーが発生しました。";
        header("Location: index.html");
        exit;
    }
} else {
    header("Location: index.html");
    exit;
}