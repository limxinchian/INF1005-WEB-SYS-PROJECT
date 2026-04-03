<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

        $allergenId = $_GET['allergen_id'] ?? null;

        if (!$allergenId) {
            die('Allergen ID is required.');
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $allergenName = trim($_POST['allergen_name'] ?? '');
                $description = trim($_POST['description'] ?? '');

                if ($allergenName === '') {
                    die('Allergen name is required.');
                }

                $updateStmt = $pdo->prepare("
                    UPDATE allergens
                    SET allergen_name = ?, description = ?, updated_at = NOW()
                    WHERE allergen_id = ?
                ");
                $updateStmt->execute([$allergenName, $description, $allergenId]);

                header('Location: allergens.php?message=' . urlencode('Allergen updated successfully.'));
                exit();
            }

            $stmt = $pdo->prepare("
                SELECT *
                FROM allergens
                WHERE allergen_id = ?
                LIMIT 1
            ");
            $stmt->execute([$allergenId]);
            $allergen = $stmt->fetch();

            if (!$allergen) {
                die('Allergen not found.');
            }
        } catch (Throwable $e) {
            die('Failed to load allergen: ' . $e->getMessage());
        }

        $title = "MealMate - Edit Allergen (" . $allergen['allergen_name'] . ")";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <main class="container-fluid px-3 py-4">
    <div class="container-fluid mt-3">
        <h1>Edit Allergen</h1>

        <form method="POST" class="d-flex flex-column pe-lg-20">
            <label for="allergen_name" class="form-label mt-3 fs-large">Allergen Name</label>
            <input class="form-control" type="text" id="allergen_name" name="allergen_name" value="<?= htmlspecialchars($allergen['allergen_name']) ?>" required>

            <label for="description" class="form-label mt-3 fs-large">Description</label>
            <textarea class="form-control" id="description" name="description"><?= htmlspecialchars($allergen['description']) ?></textarea>

            <div class="actions mt-3 d-flex justify-content-end gap-2">
                <a href="allergens.php" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
    </main>
    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>