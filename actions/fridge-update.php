<?php
/**
 * actions/fridge-update.php
 * ------------------------------------------------------------
 * Handles the fridge form submission from fridge.php.
 *
 * Logic:
 *   1. Validate: must be POST, must be logged in
 *   2. Sanitise the submitted ingredient IDs
 *   3. Cross-check each ID exists in the ingredients table
 *   4. DELETE all existing fridge rows for this user
 *   5. INSERT the newly selected ingredient rows
 *   6. Redirect back to fridge.php with ?saved=1
 * ------------------------------------------------------------
 */

require_once '../config/session.php';
require_once '../config/db.php';

// Only accept POST 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Location: ../fridge.php');
    exit;
}

// Must be logged in 
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Sanitise submitted ingredient IDs 
// $_POST['ingredients'] is an array of checkbox values (or absent if none ticked)
$submitted = $_POST['ingredients'] ?? [];

// Cast every value to int and remove anything <= 0
$submitted = array_values(
    array_filter(
        array_map('intval', (array) $submitted),
        fn($id) => $id > 0
    )
);

// Validate against actual ingredient IDs in the DB 
// Prevents inserting arbitrary IDs that don't exist.
$validIds = [];
if (!empty($submitted)) {
    // Build a safe IN clause
    $placeholders = implode(',', array_fill(0, count($submitted), '?'));
    $stmt = $pdo->prepare(
        "SELECT ingredient_id FROM ingredients WHERE ingredient_id IN ($placeholders)"
    );
    $stmt->execute($submitted);
    $validIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'ingredient_id');
    $validIds = array_map('intval', $validIds);
}

// Run as a transaction
try {
    $pdo->beginTransaction();

    // Step 1: Delete all current fridge entries for this user
    $pdo->prepare("DELETE FROM user_fridge WHERE user_id = ?")
        ->execute([$userId]);

    // Step 2: Insert the newly selected ingredients (if any)
    if (!empty($validIds)) {
        $placeholders = implode(',', array_fill(0, count($validIds), '(?, ?)'));
        $values = [];
        foreach ($validIds as $ingId) {
            $values[] = $userId;
            $values[] = $ingId;
        }
        $pdo->prepare(
            "INSERT INTO user_fridge (user_id, ingredient_id) VALUES $placeholders"
        )->execute($values);
    }

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    // Redirect back with an error flag
    header('Location: ../fridge.php?error=1');
    exit;
}

// Success: redirect back to fridge page 
header('Location: ../fridge.php?saved=1');
exit;