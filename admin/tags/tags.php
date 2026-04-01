<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/admin-guard.php';

try {
    $stmt = $pdo->query("
        SELECT
            tag_id,
            tag_name,
            tag_slug
        FROM dietary_tags
        ORDER BY tag_name ASC
    ");

    $tags = $stmt->fetchAll();
} catch (Throwable $e) {
    die('Failed to load tags: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dietary Tags</title>
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
        <a href="tag-add.php">Add Tag</a>
    </div>

    <h1>Dietary Tags</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($tags)): ?>
        <p>No tags found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Tag ID</th>
                    <th>Tag Name</th>
                    <th>Tag Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tags as $tag): ?>
                    <tr>
                        <td><?= htmlspecialchars($tag['tag_id']) ?></td>
                        <td><?= htmlspecialchars($tag['tag_name']) ?></td>
                        <td><?= htmlspecialchars($tag['tag_slug']) ?></td>
                        <td class="actions">
                            <a href="tag-edit.php?tag_id=<?= urlencode($tag['tag_id']) ?>">Edit</a>

                            <form action="../../actions/tags/tag-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this tag?');">
                                <input type="hidden" name="tag_id" value="<?= htmlspecialchars($tag['tag_id']) ?>">
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