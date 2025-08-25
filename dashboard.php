<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Lấy các giá trị bộ lọc từ URL, mặc định là 'all'
$filter_type = $_GET['filter_type'] ?? 'all';
$filter_category = $_GET['filter_category'] ?? 'all';
$filter_level = $_GET['filter_level'] ?? 'all';
$search_query = $_GET['search'] ?? '';

// Lấy các danh mục riêng biệt để lọc
$stmt_categories = $pdo->prepare("SELECT DISTINCT category FROM japanese_items WHERE user_id = ? AND category IS NOT NULL AND category != '' ORDER BY category");
$stmt_categories->execute([$user_id]);
$categories = $stmt_categories->fetchAll(PDO::FETCH_COLUMN);

// Đếm số mục theo từng trạng thái
$stmt_learned = $pdo->prepare("SELECT COUNT(*) FROM japanese_items WHERE user_id = ? AND level = 'learned'");
$stmt_learned->execute([$user_id]);
$learned_count = $stmt_learned->fetchColumn();

$stmt_studying = $pdo->prepare("SELECT COUNT(*) FROM japanese_items WHERE user_id = ? AND level = 'studying'");
$stmt_studying->execute([$user_id]);
$studying_count = $stmt_studying->fetchColumn();

$stmt_forgotten = $pdo->prepare("SELECT COUNT(*) FROM japanese_items WHERE user_id = ? AND level = 'forgotten'");
$stmt_forgotten->execute([$user_id]);
$forgotten_count = $stmt_forgotten->fetchColumn();


// === BẮT ĐẦU PHẦN LOGIC MỚI - ĐƠN GIẢN HƠN ===
// Hàm tạo URL mới, lấy tất cả các tham số hiện tại từ URL và chỉ thay đổi một cái
function create_filter_link($param_to_change, $new_value) {
    $query_params = $_GET; // Lấy tất cả các bộ lọc đang hoạt động
    if ($new_value === 'all') {
        unset($query_params[$param_to_change]); // Xóa bộ lọc nếu chọn 'all'
    } else {
        $query_params[$param_to_change] = $new_value; // Thêm hoặc cập nhật bộ lọc
    }
    return '?' . http_build_query($query_params);
}
// === KẾT THÚC PHẦN LOGIC MỚI ===


// Truy vấn các mục đã được lọc
$sql_filtered = "SELECT * FROM japanese_items WHERE user_id = ?";
$params = [$user_id];

