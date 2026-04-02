<?php
session_start();
require_once '../config/db.php';

// 1. VERIFY REQUEST METHOD
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../recipes/submit-recipe.php");
    exit();
}

// 2. SANITIZE & VALIDATE INPUTS
// Only trim — do NOT htmlspecialchars() before DB. Escape on OUTPUT instead.
$title         = trim($_POST['title'] ?? '');
$description   = trim($_POST['description'] ?? '');
// Match the exact name="" attribute from the HTML form
$prep_time_min = filter_input(INPUT_POST, 'prep_time_min', FILTER_VALIDATE_INT);
$tags          = isset($_POST['tags']) && is_array($_POST['tags']) ? $_POST['tags'] : [];

// Align variable name with the V2 database column
$submitted_by  = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
$status        = 'pending';

// 3. SERVER-SIDE VALIDATION
if (empty($title) || empty($description) || !$prep_time_min) {
    // Save input to session so form fields can be re-populated (sticky form)
    $_SESSION['old_input'] = [
        'title'         => $title,
        'description'   => $description,
        'prep_time_min' => $_POST['prep_time_min'] ?? '',
        'tags'          => $tags,
    ];
    $_SESSION['flash_error'] = "Please fill in all required fields.";
    header("Location: ../recipes/submit-recipe.php");
    exit();
}

// 4. INSERT MAIN RECIPE
try {
    // V2 update: Change prep_time to prep_time_min
    $stmt = $pdo->prepare("INSERT INTO recipes (submitted_by, title, description, prep_time_min, status) 
                           VALUES (?, ?, ?, ?, ?)");

    // V2 update: Make sure to use the exact variables defined above
    $stmt->execute([$submitted_by, $title, $description, $prep_time_min, $status]);

    $new_recipe_id = $pdo->lastInsertId();
    $stmt = null; // Release the statement

} catch (PDOException $e) {
    // Log error for admin debugging (optional but recommended)
    error_log("Recipe Insert Error: " . $e->getMessage());
    $_SESSION['flash_error'] = "Error submitting recipe. Please try again later.";
    header("Location: ../recipes/submit-recipe.php");
    exit();
}

// 5. LINK DIETARY TAGS (Relational Insert)
if (!empty($tags)) {
    // V2 update: Added the rest of your new dietary tags to the whitelist
    $allowed_tags = ['Vegan', 'Vegetarian', 'Halal', 'Nut-Free', 'Gluten-Free', 'Dairy-Free', 'Keto', 'Paleo'];

    // V2 update: The columns are tag_id and tag_name (not id and name)
    $stmt_get_tag  = $pdo->prepare("SELECT tag_id FROM dietary_tags WHERE tag_name = ?");
    $stmt_link_tag = $pdo->prepare("INSERT INTO recipe_dietary_tags (recipe_id, tag_id) VALUES (?, ?)");

    foreach ($tags as $tag_name) {
        // Whitelist check — skip any unexpected/tampered tag values
        if (!in_array($tag_name, $allowed_tags)) {
            continue;
        }

        $stmt_get_tag->execute([$tag_name]);
        $tag_row = $stmt_get_tag->fetch(PDO::FETCH_ASSOC);

        if ($tag_row) {
            // V2 update: Use the correct tag_id key
            $stmt_link_tag->execute([$new_recipe_id, $tag_row['tag_id']]);
        }
    }

    $stmt_get_tag  = null;
    $stmt_link_tag = null;
}

// 6. SUCCESS
$_SESSION['flash_success'] = "Recipe submitted successfully! It is now pending admin approval.";
header("Location: ../recipes/submit-recipe.php");
exit();
?>