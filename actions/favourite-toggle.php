<?php
/**
 * actions/favourite-toggle.php
 * ------------------------------------------------------------
 * AJAX-only endpoint — expects POST with recipe_id (int).
 * Checks if the recipe is already in favourite_recipes for
 * the current user:
 *   → if YES  : DELETE the row   (unfavourite)
 *   → if NO   : INSERT a new row (favourite)
 *
 * Returns JSON:
 *   { "success": true,  "is_favourite": true|false,  "count": int }
 *   { "success": false, "error": "message" }
 * ------------------------------------------------------------
 */

require_once '../config/session.php';
require_once '../config/db.php';

// Only accept POST 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Must be logged in 
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Validate recipe_id 
$recipeId = filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT);
if (!$recipeId || $recipeId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid recipe ID']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Verify the recipe exists and is approved
$check = $pdo->prepare("SELECT recipe_id FROM recipes WHERE recipe_id = ? AND status = 'approved'");
$check->execute([$recipeId]);
if (!$check->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Recipe not found']);
    exit;
}

// Check current favourite status
$exists = $pdo->prepare(
    "SELECT 1 FROM favourite_recipes WHERE user_id = ? AND recipe_id = ?"
);
$exists->execute([$userId, $recipeId]);
$isFavourited = (bool) $exists->fetchColumn();

// Toggle 
if ($isFavourited) {
    // Remove from favourites
    $pdo->prepare(
        "DELETE FROM favourite_recipes WHERE user_id = ? AND recipe_id = ?"
    )->execute([$userId, $recipeId]);
    $nowFavourited = false;
} else {
    // Add to favourites
    $pdo->prepare(
        "INSERT IGNORE INTO favourite_recipes (user_id, recipe_id) VALUES (?, ?)"
    )->execute([$userId, $recipeId]);
    $nowFavourited = true;
}

// Return updated total count for this user 
$countStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM favourite_recipes WHERE user_id = ?"
);
$countStmt->execute([$userId]);
$totalFavourites = (int) $countStmt->fetchColumn();

header('Content-Type: application/json');
echo json_encode([
    'success'      => true,
    'is_favourite' => $nowFavourited,
    'count'        => $totalFavourites,
]);