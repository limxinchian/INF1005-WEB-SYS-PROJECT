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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Recipes</title>
    <style>
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
    </style>
</head>

<body>
    <div class="top-links">
        <a href="../dashboard.php">Back to Dashboard</a>
        <a href="../pending.php">View Pending Recipes</a>
        <a href="recipes-trash.php">View Deleted Recipes</a>
    </div>

    <h1>All Recipes</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($recipes)): ?>
        <p>No recipes found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Recipe ID</th>
                    <th>Title</th>
                    <th>Submitted By</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recipes as $recipe): ?>
                    <tr>
                        <td><?= htmlspecialchars($recipe['recipe_id']) ?></td>
                        <td><?= htmlspecialchars($recipe['title']) ?></td>
                        <td><?= htmlspecialchars($recipe['username']) ?></td>
                        <td><?= htmlspecialchars($recipe['email']) ?></td>
                        <td><?= htmlspecialchars($recipe['status']) ?></td>
                        <td><?= htmlspecialchars($recipe['created_at']) ?></td>
                        <td><?= htmlspecialchars($recipe['updated_at']) ?></td>
                        <td class="actions">
                            <a href="recipe-edit.php?recipe_id=<?= urlencode($recipe['recipe_id']) ?>">Edit</a>

                            <form action="../../actions/recipes/recipe-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
                                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recipe['recipe_id']) ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>