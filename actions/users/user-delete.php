<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/users/users.php');
    exit();
}

$userId = $_POST['user_id'] ?? null;

if (!$userId) {
    die('User ID is required.');
}

try {
    $deletedBy = $_SESSION['user_id'] ?? null;

    $stmt = $pdo->prepare("
        UPDATE users
        SET deleted_at = NOW(),
            deleted_by = ?,
            updated_at = NOW()
        WHERE user_id = ?
          AND deleted_at IS NULL
    ");
    $stmt->execute([$deletedBy, $userId]);

    header('Location: ../../admin/users/users.php?message=' . urlencode('User moved to trash successfully.'));
    exit();
} catch (Throwable $e) {
    die('Failed to delete user: ' . $e->getMessage());
}