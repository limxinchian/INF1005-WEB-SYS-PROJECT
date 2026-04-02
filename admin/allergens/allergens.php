<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/admin-guard.php';

try {
    $stmt = $pdo->query("
        SELECT
            allergen_id,
            allergen_name,
            description,
            created_at,
            updated_at
        FROM allergens
        ORDER BY allergen_name ASC
    ");

    $allergens = $stmt->fetchAll();
} catch (Throwable $e) {
    die('Failed to load allergens: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allergens</title>
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
        <a href="allergen-add.php">Add Allergen</a>
    </div>

    <h1>Allergens</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($allergens)): ?>
        <p>No allergens found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Allergen ID</th>
                    <th>Allergen Name</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allergens as $allergen): ?>
                    <tr>
                        <td><?= htmlspecialchars($allergen['allergen_id']) ?></td>
                        <td><?= htmlspecialchars($allergen['allergen_name']) ?></td>
                        <td><?= htmlspecialchars($allergen['description']) ?></td>
                        <td><?= htmlspecialchars($allergen['created_at']) ?></td>
                        <td><?= htmlspecialchars($allergen['updated_at']) ?></td>
                        <td class="actions">
                            <a href="allergen-edit.php?allergen_id=<?= urlencode($allergen['allergen_id']) ?>">Edit</a>

                            <form action="../../actions/allergens/allergen-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this allergen?');">
                                <input type="hidden" name="allergen_id" value="<?= htmlspecialchars($allergen['allergen_id']) ?>">
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