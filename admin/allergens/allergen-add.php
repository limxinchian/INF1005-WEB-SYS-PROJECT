<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $allergenName = trim($_POST['allergen_name'] ?? '');
                $description = trim($_POST['description'] ?? '');

                if ($allergenName === '') {
                    die('Allergen name is required.');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO allergens (allergen_name, description, created_at, updated_at)
                    VALUES (?, ?, NOW(), NOW())
                ");
                $stmt->execute([$allergenName, $description]);

                header('Location: allergens.php?message=' . urlencode('Allergen added successfully.'));
                exit();
            }
        } catch (Throwable $e) {
            die('Failed to add allergen: ' . $e->getMessage());
        }
        $title = "MealMate - Add Allergen";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <main class="container-fluid px-3 py-4">
    <div class="container-fluid mt-3">
        <h1>Add Allergen</h1>


        <form method="POST" class="d-flex flex-column pe-lg-20">
            <label for="allergen_name" class="form-label mt-3 fs-large">Allergen Name</label>
            <input class="form-control" type="text" id="allergen_name" name="allergen_name" required>

            <label for="description" class="form-label mt-3 fs-large">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>

            <div class="actions mt-3 d-flex justify-content-end gap-2">
                <a href="allergens.php" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Allergen</button>
            </div>
        </form>
    </div>
    </main>
</body>
</html>