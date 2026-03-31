<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ingredientName = trim($_POST['ingredient_name'] ?? '');

        if ($ingredientName === '') {
            die('Ingredient name is required.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO ingredients (ingredient_name, created_at, updated_at)
            VALUES (?, NOW(), NOW())
        ");
        $stmt->execute([$ingredientName]);

        header('Location: ingredients.php?message=' . urlencode('Ingredient added successfully.'));
        exit();
    }
} catch (Throwable $e) {
    die('Failed to add ingredient: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Ingredient</title>
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
    <h1>Add Ingredient</h1>

    <p><a href="ingredients.php">Back to Ingredients</a></p>

    <form method="POST">
        <label for="ingredient_name">Ingredient Name</label>
        <input type="text" id="ingredient_name" name="ingredient_name" required>

        <div class="actions">
            <button type="submit">Add Ingredient</button>
            <a href="ingredients.php">Cancel</a>
        </div>
    </form>
</body>
</html>