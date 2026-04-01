<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/recipes/recipes.php');
    exit();
}

$recipeId = $_POST['recipe_id'] ?? null;

if (!$recipeId) {
    die('Recipe ID is required.');
}

try {
    $deletedBy = null;

    if (isset($_SESSION['user_id'])) {
        $deletedBy = $_SESSION['user_id'];
    }

    $stmt = $pdo->prepare("
        UPDATE recipes
        SET deleted_at = NOW(),
            deleted_by = ?,
            updated_at = NOW()
        WHERE recipe_id = ?
          AND deleted_at IS NULL
    ");
    $stmt->execute([$deletedBy, $recipeId]);

    header('Location: ../../admin/recipes/recipes.php?message=' . urlencode('Recipe moved to trash successfully.'));
    exit();
} catch (Throwable $e) {
    die('Failed to delete recipe: ' . $e->getMessage());
}
