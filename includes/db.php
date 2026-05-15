<?php
$host = '127.0.0.1';
$db   = 'webkelas_pplg3';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If database doesn't exist, try connecting without dbname to create it
    if ($e->getCode() == 1049) {
        try {
            $pdo_init = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $options);
            $pdo_init->exec("CREATE DATABASE IF NOT EXISTS `$db`");
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // Execute database.sql if possible
            $sql_file = __DIR__ . '/../database.sql';
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                $pdo->exec($sql);
            }
        } catch (\PDOException $e_init) {
            die("Database connection failed: " . $e_init->getMessage());
        }
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Auto-create project_comments table for the new feature
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `project_comments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `project_id` INT NOT NULL,
            `user_name` VARCHAR(100) NOT NULL,
            `comment` TEXT NOT NULL,
            `parent_id` INT DEFAULT NULL,
            `is_visible` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
} catch (\PDOException $e) {
    // Ignore error if table already exists or can't be created here
}
?>
