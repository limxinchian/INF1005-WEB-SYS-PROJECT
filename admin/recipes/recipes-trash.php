<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Recipes</title>
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

        .actions form {
            display: inline-block;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="top-links">
        <a href="../dashboard.php">Back to Dashboard</a>
        <a href="recipes.php">Back to All Recipes</a>
    </div>

    <h1>Deleted Recipes</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($deletedRecipes)): ?>
        <p>No deleted recipes found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Recipe ID</th>
                    <th>Title</th>
                    <th>Submitted By</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Deleted At</th>
                    <th>Days Since Deleted</th>
                    <th>Restore</th>
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
                                    <button type="submit">Restore</button>
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
</body>

</html>