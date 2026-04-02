

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once 'config/session.php';
        require_once 'config/db.php';
        require_once 'helper/get-image-link.php';

        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // 1. Favourite count 
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favourite_recipes WHERE user_id = ?");
        $stmt->execute([$userId]);
        $favCount = (int)$stmt->fetchColumn();

        // 2. Fridge ingredient count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_fridge WHERE user_id = ?");
        $stmt->execute([$userId]);
        $fridgeCount = (int)$stmt->fetchColumn();

        // 3. This week's meal plan entries 
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM meal_plan_entries mpe
            JOIN meal_plans mp ON mp.plan_id = mpe.plan_id
            WHERE mp.user_id = ?
            AND mp.start_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            AND mp.end_date   >= CURDATE()"
        );
        $stmt->execute([$userId]);
        $weekMealCount = (int)$stmt->fetchColumn();

        // 4. Upcoming meals (next 4 entries)
        $stmt = $pdo->prepare(
            "SELECT mpe.day_of_week, mpe.meal_slot, r.title, r.recipe_id, r.calories
            FROM meal_plan_entries mpe
            JOIN meal_plans mp  ON mp.plan_id  = mpe.plan_id
            JOIN recipes r      ON r.recipe_id = mpe.recipe_id
            WHERE mp.user_id = ?
            AND mp.start_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            AND mp.end_date   >= CURDATE()
            ORDER BY FIELD(mpe.day_of_week,'Monday','Tuesday','Wednesday',
                            'Thursday','Friday','Saturday','Sunday'),
                    FIELD(mpe.meal_slot,'Breakfast','Lunch','Dinner','Snack')
            LIMIT 4"
        );
        $stmt->execute([$userId]);
        $upcomingMeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Top 3 fridge-matched recipes 
        $stmt = $pdo->prepare(
            "SELECT fm.recipe_id, fm.match_pct,
                    fm.matched_ingredients, fm.total_ingredients,
                    r.title, r.calories,
                    r.prep_time_min, r.cook_time_min
            FROM v_fridge_match fm
            JOIN recipes r ON r.recipe_id = fm.recipe_id
            WHERE fm.user_id = ?
            GROUP BY fm.recipe_id, fm.match_pct, fm.matched_ingredients,
                    fm.total_ingredients, r.title,
                    r.calories, r.prep_time_min, r.cook_time_min
            ORDER BY fm.match_pct DESC
            LIMIT 3"
        );
        $stmt->execute([$userId]);
        $topMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Recent favourites (last 4)
        $stmt = $pdo->prepare(
            "SELECT r.recipe_id, r.title,
                    r.prep_time_min, r.cook_time_min, r.calories, fr.saved_at
            FROM favourite_recipes fr
            JOIN recipes r ON r.recipe_id = fr.recipe_id
            WHERE fr.user_id = ? AND r.status = 'approved'
            ORDER BY fr.saved_at DESC LIMIT 4"
        );
        $stmt->execute([$userId]);
        $recentFavs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 7. Fridge ingredient names preview
        $stmt = $pdo->prepare(
            "SELECT i.ingredient_name
            FROM user_fridge uf
            JOIN ingredients i ON i.ingredient_id = uf.ingredient_id
            WHERE uf.user_id = ?
            ORDER BY uf.added_at DESC LIMIT 12"
        );
        $stmt->execute([$userId]);
        $fridgePreview = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'ingredient_name');

        function e($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        }

        $profileInitial = 'U';
        if (!empty($_SESSION['username'])) {
            $profileInitial = strtoupper(substr($_SESSION['username'], 0, 1));
        } elseif (!empty($_SESSION['email'])) {
            $profileInitial = strtoupper(substr($_SESSION['email'], 0, 1));
        }
        
        $title = 'MealMate - Dashboard';
        include_once 'includes/header.php';
    ?>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <?php require_once 'includes/nav.php'; ?>

    <div class="container">
        <div class="page-header mt-3">
            <h1>Welcome back, <?= currentUsername(); ?>!</h1>
        </div>

        <h2 class="mt-3">Summary</h1>
        <div class="d-flex flex-column flex-lg-row gap-4 mb-4">
            <div class="card stat-card flex-fill px-3 py-2">
                <h3 class="fs-medium">Favourite Recipes</h3>
                <div class="stat-number fs-x-large"><?= e($favCount) ?></div>
            </div>

            <div class="card stat-card flex-fill px-3 py-2">
                <h3 class="fs-medium">Fridge Ingredients</h3>
                <div class="stat-number fs-x-large"><?= e($fridgeCount) ?></div>
            </div>

            <div class="card stat-card flex-fill px-3 py-2">
                <h3 class="fs-medium">Meals Planned This Week</h3>
                <div class="stat-number fs-x-large"><?= e($weekMealCount) ?></div>
            </div>
        </div>

        <h2>Quick Actions</h1>
        <div class="d-flex flex-column flex-lg-row gap-4 mb-4">
            <a class="flex-fill px-3 py-2 text-center btn btn-primary text-decoration-none fs-large" href="submit-recipe.php">Add Recipe</a>
            <a class="flex-fill px-3 py-2 text-center btn btn-primary text-decoration-none fs-large" href="meal-planner.php">Add Meal</a>
            <a class="flex-fill px-3 py-2 text-center btn btn-primary text-decoration-none fs-large" href="favourites.php">Add Favourites</a>
        </div>
        
        <!-- Top section -->
        <div class="row row-cols-1 row-cols-lg-2 g-4 mt-3">
            <!-- Upcoming meals -->
            <div class="col"><div class="card vh-50 p-3">
                <h2>Upcoming Meals</h2>

                <?php if (!empty($upcomingMeals)): ?>
                    <div class="meal-list">
                        <?php foreach ($upcomingMeals as $meal): ?>
                            <div class="meal-item">
                                <div class="meal-meta">
                                    <h4><?= e($meal['title']) ?></h4>
                                    <p><strong>Day:</strong> <?= e($meal['day_of_week']) ?></p>
                                    <p><strong>Meal:</strong> <?= e($meal['meal_slot']) ?></p>
                                    <p><strong>Calories:</strong> <?= e($meal['calories']) ?> kcal</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        No meals planned yet.
                    </div>
                <?php endif; ?>

                <a href="meal-planner.php" class="mt-auto btn btn-primary">Start planning! </a>
            </div></div>

            <!-- Fridge preview -->
            <div class="col"><div class="card vh-50 p-3">
                <h2>My Fridge</h2>

                <?php if (!empty($fridgePreview)): ?>
                    <div class="ingredient-list">
                        <?php foreach ($fridgePreview as $ingredient): ?>
                            <span class="card p-2 mb-2"><?= e($ingredient) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        Your fridge is empty. Add ingredients to get recipe matches.
                    </div>
                <?php endif; ?>

                <a href="fridge.php" class="mt-auto btn btn-primary">Update Fridge! </a>
            </div></div>

            <!-- Top matches -->
            <div class="col"><div class="card vh-50 p-3">
                <h2>Top Recipe Matches</h2>

                <?php if (!empty($topMatches)): ?>
                    <div class="recipe-list">
                        <?php foreach ($topMatches as $recipe): ?>
                            <div class="recipe-item">
                                <img
                                    src="<?= e(getImageLink($recipe['title'], $recipe['recipe_id'])) ?>"
                                    alt="<?= e($recipe['title']) ?>"
                                >
                                <div class="recipe-meta">
                                    <h4><?= e($recipe['title']) ?></h4>
                                    <p>
                                        <?= e($recipe['matched_ingredients']) ?>/<?= e($recipe['total_ingredients']) ?>
                                        ingredients matched
                                    </p>
                                    <p>
                                        Prep: <?= e($recipe['prep_time_min']) ?> min |
                                        Cook: <?= e($recipe['cook_time_min']) ?> min
                                    </p>
                                    <p>Calories: <?= e($recipe['calories']) ?> kcal</p>
                                    <span class="match-badge"><?= e($recipe['match_pct']) ?>% Match</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        No recipe matches found yet. Add more ingredients to your fridge!
                    </div>
                <?php endif; ?>
            </div></div>

            <!-- Recent favourites -->
            <div class="col"><div class="card vh-50 p-3">
                <h2>Recent Favourites</h2>

                <?php if (!empty($recentFavs)): ?>
                    <div class="fav-list">
                        <?php foreach ($recentFavs as $fav): ?>
                            <div class="fav-item card p-2 mb-2">
                                <img
                                    src="<?= e(getImageLink($fav['title'], $fav['recipe_id'])) ?>"
                                    alt="<?= e($fav['title']) ?>"
                                >
                                <div class="fav-meta">
                                    <h4><?= e($fav['title']) ?></h4>
                                    
                                <div class="nutrition mb-2 d-flex flex-column flex-sm-row gap-1 gap-md-3 ms-0">
                                    <div>
                                        <img src="assets/images/icons/prep_time.svg" alt="prep_time"><span class="fs-small">Prep Time</span>
                                        <span><?= htmlspecialchars($fav['prep_time_min']) ?> mins</span>
                                    </div>
                                    <div>
                                        <img src="assets/images/icons/cook_time.svg" alt="cook_time"><span class="fs-small">Cook Time</span>
                                        <span><?= htmlspecialchars($fav['cook_time_min']) ?> mins</span>
                                    </div>
                                    <div>
                                        <img src="assets/images/icons/calories.svg" alt="calories"><span class="fs-small">Calories</span>
                                        <span><?= htmlspecialchars($fav['calories']) ?> kcal</span>
                                    </div>
                                </div>
                                <p class="mb-0">Saved on: <?= e(date('d M Y', strtotime($fav['saved_at']))) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        No favourites yet.
                    </div>
                <?php endif; ?>

                <a href="favourites.php" class="mt-auto btn btn-primary">Discover recipes </a>
            </div></div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>