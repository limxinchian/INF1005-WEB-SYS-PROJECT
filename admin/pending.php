<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../config/db.php';
        require_once __DIR__ . '/../includes/admin-guard.php';

        try {
            $stmt = $pdo->query("
                SELECT 
                    r.recipe_id,
                    r.title,
                    r.status,
                    r.created_at,
                    u.user_id,
                    u.username,
                    u.email
                FROM recipes r
                JOIN users u ON r.submitted_by = u.user_id
                WHERE r.status = 'pending'
                ORDER BY r.created_at DESC
            ");

            $pendingRecipes = $stmt->fetchAll();
        } catch (Throwable $e) {
            die('Failed to load pending recipes: ' . $e->getMessage());
        }
        $title = "MealMate - Pending Recipes";
        include_once '../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../includes/admin_nav.php'; ?>

    <h1 class="my-3">Pending Recipes</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($pendingRecipes)): ?>
        <p>No pending recipes found.</p>
        <p><a href="dashboard.php">Return to Admin Dashboard</a></p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" class="fw-bold text-nowrap">Recipe ID</th>
                    <th scope="col" class="fw-bold text-nowrap">Title</th>
                    <th scope="col" class="fw-bold text-nowrap">Submitted By</th>
                    <th scope="col" class="fw-bold text-nowrap">Email</th>
                    <th scope="col" class="fw-bold text-nowrap">Status</th>
                    <th scope="col" class="fw-bold text-nowrap">Created At</th>
                    <th scope="col" class="fw-bold text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($pendingRecipes) as $recipe): ?>
                    <tr>
                        <td><?= htmlspecialchars($recipe['recipe_id']) ?></td>
                        <td><?= htmlspecialchars($recipe['title']) ?></td>
                        <td><?= htmlspecialchars($recipe['username']) ?></td>
                        <td><?= htmlspecialchars($recipe['email']) ?></td>
                        <td><?= htmlspecialchars($recipe['status']) ?></td>
                        <td><?= htmlspecialchars($recipe['created_at']) ?></td>
                        <td class="actions text-nowrap">
                            <form action="../actions/recipes/admin-approve.php" method="POST">
                                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recipe['recipe_id']) ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-primary w-100">Approve</button>
                            </form>

                            <form class="mt-2" action="../actions/recipes/admin-approve.php" method="POST">
                                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recipe['recipe_id']) ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-danger w-100">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>