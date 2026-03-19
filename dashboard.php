<?php
require_once 'config/session.php';
require_once 'config/db.php';

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
            r.title, r.image_url, r.calories,
            r.prep_time_min, r.cook_time_min
     FROM v_fridge_match fm
     JOIN recipes r ON r.recipe_id = fm.recipe_id
     WHERE fm.user_id = ?
     GROUP BY fm.recipe_id, fm.match_pct, fm.matched_ingredients,
              fm.total_ingredients, r.title, r.image_url,
              r.calories, r.prep_time_min, r.cook_time_min
     ORDER BY fm.match_pct DESC
     LIMIT 3"
);
$stmt->execute([$userId]);
$topMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Recent favourites (last 4)
$stmt = $pdo->prepare(
    "SELECT r.recipe_id, r.title, r.image_url,
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealMate Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            color: black;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
            color:#1f3b2d;
        }

        .page-header p {
            color: grey;
            font-size: 15px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        }

        .stat-card h3 {
            font-size: 15px;
            color: grey;
            margin-bottom: 10px;
        }

        .stat-card .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: black;
        }

        .section-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card h2 {
            font-size: 20px;
            margin-bottom: 16px;
            color: darkblue;
        }

        .meal-list, .recipe-list, .fav-list, .ingredient-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .meal-item, .recipe-item, .fav-item {
            display: flex;
            gap: 14px;
            align-items: center;
            padding: 12px;
            border: 1px solid #eee;
            border-radius: 12px;
            background: white;
        }

        .meal-meta, .recipe-meta, .fav-meta {
            flex: 1;
        }

        .meal-meta h4, .recipe-meta h4, .fav-meta h4 {
            font-size: 16px;
            margin-bottom: 6px;
        }

        .meal-meta p, .recipe-meta p, .fav-meta p {
            font-size: 14px;
            color: black;
            margin-bottom: 4px;
        }

        .recipe-item img, .fav-item img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
            background: white;
        }

        .match-badge {
            display: inline-block;
            background: whitesmoke;
            color: lightcoral;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: bold;
            margin-top: 6px;
        }

        .ingredient-list {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 10px;
        }

        .ingredient-tag {
            background: whitesmoke;
            color: black;
            border: 1px solid whitesmoke;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 14px;
        }

        .empty-state {
            font-size: 14px;
            color: black;
            background: lightgray;
            border: 1px dashed lightgray;
            padding: 16px;
            border-radius: 10px;
        }

        .card-footer-link {
            display: inline-block;
            margin-top: 16px;
            text-decoration: none;
            color: lightcoral;
            font-weight: bold;
        }

        .card-footer-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            .section-grid, .bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Top navbar */
        .top-nav {
            background: white;
            border-bottom: 1px solid grey;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .brand {
            font-size: 24px;
            font-weight: 700;
            color: darkblue;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            color: black;
            font-weight: 600;
            font-size: 15px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: 0.2s ease;
        }

        .nav-links a:hover {
            background: whitesmoke;
            color: black;
        }

        .nav-links a.active {
            background: black;
            color: white;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .profile-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: grey;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            overflow: hidden;
        }

        .profile-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .signout-btn {
            text-decoration: none;
            background: white;
            color: black;
            font-weight: 600;
            padding: 9px 14px;
            border-radius: 8px;
            transition: 0.2s ease;
        }

        .signout-btn:hover {
            background: lightgray;
        }

        @media (max-width: 900px) {
            .nav-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-left, .nav-right {
                width: 100%;
                justify-content: space-between;
            }

            .nav-left {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .nav-links {
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
    <div class="nav-inner">
        <div class="nav-left">
            <a href="dashboard.php" class="brand">MealMate</a>

            <div class="nav-links">
                <a href="recipes.php">Recipes</a>
                <a href="fridge.php">Fridge</a>
                <a href="meal-planner.php">Planner</a>
                <a href="favourites.php">Favourites</a>
            </div>
        </div>

        <div class="nav-right">
            <a href="profile.php" class="profile-circle" title="My Profile">
                <?= e($profileInitial) ?>
            </a>
            <a href="logout.php" class="signout-btn">Sign Out</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1>Welcome back!</h1>
    </div>

    <!-- Summary cards -->
    <div class="stats-grid">
        <div class="card stat-card">
            <h3>Favourite Recipes</h3>
            <div class="stat-number"><?= e($favCount) ?></div>
        </div>

        <div class="card stat-card">
            <h3>Fridge Ingredients</h3>
            <div class="stat-number"><?= e($fridgeCount) ?></div>
        </div>

        <div class="card stat-card">
            <h3>Meals Planned This Week</h3>
            <div class="stat-number"><?= e($weekMealCount) ?></div>
        </div>
    </div>

    <!-- Top section -->
    <div class="section-grid">
        <!-- Upcoming meals -->
        <div class="card">
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

            <a href="meal-planner.php" class="card-footer-link">Start planning! </a>
        </div>

        <!-- Fridge preview -->
        <div class="card">
            <h2>My Fridge</h2>

            <?php if (!empty($fridgePreview)): ?>
                <div class="ingredient-list">
                    <?php foreach ($fridgePreview as $ingredient): ?>
                        <span class="ingredient-tag"><?= e($ingredient) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    Your fridge is empty. Add ingredients to get recipe matches.
                </div>
            <?php endif; ?>

            <a href="fridge.php" class="card-footer-link">Update Fridge! </a>
        </div>
    </div>

    <!-- Bottom section -->
    <div class="bottom-grid">
        <!-- Top matches -->
        <div class="card">
            <h2>Top Recipe Matches</h2>

            <?php if (!empty($topMatches)): ?>
                <div class="recipe-list">
                    <?php foreach ($topMatches as $recipe): ?>
                        <div class="recipe-item">
                            <img
                                src="<?= e(!empty($recipe['image_url']) ? $recipe['image_url'] : 'https://via.placeholder.com/90x90?text=Recipe') ?>"
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
        </div>

        <!-- Recent favourites -->
        <div class="card">
            <h2>Recent Favourites</h2>

            <?php if (!empty($recentFavs)): ?>
                <div class="fav-list">
                    <?php foreach ($recentFavs as $fav): ?>
                        <div class="fav-item">
                            <img
                                src="<?= e(!empty($fav['image_url']) ? $fav['image_url'] : 'https://via.placeholder.com/90x90?text=Recipe') ?>"
                                alt="<?= e($fav['title']) ?>"
                            >
                            <div class="fav-meta">
                                <h4><?= e($fav['title']) ?></h4>
                                <p>
                                    Prep: <?= e($fav['prep_time_min']) ?> min |
                                    Cook: <?= e($fav['cook_time_min']) ?> min
                                </p>
                                <p>Calories: <?= e($fav['calories']) ?> kcal</p>
                                <p>Saved on: <?= e(date('d M Y', strtotime($fav['saved_at']))) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    No favourites yet.
                </div>
            <?php endif; ?>

            <a href="favourites.php" class="card-footer-link">Discover recipes </a>
        </div>
    </div>
</div>

</body>
</html>