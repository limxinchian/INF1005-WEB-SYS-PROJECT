<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Allergen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        form {
            max-width: 700px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
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
    <h1>Edit Allergen</h1>

    <p><a href="allergens.php">Back to Allergens</a></p>

    <form method="POST">
        <label for="allergen_name">Allergen Name</label>
        <input type="text" id="allergen_name" name="allergen_name" value="<?= htmlspecialchars($allergen['allergen_name']) ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description"><?= htmlspecialchars($allergen['description']) ?></textarea>

        <div class="actions">
            <button type="submit">Save Changes</button>
            <a href="allergens.php">Cancel</a>
        </div>
    </form>
</body>
</html>