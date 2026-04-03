<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

        try {
            $stmt = $pdo->query("
                SELECT
                    r.recipe_id,
                    r.title,
                    r.status,
                    r.created_at,
                    r.updated_at,
                    r.deleted_at,
                    u.username,
                    u.email,
                    TIMESTAMPDIFF(DAY, r.deleted_at, NOW()) AS days_since_deleted
                FROM recipes r
                JOIN users u ON r.submitted_by = u.user_id
                WHERE r.deleted_at IS NOT NULL
                ORDER BY r.deleted_at DESC
            ");

            $deletedRecipes = $stmt->fetchAll();
        } catch (Throwable $e) {
            die('Failed to load deleted recipes: ' . $e->getMessage());
        }
        $title = "MealMate - Deleted Recipes";
        include_once '../../includes/header.php';
    ?>
</head>

<body>
    <?php include_once '../../includes/admin_nav.php'; ?>\
    <main class="container-fluid px-3 py-4">

    <h1>Deleted Recipes</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($deletedRecipes)): ?>
        <p>No deleted recipes found.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" class="fw-bold text-nowrap">Recipe ID</th>
                    <th scope="col" class="fw-bold text-nowrap">Title</th>
                    <th scope="col" class="fw-bold text-nowrap">Submitted By</th>
                    <th scope="col" class="fw-bold text-nowrap">Email</th>
                    <th scope="col" class="fw-bold text-nowrap">Status</th>
                    <th scope="col" class="fw-bold text-nowrap">Deleted At</th>
                    <th scope="col" class="fw-bold text-nowrap">Days Since Deleted</th>
                    <th scope="col" class="fw-bold text-nowrap">Restore</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deletedRecipes as $recipe): ?>
                    <tr>
                        <td><?= htmlspecialchars($recipe['recipe_id']) ?></td>
                        <td><?= htmlspecialchars($recipe['title']) ?></td>
                        <td><?= htmlspecialchars($recipe['username']) ?></td>
                        <td><?= htmlspecialchars($recipe['email']) ?></td>
                        <td><?= htmlspecialchars($recipe['status']) ?></td>
                        <td><?= htmlspecialchars($recipe['deleted_at']) ?></td>
                        <td><?= htmlspecialchars($recipe['days_since_deleted']) ?></td>
                        <td>
                            <?php if ((int)$recipe['days_since_deleted'] < 30): ?>
                                <form action="../../actions/recipes/recipe-restore.php" method="POST">
                                    <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recipe['recipe_id']) ?>">
                                    <button type="submit" class="btn btn-warning w-100">Restore</button>
                                </form>
                            <?php else: ?>
                                <span>Restore expired</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    </main>
    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>