<?php
// includes/logger.php

function logActivity($pdo, $user_type, $user_identifier, $action) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_type, user_identifier, action) VALUES (?, ?, ?)");
        $stmt->execute([$user_type, $user_identifier, $action]);
        return true;
    } catch (PDOException $e) {
        // Silently fail if logging fails to not disrupt user experience
        // error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}
?>
