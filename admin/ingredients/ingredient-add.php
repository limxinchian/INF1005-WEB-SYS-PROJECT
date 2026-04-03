<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ingredientName = trim($_POST['ingredient_name'] ?? '');

                if ($ingredientName === '') {
                    die('Ingredient name is required.');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO ingredients (ingredient_name, created_at, updated_at)
                    VALUES (?, NOW(), NOW())
                ");
                $stmt->execute([$ingredientName]);

                header('Location: ingredients.php?message=' . urlencode('Ingredient added successfully.'));
                exit();
            }
        } catch (Throwable $e) {
            die('Failed to add ingredient: ' . $e->getMessage());
        }

        $title = "MealMate - Add Ingredient";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <main class="container-fluid px-3 py-4">
    <div class="container-fluid mt-3">
        <h1>Add Ingredient</h1>

        <form method="POST" class="d-flex flex-column pe-lg-20">
            <label for="ingredient_name" class="form-label mt-3 fs-large">Ingredient Name</label>
            <input type="text" id="ingredient_name" name="ingredient_name" class="form-control" required>

            <div class="actions mt-3 d-flex justify-content-end gap-2">
                <a href="ingredients.php" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Ingredient</button>
            </div>
        </form>
    </div>
    </main>
    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>