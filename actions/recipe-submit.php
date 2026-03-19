<?php
session_start();
require_once '../db.php'; 

// 1. VERIFY REQUEST METHOD
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../submit-recipe.php");
    exit();
}

// 2. SANITIZE & VALIDATE INPUTS
// Only trim — do NOT htmlspecialchars() before DB. Escape on OUTPUT instead.
$title        = trim($_POST['title'] ?? '');
$instructions = trim($_POST['instructions'] ?? '');
$prep_time    = filter_input(INPUT_POST, 'prep_time', FILTER_VALIDATE_INT);
$tags         = isset($_POST['tags']) && is_array($_POST['tags']) ? $_POST['tags'] : [];

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
$status  = 'pending';

// 3. SERVER-SIDE VALIDATION
if (empty($title) || empty($instructions) || !$prep_time) {
    // Save input to session so form fields can be re-populated (sticky form)
    $_SESSION['old_input'] = [
        'title'        => $title,
        'instructions' => $instructions,
        'prep_time'    => $_POST['prep_time'] ?? '',
        'tags'         => $tags,
    ];
    $_SESSION['flash_error'] = "Please fill in all required fields.";
    header("Location: ../submit-recipe.php");
    exit();
}

// 4. INSERT MAIN RECIPE
// PDO differences from MySQLi:
//   - No bind_param() — pass values directly into execute() as an array
//   - No $stmt->close() — just set $stmt = null or let it go out of scope
//   - Use try/catch because PDO throws exceptions on failure (if PDO::ERRMODE_EXCEPTION is set)
try {
    $stmt = $pdo->prepare("INSERT INTO recipes (user_id, title, instructions, prep_time, status) 
                           VALUES (?, ?, ?, ?, ?)");

    // Pass all values as an ordered array — no type hints needed like MySQLi's "issis"
    $stmt->execute([$user_id, $title, $instructions, $prep_time, $status]);

    // PDO uses lastInsertId() on the connection object, not on the statement
    $new_recipe_id = $pdo->lastInsertId();
    $stmt = null; // Release the statement

} catch (PDOException $e) {
    $_SESSION['flash_error'] = "Error submitting recipe. Please try again later.";
    header("Location: ../submit-recipe.php");
    exit();
}

// 5. LINK DIETARY TAGS (Relational Insert)
if (!empty($tags)) {
    $allowed_tags = ['Vegan', 'Halal', 'Nut-Free', 'Gluten-Free'];

    // Prepare both statements once, then reuse them in the loop (efficient)
    $stmt_get_tag  = $pdo->prepare("SELECT id FROM dietary_tags WHERE name = ?");
    $stmt_link_tag = $pdo->prepare("INSERT INTO recipe_dietary_tags (recipe_id, tag_id) VALUES (?, ?)");

    foreach ($tags as $tag_name) {
        // Whitelist check — skip any unexpected/tampered tag values
        if (!in_array($tag_name, $allowed_tags)) {
            continue;
        }

        // PDO: pass value as array into execute()
        $stmt_get_tag->execute([$tag_name]);

        // PDO: use fetch() with FETCH_ASSOC (or set as default in db_connect.php)
        $tag_row = $stmt_get_tag->fetch(PDO::FETCH_ASSOC);

        if ($tag_row) {
            $stmt_link_tag->execute([$new_recipe_id, $tag_row['id']]);
        }
    }

    $stmt_get_tag  = null;
    $stmt_link_tag = null;
}

// 6. SUCCESS
$_SESSION['flash_success'] = "Recipe submitted successfully! It is now pending admin approval.";
header("Location: ../submit-recipe.php");
exit();
?>
