<?php
require_once 'includes/db.php';

try {
    // 1. Add new columns to students table if they don't exist
    $columnsToAdd = [
        "nisn" => "VARCHAR(20) DEFAULT NULL",
        "kelas" => "VARCHAR(50) DEFAULT NULL",
        "email" => "VARCHAR(100) DEFAULT NULL",
        "password" => "VARCHAR(255) DEFAULT NULL",
        "portfolio_link" => "VARCHAR(255) DEFAULT NULL",
        "github_link" => "VARCHAR(255) DEFAULT NULL",
        "reset_token" => "VARCHAR(255) DEFAULT NULL",
        "reset_expires" => "DATETIME DEFAULT NULL"
    ];

    foreach ($columnsToAdd as $col => $def) {
        try {
            $pdo->exec("ALTER TABLE `students` ADD COLUMN `$col` $def");
            echo "Added column $col to students.\n";
        } catch (PDOException $e) {
            // Column might already exist
            echo "Column $col already exists or error: " . $e->getMessage() . "\n";
        }
    }

    // 2. Create visitors table (optional, but good for tracking if they have persistent names/classes)
    // Actually, we can just track them in session, but let's make a table to be safe.
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `visitors` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `kelas` VARCHAR(50) NOT NULL,
            `last_login` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Visitors table created/verified.\n";

    // 3. Create activity_logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `activity_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_type` ENUM('admin', 'student', 'visitor') NOT NULL,
            `user_identifier` VARCHAR(100) NOT NULL,
            `action` TEXT NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Activity logs table created/verified.\n";

    // 4. Add makers column to projects
    try {
        $pdo->exec("ALTER TABLE `projects` ADD COLUMN `makers` TEXT DEFAULT NULL");
        echo "Added makers column to projects.\n";
    } catch (PDOException $e) {
        echo "Column makers already exists or error: " . $e->getMessage() . "\n";
    }

    // 5. Create project_comments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `project_comments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `project_id` INT NOT NULL,
            `user_name` VARCHAR(100) NOT NULL,
            `comment` TEXT NOT NULL,
            `parent_id` INT DEFAULT NULL,
            `is_visible` TINYINT(1) DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "project_comments table created/verified.\n";

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage());
}
