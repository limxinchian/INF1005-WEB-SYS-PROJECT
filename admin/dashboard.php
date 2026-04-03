<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/admin-guard.php';

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

    $title = "Mealmate - Admin Dashboard";
    include_once '../includes/header.php';
    ?>
</head>

<body>
    <?php include_once '../includes/admin_nav.php'; ?>
    <main class="container-fluid px-3 py-4">
        <h1 class="mb-4">Mealmate Admin Dashboard</h1>
        <div class="container-fluid px-3 py-4">
            <div class="row g-3 mb-4 justify-content-center">
                <div class="col-12 col-md-4 col-lg">
                    <div class="card p-3 mb-0 h-100">
                        <h2>Total Users</h2>
                        <p class="fs-medium"><?= htmlspecialchars($totalUsers) ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg">
                    <div class="card p-3 mb-0 h-100">
                        <h2>Total Recipes</h2>
                        <p class="fs-medium"><?= htmlspecialchars($totalRecipes) ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg">
                    <div class="card p-3 mb-0 h-100">
                        <h2>Pending Recipes</h2>
                        <p class="fs-medium"><?= htmlspecialchars($pendingRecipes) ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg">
                    <div class="card p-3 mb-0 h-100">
                        <h2>Approved Recipes</h2>
                        <p class="fs-medium"><?= htmlspecialchars($approvedRecipes) ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg">
                    <div class="card p-3 mb-0 h-100">
                        <h2>Rejected Recipes</h2>
                        <p class="fs-medium"><?= htmlspecialchars($rejectedRecipes) ?></p>
                    </div>
                </div>
            </div>
            <br>

            <div>
                <h2>Recent Pending Recipes</h2>

                <div class="d-flex flex-row gap-3 mb-3">
                    <a href="recipes/pending.php">Pending Recipes</a>
                    <a href="recipes/recipes.php">All Recipes</a>
                </div>
                <?php if (empty($recentPendingRecipes)): ?>
                    <p>No pending recipes at the moment.</p>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col" class="fw-bold text-nowrap">Recipe ID</th>
                                <th scope="col" class="fw-bold text-nowrap">Title</th>
                                <th scope="col" class="fw-bold text-nowrap">Submitted By</th>
                                <th scope="col" class="fw-bold text-nowrap">Email</th>
                                <th scope="col" class="fw-bold text-nowrap">Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($recentPendingRecipes) as $recipe): ?>
                                <tr>
                                    <th scope="row" class="fw-bold text-nowrap"><?= htmlspecialchars($recipe['recipe_id']) ?></th>
                                    <td class="text-nowrap"><?= htmlspecialchars($recipe['title']) ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($recipe['username']) ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($recipe['email']) ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($recipe['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include_once '../includes/footer.php'; ?>
</body>

</html>