<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

        try {
            $stmt = $pdo->query("
                SELECT
                    ingredient_id,
                    ingredient_name,
                    created_at,
                    updated_at
                FROM ingredients
                ORDER BY ingredient_name ASC
            ");

            $ingredients = $stmt->fetchAll();
        } catch (Throwable $e) {
            die('Failed to load ingredients: ' . $e->getMessage());
        }

        $title = "MealMate - Ingredients";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>

    <div class="mt-3 d-flex flex-row justify-content-between align-items-center">
        <h1>Ingredients</h1>
        <a href="ingredient-add.php" class="btn btn-primary">Add Ingredient</a>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($ingredients)): ?>
        <p>No ingredients found.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" class="fw-bold text-nowrap">Ingredient ID</th>
                    <th scope="col" class="fw-bold text-nowrap">Ingredient Name</th>
                    <th scope="col" class="fw-bold text-nowrap">Ingredient Name (As Displayed)</th>
                    <th scope="col" class="fw-bold text-nowrap">Created At</th>
                    <th scope="col" class="fw-bold text-nowrap">Updated At</th>
                    <th scope="col" class="fw-bold text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredients as $ingredient): ?>
                    <tr>
                        <td scope="row" class="fw-bold text-nowrap"><?= htmlspecialchars($ingredient['ingredient_id']) ?></td>
                        <td  class="text-nowrap"><?= htmlspecialchars($ingredient['ingredient_name']) ?></td>
                        <td  class="text-nowrap"><?= htmlspecialchars(ucwords($ingredient['ingredient_name'])) ?></td>
                        <td  class="text-nowrap"><?= htmlspecialchars($ingredient['created_at']) ?></td>
                        <td  class="text-nowrap"><?= htmlspecialchars($ingredient['updated_at']) ?></td>
                        <td class="actions">
                            <a href="ingredient-edit.php?ingredient_id=<?= urlencode($ingredient['ingredient_id']) ?>" class="btn btn-warning w-100">Edit</a>

                            <form class="mt-1" action="../../actions/ingredients/ingredient-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this ingredient?');">
                                <input type="hidden" name="ingredient_id" value="<?= htmlspecialchars($ingredient['ingredient_id']) ?>">
                                <button type="submit" class="btn btn-danger w-100">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>