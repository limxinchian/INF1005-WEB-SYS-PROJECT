<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

        $ingredientId = $_GET['ingredient_id'] ?? null;

        if (!$ingredientId) {
            die('Ingredient ID is required.');
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ingredientName = trim($_POST['ingredient_name'] ?? '');

                if ($ingredientName === '') {
                    die('Ingredient name is required.');
                }

                $updateStmt = $pdo->prepare("
                    UPDATE ingredients
                    SET ingredient_name = ?, updated_at = NOW()
                    WHERE ingredient_id = ?
                ");
                $updateStmt->execute([$ingredientName, $ingredientId]);

                header('Location: ingredients.php?message=' . urlencode('Ingredient updated successfully.'));
                exit();
            }

            $stmt = $pdo->prepare("
                SELECT *
                FROM ingredients
                WHERE ingredient_id = ?
                LIMIT 1
            ");
            $stmt->execute([$ingredientId]);
            $ingredient = $stmt->fetch();

            if (!$ingredient) {
                die('Ingredient not found.');
            }
        } catch (Throwable $e) {
            die('Failed to load ingredient: ' . $e->getMessage());
        }
        $title = "MealMate - Edit Ingredient (" . $ingredient['ingredient_name'] . ")";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <div class="container-fluid mt-3">
        <h1>Edit Ingredient (<?= htmlspecialchars($ingredient['ingredient_name']) ?>)</h1>

        <form method="POST" class="d-flex flex-column pe-lg-20">
            <label for="ingredient_name" class="form-label mt-3 fs-large">Ingredient Name</label>
            <input class="form-control" type="text" id="ingredient_name" name="ingredient_name" value="<?= htmlspecialchars($ingredient['ingredient_name']) ?>" required>

            <div class="actions mt-3 d-flex justify-content-end gap-2">
                <a href="ingredients.php" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>