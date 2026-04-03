<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

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

        $title = "MealMate - Edit Tag (" . $tag['tag_name'] . ")";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <div class="container-fluid mt-3">
        <h1>Edit Tag (<?= htmlspecialchars($tag['tag_name']) ?>)</h1>

        <form method="POST" class="d-flex flex-column pe-lg-20">
            <label for="tag_name" class="form-label mt-3 fs-large">Tag Name</label>
            <input class="form-control" type="text" id="tag_name" name="tag_name" value="<?= htmlspecialchars($tag['tag_name']) ?>" required>

            <label for="tag_slug" class="form-label mt-3 fs-large">Tag Slug</label>
            <input class="form-control" type="text" id="tag_slug" name="tag_slug" value="<?= htmlspecialchars($tag['tag_slug']) ?>">

            <div class="actions mt-3 d-flex justify-content-end gap-2">
                <a href="tags.php" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>