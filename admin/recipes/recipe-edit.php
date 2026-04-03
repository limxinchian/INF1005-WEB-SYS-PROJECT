<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

        $recipeId = $_GET['recipe_id'] ?? null;

        if (!$recipeId) {
            die('Recipe ID is required.');
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
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
        $title = "MealMate - Edit Recipe (" . $recipe['title'] . ")";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <main class="container-fluid px-3 py-4">
    <div class="container-fluid mt-3">
        <h1>Edit Recipe (<?= htmlspecialchars($recipe['title']) ?>)</h1>

        <form method="POST" class="d-flex flex-column pe-lg-20">
            <label for="title" class="form-label fs-large">Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($recipe['title']) ?>" required>

            <label for="description" class="form-label mt-3 fs-large">Description</label>
            <textarea id="description" name="description" class="form-control"><?= htmlspecialchars($recipe['description']) ?></textarea>

            <label for="prep_time_min" class="form-label mt-3 fs-large">Prep Time (min)</label>
            <input type="number" id="prep_time_min" name="prep_time_min" class="form-control" value="<?= htmlspecialchars($recipe['prep_time_min']) ?>">

            <label for="cook_time_min" class="form-label mt-3 fs-large">Cook Time (min)</label>
            <input type="number" id="cook_time_min" name="cook_time_min" class="form-control" value="<?= htmlspecialchars($recipe['cook_time_min']) ?>">

            <label for="servings" class="form-label mt-3 fs-large">Servings</label>
            <input type="number" id="servings" name="servings" class="form-control" value="<?= htmlspecialchars($recipe['servings']) ?>">

            <label for="calories" class="form-label mt-3 fs-large">Calories</label>
            <input type="number" id="calories" name="calories" class="form-control" value="<?= htmlspecialchars($recipe['calories']) ?>">

            <label for="protein_g" class="form-label mt-3 fs-large">Protein (g)</label>
            <input type="number" id="protein_g" name="protein_g" class="form-control" value="<?= htmlspecialchars($recipe['protein_g']) ?>">

            <label for="carbs_g" class="form-label mt-3 fs-large">Carbs (g)</label>
            <input type="number" id="carbs_g" name="carbs_g" class="form-control" value="<?= htmlspecialchars($recipe['carbs_g']) ?>">

            <label for="fat_g" class="form-label mt-3 fs-large">Fat (g)</label>
            <input type="number" id="fat_g" name="fat_g" class="form-control" value="<?= htmlspecialchars($recipe['fat_g']) ?>">

            <label for="status" class="form-label mt-3 fs-large">Status</label>
            <select id="status" name="status" class="form-control" required>
                <option value="pending" <?= $recipe['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $recipe['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $recipe['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>

            <div class="actions mt-3 d-flex justify-content-end gap-2">
                <a href="recipes.php" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

    </main>
    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>