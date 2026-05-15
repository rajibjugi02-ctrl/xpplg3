<?php
require_once 'includes/db.php';

$id = '026';
$email = 'rajibjugi02@gmail.com';
$password = 'zahir123';
$nisn = '026';

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$update = $pdo->prepare("UPDATE students SET email = ?, password = ?, nisn = ? WHERE id = ?");
if ($update->execute([$email, $hashedPassword, $nisn, $id])) {
    echo "Successfully updated account 026.";
} else {
    echo "Failed to update account 026.";
}
?>
