<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Allergen</title>
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
    <h1>Add Allergen</h1>

    <p><a href="allergens.php">Back to Allergens</a></p>

    <form method="POST">
        <label for="allergen_name">Allergen Name</label>
        <input type="text" id="allergen_name" name="allergen_name" required>

        <label for="description">Description</label>
        <textarea id="description" name="description"></textarea>

        <div class="actions">
            <button type="submit">Add Allergen</button>
            <a href="allergens.php">Cancel</a>
        </div>
    </form>
</body>
</html>