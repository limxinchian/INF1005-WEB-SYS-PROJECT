<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/recipes/recipes-trash.php');
    exit();
}

$recipeId = $_POST['recipe_id'] ?? null;

if (!$recipeId) {
    die('Recipe ID is required.');
}

try {
    $stmt = $pdo->prepare("
        UPDATE recipes
        SET deleted_at = NULL,
            deleted_by = NULL,
            updated_at = NOW()
        WHERE recipe_id = ?
          AND deleted_at IS NOT NULL
          AND deleted_at >= NOW() - INTERVAL 30 DAY
    ");
    $stmt->execute([$recipeId]);

    header('Location: ../../admin/recipes/recipes-trash.php?message=' . urlencode('Recipe restored successfully.'));
    exit();
} catch (Throwable $e) {
    die('Failed to restore recipe: ' . $e->getMessage());
}