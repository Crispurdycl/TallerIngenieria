<?php
/**
 * Database connection setup
 * Supports SQLite out-of-the-box, with optional MySQL configuration.
 */

// Define SQLite database path inside db/ folder
$db_dir = __DIR__;
$db_file = $db_dir . '/calcunota.db';

// Ensure the directory exists
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

try {
    // Connect to SQLite (fallback to MySQL can be done here if needed)
    $pdo = new PDO('sqlite:' . $db_file);
    
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Initialize database tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS subjects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        scale TEXT NOT NULL,
        passing_grade REAL NOT NULL,
        exam_enabled INTEGER NOT NULL DEFAULT 0,
        exam_weight REAL NOT NULL DEFAULT 30,
        equal_weights INTEGER NOT NULL DEFAULT 0,
        grades_json TEXT NOT NULL DEFAULT '[]',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

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
