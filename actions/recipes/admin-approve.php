<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/mailer.php';
require_once __DIR__ . '/../../includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/pending.php');
    exit();
}

$recipeId = $_POST['recipe_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$recipeId || !in_array($action, ['approve', 'reject'], true)) {
    die('Invalid request.');
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

try {
    $stmt = $pdo->prepare("
        SELECT 
            r.recipe_id,
            r.title,
            u.username,
            u.email
        FROM recipes r
        JOIN users u ON r.submitted_by = u.user_id
        WHERE r.recipe_id = ?
        LIMIT 1
    ");
    $stmt->execute([$recipeId]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        die('Recipe not found.');
    }

    $updateStmt = $pdo->prepare("
        UPDATE recipes
        SET status = ?, updated_at = NOW()
        WHERE recipe_id = ?
    ");
    $updateStmt->execute([$newStatus, $recipeId]);

    $mailWarning = '';

    try {
        if ($newStatus === 'approved') {
            sendRecipeApprovedEmail($recipe['email'], $recipe['username'], $recipe['title']);
        } else {
            sendRecipeRejectedEmail($recipe['email'], $recipe['username'], $recipe['title']);
        }
    } catch (Throwable $mailError) {
        $mailWarning = ' However, email notification could not be sent.';
    }

    header('Location: ../../admin/pending.php?message=' . urlencode("Recipe {$newStatus} successfully." . $mailWarning));
    exit();
} catch (Throwable $e) {
    die('Failed to update recipe status: ' . $e->getMessage());
}
