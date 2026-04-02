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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Recipes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        table {
            border-collapse: collapse;
            margin-top: 16px;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            vertical-align: middle;
        }

        td.actions {
            white-space: nowrap;
        }

        td.actions form {
            display: inline-block;
            margin: 0 4px 0 0;
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
    </style>
</head>
<body>

    <h1>Pending Recipes</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($pendingRecipes)): ?>
        <p>No pending recipes found.</p>
        <p><a href="dashboard.php">Return to Admin Dashboard</a></p>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingRecipes as $recipe): ?>
                    <tr>
                        <td><?= htmlspecialchars($recipe['recipe_id']) ?></td>
                        <td><?= htmlspecialchars($recipe['title']) ?></td>
                        <td><?= htmlspecialchars($recipe['username']) ?></td>
                        <td><?= htmlspecialchars($recipe['email']) ?></td>
                        <td><?= htmlspecialchars($recipe['status']) ?></td>
                        <td><?= htmlspecialchars($recipe['created_at']) ?></td>
                        <td class="actions">
                            <form action="../actions/recipes/admin-approve.php" method="POST">
                                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recipe['recipe_id']) ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit">Approve</button>
                            </form>

                            <form action="../actions/recipes/admin-approve.php" method="POST">
                                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recipe['recipe_id']) ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top: 16px;">
            <a href="dashboard.php">Back to Dashboard</a>
        </p>
    <?php endif; ?>
</body>
</html>