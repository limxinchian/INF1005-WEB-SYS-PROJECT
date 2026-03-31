<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

function makeSlug(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

$tagId = $_GET['tag_id'] ?? null;

if (!$tagId) {
    die('Tag ID is required.');
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tagName = trim($_POST['tag_name'] ?? '');
        $tagSlug = trim($_POST['tag_slug'] ?? '');

        if ($tagName === '') {
            die('Tag name is required.');
        }

        if ($tagSlug === '') {
            $tagSlug = makeSlug($tagName);
        }

        $updateStmt = $pdo->prepare("
            UPDATE dietary_tags
            SET tag_name = ?, tag_slug = ?
            WHERE tag_id = ?
        ");
        $updateStmt->execute([$tagName, $tagSlug, $tagId]);

        header('Location: tags.php?message=' . urlencode('Tag updated successfully.'));
        exit();
    }

    $stmt = $pdo->prepare("
        SELECT *
        FROM dietary_tags
        WHERE tag_id = ?
        LIMIT 1
    ");
    $stmt->execute([$tagId]);
    $tag = $stmt->fetch();

    if (!$tag) {
        die('Tag not found.');
    }
} catch (Throwable $e) {
    die('Failed to load tag: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tag</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        form {
            max-width: 600px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        .actions {
            margin-top: 20px;
        }

        .actions button,
        .actions a {
            margin-right: 12px;
        }
    </style>
</head>
<body>
    <h1>Edit Tag</h1>

    <p><a href="tags.php">Back to Tags</a></p>

    <form method="POST">
        <label for="tag_name">Tag Name</label>
        <input type="text" id="tag_name" name="tag_name" value="<?= htmlspecialchars($tag['tag_name']) ?>" required>

        <label for="tag_slug">Tag Slug</label>
        <input type="text" id="tag_slug" name="tag_slug" value="<?= htmlspecialchars($tag['tag_slug']) ?>">

        <div class="actions">
            <button type="submit">Save Changes</button>
            <a href="tags.php">Cancel</a>
        </div>
    </form>
</body>
</html>