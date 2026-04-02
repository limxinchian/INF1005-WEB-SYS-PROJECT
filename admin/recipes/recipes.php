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
                u.username,
                u.email
            FROM recipes r
            JOIN users u ON r.submitted_by = u.user_id
            WHERE r.deleted_at IS NULL
            ORDER BY r.created_at DESC
        ");

            $recipes = $stmt->fetchAll();
        } catch (Throwable $e) {
            die('Failed to load recipes: ' . $e->getMessage());
        }

        $title = "MealMate - All Recipes";
        include_once '../../includes/header.php';
    ?>
    <!-- <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        table {
            border-collapse: collapse;
            margin-top: 16px;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            vertical-align: middle;
            text-align: left;
        }

        .top-links {
            margin-bottom: 16px;
        }

        .top-links a {
            margin-right: 12px;
        }

        .message {
            color: green;
            font-weight: bold;
            margin: 12px 0;
        }

        .actions {
            white-space: nowrap;
        }

        .actions a,
        .actions button {
            margin-right: 6px;
        }

        .actions form {
            display: inline-block;
            margin: 0;
        }
    </style> -->
</head>

<body>
    <?php include_once '../../includes/admin_nav.php'; ?>

    <h1>All Recipes</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($recipes)): ?>
        <p>No recipes found.</p>
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
                    <th scope="col" class="fw-bold text-nowrap">Updated At</th>
                    <th scope="col" class="fw-bold text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($recipes) as $recipe): ?>
                    <tr>
                        <td scope="row" class="fw-bold text-nowrap"><?= htmlspecialchars($recipe['recipe_id']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($recipe['title']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($recipe['username']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($recipe['email']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($recipe['status']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($recipe['created_at']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($recipe['updated_at']) ?></td>
                        <td class="actions">
                            <a href="recipe-edit.php?recipe_id=<?= urlencode($recipe['recipe_id']) ?>" class="btn btn-warning w-100">Edit</a>

                            <form class="mt-1" action="../../actions/recipes/recipe-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
                                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recipe['recipe_id']) ?>">
                                <button type="submit" class="btn btn-danger w-100">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>