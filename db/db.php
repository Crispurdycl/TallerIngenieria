<?php
/**
 * Database connection setup
 * Supports SQLite out-of-the-box for digital wallet.
 */

// Define SQLite database path inside db/ folder
$db_dir = __DIR__;
$db_file = $db_dir . '/wallet.db';

// Ensure the directory exists
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

try {
    $pdo = new PDO('sqlite:' . $db_file);
    
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Initialize database tables
    // 1. Table for expenses
    $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        description TEXT NOT NULL,
        amount REAL NOT NULL,
        category TEXT NOT NULL,
        date TEXT NOT NULL,
        payment_method TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Table for settings (e.g. monthly income)
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
    )");

    // Insert default monthly income if not present
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE key = 'monthly_income'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (key, value) VALUES ('monthly_income', '1000000')");
    }

    // Insert default savings goal if not present
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE key = 'savings_goal'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (key, value) VALUES ('savings_goal', '200000')");
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Helper to get the PDO instance
 */
function getDB() {
    global $pdo;
    return $pdo;
}

