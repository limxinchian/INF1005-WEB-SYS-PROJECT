<?php
require_once __DIR__ . '/../config/db.php';
// require_once __DIR__ . '/../includes/admin-guard.php';

try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalRecipes = $pdo->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
    $recentPendingStmt = $pdo->query("
    SELECT 
        r.recipe_id,
        r.title,
        r.created_at,
        u.username,
        u.email
    FROM recipes r
    JOIN users u ON r.submitted_by = u.user_id
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC
    LIMIT 5
");

    $recentPendingRecipes = $recentPendingStmt->fetchAll();

    $pendingRecipes = $pdo->query("
        SELECT COUNT(*) 
        FROM recipes 
        WHERE status = 'pending'
    ")->fetchColumn();

    $approvedRecipes = $pdo->query("
        SELECT COUNT(*) 
        FROM recipes 
        WHERE status = 'approved'
    ")->fetchColumn();

    $rejectedRecipes = $pdo->query("
        SELECT COUNT(*) 
        FROM recipes 
        WHERE status = 'rejected'
    ")->fetchColumn();
} catch (Throwable $e) {
    die('Failed to load dashboard data: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        h1 {
            margin-bottom: 20px;
        }

        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 30px;
        }

        .card {
            border: 1px solid #ccc;
            padding: 16px;
            width: 220px;
        }

        .card h2 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }

        .card p {
            font-size: 28px;
            margin: 0;
            font-weight: bold;
        }

        .links a {
            display: inline-block;
            margin-right: 12px;
        }
    </style>
</head>

<body>
    <h1>Admin Dashboard</h1>

    <div class="stats">
        <div class="card">
            <h2>Total Users</h2>
            <p><?= htmlspecialchars($totalUsers) ?></p>
        </div>

        <div class="card">
            <h2>Total Recipes</h2>
            <p><?= htmlspecialchars($totalRecipes) ?></p>
        </div>

        <div class="card">
            <h2>Pending Recipes</h2>
            <p><?= htmlspecialchars($pendingRecipes) ?></p>
        </div>

        <div class="card">
            <h2>Approved Recipes</h2>
            <p><?= htmlspecialchars($approvedRecipes) ?></p>
        </div>

        <div class="card">
            <h2>Rejected Recipes</h2>
            <p><?= htmlspecialchars($rejectedRecipes) ?></p>
        </div>
    </div>

    <div class="links">
        <a href="pending.php">View Pending Recipes</a>
        <a href="recipes/recipes.php">View All Recipes</a>   
        <a href="users/users.php">View All Users</a>
        <a href="ingredients/ingredients.php">Manage Ingredients</a>
        <a href="allergens/allergens.php">Manage Allergens</a>
        <a href="tags/tags.php">Manage Tags</a>
    </div>

    <br>

    <div class="links">
        <a href="recipes/recipes-trash.php">View Deleted Recipes</a>
        <a href="users/users-trash.php">View Deleted Users</a>
    </div>
    <h2>Recent Pending Recipes</h2>

    <?php if (empty($recentPendingRecipes)): ?>
        <p>No pending recipes at the moment.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Recipe ID</th>
                    <th>Title</th>
                    <th>Submitted By</th>
                    <th>Email</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentPendingRecipes as $recipe): ?>
                    <tr>
                        <td><?= htmlspecialchars($recipe['recipe_id']) ?></td>
                        <td><?= htmlspecialchars($recipe['title']) ?></td>
                        <td><?= htmlspecialchars($recipe['username']) ?></td>
                        <td><?= htmlspecialchars($recipe['email']) ?></td>
                        <td><?= htmlspecialchars($recipe['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>