<?php
// データベース接続設定
$host = 'db'; 
$db   = 'test_db'; 
$user = 'root'; 
$pass = 'password';

try {
    // MySQLへの接続（PDOという仕組みを使用）
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // 1. 家計簿用のテーブルがなければ作成する
    $pdo->exec("CREATE TABLE IF NOT EXISTS kakeibo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item VARCHAR(100),
        amount INT,
        category VARCHAR(50),
        created_at DATE
    )");

    // 2. フォームからデータ（品目と金額）が送られてきたら登録
    if (!empty($_POST['item']) && !empty($_POST['amount'])) {
        $stmt = $pdo->prepare("INSERT INTO kakeibo (item, amount, category, created_at) VALUES (?, ?, ?, CURDATE())");
        $stmt->execute([$_POST['item'], $_POST['amount'], $_POST['category']]);
    }

    // 3. 消去ボタンが押されたら、そのIDのデータを削除
    if (!empty($_POST['delete_id'])) {
        $stmt = $pdo->prepare("DELETE FROM kakeibo WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
    }

    // 4. 表示用のデータ取得（新しい順）
    $items = $pdo->query("SELECT * FROM kakeibo ORDER BY id DESC")->fetchAll();
    
    // 5. 合計金額を計算（SQLのSUM関数を使用）
    $total = $pdo->query("SELECT SUM(amount) FROM kakeibo")->fetchColumn();

} catch (PDOException $e) { 
    die("エラーが発生しました: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>爆速家計簿 Docker版</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f0f2f5; padding: 20px; color: #333; }
        .container { max-width: 500px; margin: auto; }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .total { font-size: 1.8em; color: #2c3e50; text-align: center; border-bottom: 3px solid #3498db; padding-bottom: 15px; margin-top: 0; }
        input, select, button { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 1em; }
        button { background: #3498db; color: white; border: none; cursor: pointer; font-weight: bold; transition: background 0.3s; }
        button:hover { background: #2980b9; }
        .item-list { list-style: none; padding: 0; }
        .item-card { background: #fff; padding: 15px; border-left: 6px solid #3498db; border-radius: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .item-info strong { display: block; font-size: 1.1em; }
        .item-info small { color: #7f8c8d; }
        .amount { font-size: 1.2em; font-weight: bold; color: #2c3e50; }
        .delete-btn { background: none; color: #e74c3c; width: auto; padding: 5px 10px; margin: 0; font-size: 0.9em; border: 1px solid #e74c3c; border-radius: 5px; }
        .delete-btn:hover { background: #e74c3c; color: white; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h2 class="total">合計: ¥<?php echo number_format((float)$total); ?></h2>
        
        <form method="POST">
            <input type="text" name="item" placeholder="何を買った？ (例: コンビニ)" required>
            <input type="number" name="amount" placeholder="金額 (例: 500)" required>
            <select name="category">
                <option value="食費">🍎 食費</option>
                <option value="生活用品">🏠 生活用品</option>
                <option value="趣味・娯楽">🎮 趣味・娯楽</option>
                <option value="その他">📦 その他</option>
            </select>
            <button type="submit">家計簿に記録する</button>
        </form>
    </div>

    <h3>💸 最近の履歴</h3>
    <div class="item-list">
        <?php foreach ($items as $i): ?>
            <div class="item-card">
                <div class="item-info">
                    <strong><?php echo htmlspecialchars($i['item']); ?></strong>
                    <small><?php echo htmlspecialchars($i['category']); ?> | <?php echo $i['created_at']; ?></small>
                    <div class="amount">¥<?php echo number_format($i['amount']); ?></div>
                </div>
                <form method="POST" onsubmit="return confirm('この項目を削除しますか？');">
                    <input type="hidden" name="delete_id" value="<?php echo $i['id']; ?>">
                    <button type="submit" class="delete-btn">消去</button>
                </form>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($items)): ?>
            <p style="text-align:center; color: #95a5a6;">まだデータがありません。上のフォームから登録しましょう！</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
