<!DOCTYPE html>
<html lang="en">
<head>
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

        $title = "MealMate - Allergens";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <main class="container-fluid px-3 py-4">
    <div class="container-fluid">
        <div class="mt-3 d-flex flex-row justify-content-between align-items-center">
            <h1>Allergens</h1>
            <a href="allergen-add.php" class="btn btn-primary">Add Allergen</a>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
        <?php endif; ?>

        <?php if (empty($allergens)): ?>
            <p>No allergens found.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col" class="fw-bold text-nowrap">Allergen ID</th>
                        <th scope="col" class="fw-bold text-nowrap">Allergen Name</th>
                        <th scope="col" class="fw-bold text-nowrap">Description</th>
                        <th scope="col" class="fw-bold text-nowrap">Created At</th>
                        <th scope="col" class="fw-bold text-nowrap">Updated At</th>
                        <th scope="col" class="fw-bold text-nowrap">Actions</th>
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
                                <a href="allergen-edit.php?allergen_id=<?= urlencode($allergen['allergen_id']) ?>" class="btn btn-warning w-50">Edit</a>

                                <form class="mt-1" action="../../actions/allergens/allergen-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this allergen?');">
                                    <input type="hidden" name="allergen_id" value="<?= htmlspecialchars($allergen['allergen_id']) ?>">
                                    <button type="submit" class="btn btn-danger w-50">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    </main>
</body>
</html>