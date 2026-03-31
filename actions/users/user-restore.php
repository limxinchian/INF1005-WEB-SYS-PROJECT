<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/users/users-trash.php');
    exit();
}

$userId = $_POST['user_id'] ?? null;

if (!$userId) {
    die('User ID is required.');
}

try {
    $stmt = $pdo->prepare("
        UPDATE users
        SET deleted_at = NULL,
            deleted_by = NULL,
            updated_at = NOW()
        WHERE user_id = ?
          AND deleted_at IS NOT NULL
          AND deleted_at >= NOW() - INTERVAL 30 DAY
    ");
    $stmt->execute([$userId]);

    header('Location: ../../admin/users/users-trash.php?message=' . urlencode('User restored successfully.'));
    exit();
} catch (Throwable $e) {
    die('Failed to restore user: ' . $e->getMessage());
}