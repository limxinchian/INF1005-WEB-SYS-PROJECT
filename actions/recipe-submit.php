<?php
session_start();
require_once '../config/db.php';
require_once '../vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// 1. VERIFY REQUEST METHOD
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../submit-recipe.php");
    exit();
}

// 2. SANITIZE & VALIDATE INPUTS
$title                 = trim($_POST['title'] ?? '');
$prep_time_min         = filter_input(INPUT_POST, 'prep_time_min', FILTER_VALIDATE_INT);
$cook_time_min         = filter_input(INPUT_POST, 'cook_time_min', FILTER_VALIDATE_INT);
$calories              = filter_input(INPUT_POST, 'calories', FILTER_VALIDATE_INT);
$protein_g             = filter_input(INPUT_POST, 'protein_g', FILTER_VALIDATE_INT);
$carbs_g               = filter_input(INPUT_POST, 'carbs_g', FILTER_VALIDATE_INT);
$fat_g                 = filter_input(INPUT_POST, 'fat_g', FILTER_VALIDATE_INT);
$tags                  = isset($_POST['tags']) && is_array($_POST['tags']) ? $_POST['tags'] : [];

// Grab the new STRICT IDs and Arrays
$ingredient_ids        = $_POST['ingredient_ids'] ?? [];
$ingredient_amounts = $_POST['ingredient_amounts'] ?? [];
$ingredient_units   = $_POST['ingredient_units'] ?? [];
$steps                 = $_POST['steps'] ?? [];

$submitted_by          = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
$status                = 'pending';

// 3. SERVER-SIDE VALIDATION
if (empty($title) || !$prep_time_min || !$cook_time_min || empty($ingredient_ids[0]) || empty($steps[0])) {
    $_SESSION['old_input'] = [
        'title'         => $title,
        'prep_time_min' => $_POST['prep_time_min'] ?? '',
        'tags'          => $tags,
    ];
    $_SESSION['flash_error'] = "Please fill in all required fields, including at least 1 ingredient and 1 step.";
    header("Location: ../submit-recipe.php");
    exit();
}

try {
    $pdo->beginTransaction();

    // 4. INSERT MAIN RECIPE
    $description = substr(trim($steps[0]), 0, 100) . '...';
    $stmt = $pdo->prepare("INSERT INTO recipes (submitted_by, title, description, prep_time_min, cook_time_min, calories, protein_g, carbs_g, fat_g, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$submitted_by, $title, $description, $prep_time_min, $cook_time_min, $calories, $protein_g, $carbs_g, $fat_g, $status]);
    $new_recipe_id = $pdo->lastInsertId();

// 5. STRICT INGREDIENTS INSERTION
    // We updated the SQL to include both the 'quantity' and 'unit' columns separately!
    $stmt_link_ing = $pdo->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit) VALUES (?, ?, ?, ?)");

    for ($i = 0; $i < count($ingredient_ids); $i++) {
        $ing_id = trim($ingredient_ids[$i]);
        $amount = trim($ingredient_amounts[$i] ?? '');
        $unit   = trim($ingredient_units[$i] ?? '');

        if (empty($ing_id)) continue;

        // Insert them directly into their own separate columns
        $stmt_link_ing->execute([$new_recipe_id, $ing_id, $amount, $unit]);
    }


// 6. DYNAMIC STEPS INSERTION
    // Changed 'step_number' to 'step_order' right here!
    $stmt_step = $pdo->prepare("INSERT INTO recipe_steps (recipe_id, step_order, instruction) VALUES (?, ?, ?)");
    for ($i = 0; $i < count($steps); $i++) {
        $instruction = trim($steps[$i]);
        if (empty($instruction)) continue;
        
        $step_num = $i + 1; 
        $stmt_step->execute([$new_recipe_id, $step_num, $instruction]);
    }

    // 7. LINK DIETARY TAGS
    if (!empty($tags)) {
        $allowed_tags = ['Vegan', 'Vegetarian', 'Halal', 'Nut-Free', 'Gluten-Free', 'Dairy-Free', 'Keto', 'Paleo'];
        $stmt_get_tag  = $pdo->prepare("SELECT tag_id FROM dietary_tags WHERE tag_name = ?");
        $stmt_link_tag = $pdo->prepare("INSERT INTO recipe_dietary_tags (recipe_id, tag_id) VALUES (?, ?)");

        foreach ($tags as $tag_name) {
            if (!in_array($tag_name, $allowed_tags)) continue;
            $stmt_get_tag->execute([$tag_name]);
            $tag_row = $stmt_get_tag->fetch(PDO::FETCH_ASSOC);

            if ($tag_row) {
                $stmt_link_tag->execute([$new_recipe_id, $tag_row['tag_id']]);
            }
        }
    }

    // 8. COMMIT THE TRANSACTION
    $pdo->commit();

    // 9. HANDLE IMAGE UPLOAD
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/recipes/';
        $file_tmp   = $_FILES['recipe_image']['tmp_name'];
        $file_name  = basename($_FILES['recipe_image']['name']);
        $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['png', 'jpg', 'jpeg'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = 'recipe_' . $new_recipe_id . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($file_tmp, $destination)) {
                $storage = new StorageClient([
                    'projectId' => $env['GOOGLE_PROJECT_ID'] ?? null,
                ]);
                $bucketName = 'mealmate_recipe_images';
                $recipeImgName = str_replace([' ', '/', ':', '?', ','], '_', $title);
                $blobName = $new_recipe_id . '_' . $recipeImgName;

                $bucket = $storage->bucket($bucketName);

                if (!$file = fopen($destination, 'r')) {
                    throw new \InvalidArgumentException('Unable to open file for reading');
                }
                $object = $bucket->upload($file, [
                    'name' => $blobName
                ]);
            }
        }
    }


    $_SESSION['flash_success'] = "Recipe submitted successfully! It is now pending admin approval.";
    header("Location: ../submit-recipe.php");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Recipe Insert Error: " . $e->getMessage());
    $_SESSION['flash_error'] = "Database Error: " . $e->getMessage();
    header("Location: ../submit-recipe.php");
    exit();
}
?>