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

                $stmt = $pdo->prepare("
                    INSERT INTO dietary_tags (tag_name, tag_slug)
                    VALUES (?, ?)
                ");
                $stmt->execute([$tagName, $tagSlug]);

                header('Location: tags.php?message=' . urlencode('Tag added successfully.'));
                exit();
            }
        } catch (Throwable $e) {
            die('Failed to add tag: ' . $e->getMessage());
        }
        $title = "MealMate - Add Dietary Tag";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <main class="container-fluid px-3 py-4">
    <div class="container-fluid mt-3">
        <h1>Add Tag</h1>

        <form method="POST" class="d-flex flex-column pe-lg-20">
            <label for="tag_name" class="form-label mt-3 fs-large">Tag Name</label>
            <input type="text" id="tag_name" name="tag_name" class="form-control" required>

            <label for="tag_slug" class="form-label mt-3 fs-large">Tag Slug</label>
            <input type="text" id="tag_slug" name="tag_slug" class="form-control">

            <div class="actions mt-3 d-flex justify-content-end gap-2">
                <a href="tags.php" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Tag</button>
            </div>
        </form>
    </div>
    </main>
    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>