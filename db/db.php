<?php
$db_dir = __DIR__;
$db_file = $db_dir . '/wallet.db';

if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

try {
    $pdo = new PDO('sqlite:' . $db_file);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        description TEXT NOT NULL,
        amount REAL NOT NULL,
        category TEXT NOT NULL,
        date TEXT NOT NULL,
        payment_method TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
    )");

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE key = 'monthly_income'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (key, value) VALUES ('monthly_income', '1000000')");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE key = 'savings_goal'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (key, value) VALUES ('savings_goal', '200000')");
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function getDB() {
    global $pdo;
    return $pdo;
}