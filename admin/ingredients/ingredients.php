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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingredients</title>
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

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
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
        <a href="ingredient-add.php">Add Ingredient</a>
    </div>

    <h1>Ingredients</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($ingredients)): ?>
        <p>No ingredients found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Ingredient ID</th>
                    <th>Ingredient Name</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredients as $ingredient): ?>
                    <tr>
                        <td><?= htmlspecialchars($ingredient['ingredient_id']) ?></td>
                        <td><?= htmlspecialchars($ingredient['ingredient_name']) ?></td>
                        <td><?= htmlspecialchars($ingredient['created_at']) ?></td>
                        <td><?= htmlspecialchars($ingredient['updated_at']) ?></td>
                        <td class="actions">
                            <a href="ingredient-edit.php?ingredient_id=<?= urlencode($ingredient['ingredient_id']) ?>">Edit</a>

                            <form action="../../actions/ingredients/ingredient-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this ingredient?');">
                                <input type="hidden" name="ingredient_id" value="<?= htmlspecialchars($ingredient['ingredient_id']) ?>">
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