<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

$recipeId = $_GET['recipe_id'] ?? null;

if (!$recipeId) {
    die('Recipe ID is required.');
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        $prepTime = (int) ($_POST['prep_time_min'] ?? 0);
        $cookTime = (int) ($_POST['cook_time_min'] ?? 0);
        $servings = (int) ($_POST['servings'] ?? 0);
        $calories = (int) ($_POST['calories'] ?? 0);
        $protein = (int) ($_POST['protein_g'] ?? 0);
        $carbs = (int) ($_POST['carbs_g'] ?? 0);
        $fat = (int) ($_POST['fat_g'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if ($title === '' || !in_array($status, ['pending', 'approved', 'rejected'], true)) {
            die('Invalid form input.');
        }

        $updateStmt = $pdo->prepare("
            UPDATE recipes
            SET
                title = ?,
                description = ?,
                image_url = ?,
                prep_time_min = ?,
                cook_time_min = ?,
                servings = ?,
                calories = ?,
                protein_g = ?,
                carbs_g = ?,
                fat_g = ?,
                status = ?,
                updated_at = NOW()
            WHERE recipe_id = ?
        ");

        $updateStmt->execute([
            $title,
            $description,
            $imageUrl,
            $prepTime,
            $cookTime,
            $servings,
            $calories,
            $protein,
            $carbs,
            $fat,
            $status,
            $recipeId
        ]);

        header('Location: recipes.php?message=' . urlencode('Recipe updated successfully.'));
        exit();
    }

    $stmt = $pdo->prepare("
        SELECT *
        FROM recipes
        WHERE recipe_id = ?
        LIMIT 1
    ");
    $stmt->execute([$recipeId]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        die('Recipe not found.');
    }
} catch (Throwable $e) {
    die('Failed to load recipe: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        form {
            max-width: 700px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .actions {
            margin-top: 20px;
        }

        .actions button,
        .actions a {
            margin-right: 12px;
        }
    </style>
</head>
<body>
    <h1>Edit Recipe</h1>

    <p>
        <a href="recipes.php">Back to All Recipes</a>
    </p>

    <form method="POST">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description"><?= htmlspecialchars($recipe['description']) ?></textarea>

        <label for="image_url">Image URL</label>
        <input type="text" id="image_url" name="image_url" value="<?= htmlspecialchars($recipe['image_url']) ?>">

        <label for="prep_time_min">Prep Time (min)</label>
        <input type="number" id="prep_time_min" name="prep_time_min" value="<?= htmlspecialchars($recipe['prep_time_min']) ?>">

        <label for="cook_time_min">Cook Time (min)</label>
        <input type="number" id="cook_time_min" name="cook_time_min" value="<?= htmlspecialchars($recipe['cook_time_min']) ?>">

        <label for="servings">Servings</label>
        <input type="number" id="servings" name="servings" value="<?= htmlspecialchars($recipe['servings']) ?>">

        <label for="calories">Calories</label>
        <input type="number" id="calories" name="calories" value="<?= htmlspecialchars($recipe['calories']) ?>">

        <label for="protein_g">Protein (g)</label>
        <input type="number" id="protein_g" name="protein_g" value="<?= htmlspecialchars($recipe['protein_g']) ?>">

        <label for="carbs_g">Carbs (g)</label>
        <input type="number" id="carbs_g" name="carbs_g" value="<?= htmlspecialchars($recipe['carbs_g']) ?>">

        <label for="fat_g">Fat (g)</label>
        <input type="number" id="fat_g" name="fat_g" value="<?= htmlspecialchars($recipe['fat_g']) ?>">

        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="pending" <?= $recipe['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $recipe['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= $recipe['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>

        <div class="actions">
            <button type="submit">Save Changes</button>
            <a href="recipes.php">Cancel</a>
        </div>
    </form>
</body>
</html>