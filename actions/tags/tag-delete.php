<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/tags/tags.php');
    exit();
}

$tagId = $_POST['tag_id'] ?? null;

if (!$tagId) {
    die('Tag ID is required.');
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM dietary_tags
        WHERE tag_id = ?
    ");
    $stmt->execute([$tagId]);

    header('Location: ../../admin/tags/tags.php?message=' . urlencode('Tag deleted successfully.'));
    exit();
} catch (Throwable $e) {
    die('Failed to delete tag: ' . $e->getMessage());
}