<!DOCTYPE html>
<html lang="en">
<head>
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

        $title = "MealMate - Dietary Tags";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <div class="container-fluid">
        <div class="mt-3 d-flex flex-row justify-content-between align-items-center">
            <h1>Dietary Tags</h1>
            <div class="top-links">
                <a href="tag-add.php" class="btn btn-primary">Add Tag</a>
            </div>
        </div>
        <?php if (isset($_GET['message'])): ?>
            <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
        <?php endif; ?>

        <?php if (empty($tags)): ?>
            <p>No tags found.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col" class="fw-bold text-nowrap">Tag ID</th>
                        <th scope="col" class="fw-bold text-nowrap">Tag Name</th>
                        <th scope="col" class="fw-bold text-nowrap">Tag Slug</th>
                        <th scope="col" class="fw-bold text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tags as $tag): ?>
                        <tr>
                            <td><?= htmlspecialchars($tag['tag_id']) ?></td>
                            <td><?= htmlspecialchars($tag['tag_name']) ?></td>
                            <td><?= htmlspecialchars($tag['tag_slug']) ?></td>
                            <td class="actions text-center">
                                <a href="tag-edit.php?tag_id=<?= urlencode($tag['tag_id']) ?>" class="btn btn-warning w-50">Edit</a>

                                <form class="mt-1 d-flex justify-content-center" action="../../actions/tags/tag-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this tag?');">
                                    <input type="hidden" name="tag_id" value="<?= htmlspecialchars($tag['tag_id']) ?>">
                                    <button type="submit" class="btn btn-danger w-50">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>