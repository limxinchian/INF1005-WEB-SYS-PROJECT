<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/ingredients/ingredients.php');
    exit();
}

$ingredientId = $_POST['ingredient_id'] ?? null;

if (!$ingredientId) {
    die('Ingredient ID is required.');
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM ingredients
        WHERE ingredient_id = ?
    ");
    $stmt->execute([$ingredientId]);

    header('Location: ../../admin/ingredients/ingredients.php?message=' . urlencode('Ingredient deleted successfully.'));
    exit();
} catch (Throwable $e) {
    die('Failed to delete ingredient: ' . $e->getMessage());
}