if ($filter_type !== 'all') {
    $sql_filtered .= " AND type = ?";
    $params[] = $filter_type;
}
if ($filter_category !== 'all') {
    $sql_filtered .= " AND category = ?";
    $params[] = $filter_category;
}
if ($filter_level !== 'all') {
    $sql_filtered .= " AND level = ?";
    $params[] = $filter_level;
}
if (!empty($search_query)) {
    $sql_filtered .= " AND (japanese_text LIKE ? OR reading LIKE ? OR meaning LIKE ? OR example_sentence LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql_filtered .= " ORDER BY created_at DESC";

$stmt_filtered = $pdo->prepare($sql_filtered);
$stmt_filtered->execute($params);
$items = $stmt_filtered->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日本語学習アプリ - ダッシュボード</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body class="dark-theme">
    <div class="dashboard-container">
        <header class="main-header">
            <div class="greeting">
                <h1>こんにちは、<?php echo htmlspecialchars($_SESSION['username']); ?>さん! 👋</h1>
                <p>今日も日本語学習を頑張りましょう</p>
            </div>
            <a href="logout.php" class="logout-btn">
                ログアウト <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </header>

        <section class="add-item-section">
            <h3>新しい学習項目を追加</h3>
            <form id="add-item-form">
                <div class="form-row-top">
                    <select name="type" class="form-select">
                        <option value="vocabulary">単語 (Vocabulary)</option>
                        <option value="grammar">文法 (Grammar)</option>
                        <option value="kanji">漢字 (Kanji)</option>
                    </select>
                    <input type="text" name="category" placeholder="カテゴリ (例: IT用語, 日常会話)" class="form-input" />
                    <select name="level" class="form-select">
                        <option value="studying">勉強中 (Studying)</option>
                        <option value="learned">覚えた (Learned)</option>
                        <option value="forgotten">忘れた (Forgotten)</option>
                    </select>
                </div>
                <input type="text" name="japanese_text" placeholder="日本語 (例: プログラミング)" required class="form-input" />
                <input type="text" name="reading" placeholder="ローマ字 / 読み方 (例: puroguramingu)" class="form-input" />
                <input type="text" name="meaning" placeholder="意味 / English (例: Programming)" required class="form-input" />
                <textarea name="example_sentence" placeholder="例文 / ノート (例: プログラミングは難しいです。)" class="form-textarea"></textarea>
                <button type="submit" class="add-btn"><i class="fa-solid fa-plus"></i> 追加</button>
            </form>
        </section>

        <section class="summary-section">
            <a href="<?php echo create_filter_link('filter_level', 'learned'); ?>" class="summary-card-link">
                <div class="summary-card">
                    <p>覚えた</p>
                    <span><?php echo $learned_count; ?></span>
                </div>
            </a>
            <a href="<?php echo create_filter_link('filter_level', 'studying'); ?>" class="summary-card-link">
                <div class="summary-card">
                    <p>勉強中</p>
                    <span><?php echo $studying_count; ?></span>
                </div>
            </a>
            <a href="<?php echo create_filter_link('filter_level', 'forgotten'); ?>" class="summary-card-link">
                <div class="summary-card">
                    <p>忘れた</p>
                    <span><?php echo $forgotten_count; ?></span>
                </div>
            </a>
        </section>

        <section class="item-list-section">
             <div class="filter-bar">
                 <form method="GET" action="dashboard.php" class="filter-form">
                    <div class="filter-group">
                        <span>タイプ:</span>
                      <a href="dashboard.php" class="filter-btn <?php echo !$is_any_filter_active ? 'active' : ''; ?>">すべて表示</a>
                        <a href="<?php echo create_filter_link('filter_type', 'vocabulary'); ?>" class="filter-btn <?php echo $filter_type === 'vocabulary' ? 'active' : ''; ?>">単語</a>
                        <a href="<?php echo create_filter_link('filter_type', 'grammar'); ?>" class="filter-btn <?php echo $filter_type === 'grammar' ? 'active' : ''; ?>">文法</a>
                        <a href="<?php echo create_filter_link('filter_type', 'kanji'); ?>" class="filter-btn <?php echo $filter_type === 'kanji' ? 'active' : ''; ?>">漢字</a>
                    </div>
                    
                    <?php if ($filter_level !== 'all'): ?><input type="hidden" name="filter_level" value="<?php echo htmlspecialchars($filter_level); ?>"><?php endif; ?>
                    <?php if ($filter_type !== 'all'): ?><input type="hidden" name="filter_type" value="<?php echo htmlspecialchars($filter_type); ?>"><?php endif; ?>

                    <div class="filter-group">
                        <span>カテゴリ:</span>
                        <select name="filter_category" class="filter-select" onchange="this.form.submit()">
                            <option value="all">すべて</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filter_category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="search-group">
                        <input type="text" name="search" placeholder="検索..." value="<?php echo htmlspecialchars($search_query); ?>" class="search-input" />
                        <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </form>
            </div>
            <h4>学習項目リスト</h4>
            <ul class="item-list">
                <?php if (empty($items)): ?>
                    <li class="item no-items">学習項目がありません。新しい項目を追加しましょう！</li>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <li class="item <?php echo htmlspecialchars($item['level']); ?>" data-id="<?php echo $item['id']; ?>">
                            <div class="item-header">
                                <div class="item-type-category">
                                    <span class="item-type type-<?php echo htmlspecialchars($item['type']); ?>">
                                        <?php echo htmlspecialchars(['vocabulary' => '単語', 'grammar' => '文法', 'kanji' => '漢字'][$item['type']]); ?>
                                    </span>
                                    <?php if (!empty($item['category'])): ?>
                                        <span class="item-category"><?php echo htmlspecialchars($item['category']); ?></span>
                                    <?php endif; ?>
                                    <span class="item-level level-<?php echo htmlspecialchars($item['level']); ?>">
                                        <?php echo htmlspecialchars(['learned' => '覚えた', 'studying' => '勉強中', 'forgotten' => '忘れた'][$item['level']]); ?>
                                    </span>
                                </div>
                                <div class="item-actions">
                                    <button class="check-progress-btn" data-id="<?php echo $item['id']; ?>" data-level="<?php echo htmlspecialchars($item['level']); ?>">
                                        <?php if ($item['level'] === 'studying'): ?><i class="fa-solid fa-check"></i><?php elseif ($item['level'] === 'learned'): ?><i class="fa-solid fa-undo"></i><?php endif; ?>
                                    </button>
                                    <button class="edit-btn" data-id="<?php echo $item['id']; ?>"><i class="fa-solid fa-edit"></i></button>
                                    <button class="delete-btn" data-id="<?php echo $item['id']; ?>"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="item-content">
                                <span class="item-japanese-text"><?php echo htmlspecialchars($item['japanese_text']); ?></span>
                                <?php if (!empty($item['reading'])): ?>
                                    <span class="item-reading">読み方: <?php echo htmlspecialchars($item['reading']); ?></span>
                                <?php endif; ?>
                                <span class="item-meaning">意味: <?php echo htmlspecialchars($item['meaning']); ?></span>
                                <?php if (!empty($item['example_sentence'])): ?>
                                    <p class="item-example">例文: <?php echo nl2br(htmlspecialchars($item['example_sentence'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>
    </div>

    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>学習項目を編集</h3>
                <button id="close-modal" class="close-modal">&times;</button>
            </div>
            <form id="edit-item-form">
                <input type="hidden" name="id" id="edit-item-id">
                <div class="form-row-top">
                    <select name="type" id="edit-type" class="form-select">
                        <option value="vocabulary">単語</option>
                        <option value="grammar">文法</option>
                        <option value="kanji">漢字</option>
                    </select>
                    <input type="text" name="category" id="edit-category" placeholder="カテゴリ" class="form-input" />
                    <select name="level" id="edit-level" class="form-select">
                        <option value="studying">勉強中</option>
                        <option value="learned">覚えた</option>
                        <option value="forgotten">忘れた</option>
                    </select>
                </div>
                <input type="text" name="japanese_text" id="edit-japanese-text" placeholder="日本語" required class="form-input" />
                <input type="text" name="reading" id="edit-reading" placeholder="ローマ字 / 読み方" class="form-input" />
                <input type="text" name="meaning" id="edit-meaning" placeholder="意味" required class="form-input" />
                <textarea name="example_sentence" id="edit-example-sentence" placeholder="例文 / ノート" class="form-textarea"></textarea>
                <button type="submit" class="add-btn"><i class="fa-solid fa-save"></i> 保存</button>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html> 