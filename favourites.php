<?php
/**
 * favourites.php
 * ------------------------------------------------------------
 * Shows all recipes the logged-in user has saved as favourites.
 * Features:
 *   - Live count badge in heading
 *   - Tag filter bar (built from the user's actual saved tags)
 *   - Sort by: Date saved, Name A–Z, Calories, Total time
 *   - Recipe cards with nutrition pills, dietary tags,
 *     "View Recipe" button, "+ Add to Plan" button,
 *     and an animated "Remove Favourite" button
 *   - Smooth card-removal animation on unfavourite (AJAX)
 *   - Empty state with CTA
 * ------------------------------------------------------------
 */

require_once 'config/session.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Sort & filter params 
$validSorts = ['saved_at' => 'fr.saved_at DESC', 'title' => 'r.title ASC', 'calories' => 'r.calories ASC', 'time' => '(r.prep_time_min + r.cook_time_min) ASC'];
$sortKey    = array_key_exists($_GET['sort'] ?? '', $validSorts) ? $_GET['sort'] : 'saved_at';
$orderBy    = $validSorts[$sortKey];
$filterTag  = trim($_GET['tag'] ?? '');

// Query favourites 
$stmt = $pdo->prepare(
    "SELECT
        r.recipe_id, r.title, r.image_url,
        r.prep_time_min, r.cook_time_min, r.servings,
        r.calories, r.protein_g, r.carbs_g, r.fat_g,
        u.username AS author,
        fr.saved_at,
        GROUP_CONCAT(DISTINCT dt.tag_name ORDER BY dt.tag_name SEPARATOR '||') AS tags
     FROM favourite_recipes fr
     JOIN recipes r ON r.recipe_id = fr.recipe_id
     JOIN users u   ON u.user_id   = r.submitted_by
     LEFT JOIN recipe_dietary_tags rdt ON rdt.recipe_id = r.recipe_id
     LEFT JOIN dietary_tags dt         ON dt.tag_id     = rdt.tag_id
     WHERE fr.user_id = ? AND r.status = 'approved'
     GROUP BY r.recipe_id, r.title, r.image_url,
              r.prep_time_min, r.cook_time_min, r.servings,
              r.calories, r.protein_g, r.carbs_g, r.fat_g,
              u.username, fr.saved_at
     ORDER BY $orderBy"
);
$stmt->execute([$userId]);
$allFavourites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Parse tags into arrays on each recipe
foreach ($allFavourites as &$r) {
    $r['tag_array'] = $r['tags'] ? explode('||', $r['tags']) : [];
}
unset($r);

// Apply tag filter 
$favourites = $allFavourites;
if ($filterTag) {
    $favourites = array_values(array_filter($allFavourites, fn($r) =>
        in_array($filterTag, $r['tag_array'])
    ));
}

// Collect all unique tags for filter bar
$allTags = [];
foreach ($allFavourites as $r) {
    foreach ($r['tag_array'] as $t) $allTags[$t] = true;
}
ksort($allTags);

// Totals 
$totalCount    = count($allFavourites);
$filteredCount = count($favourites);

$username = htmlspecialchars($_SESSION['username']);
$initials = strtoupper(substr($username, 0, 2));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealMate Favourites</title>
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

        a {
            text-decoration: none;
            color: inherit;
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
            color: #1f3b2d;
        }

        .page-header p {
            color: grey;
            font-size: 15px;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        }

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

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 46px;
            height: 46px;
            padding: 0 14px;
            border-radius: 999px;
            background: black;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        .toolbar {
            margin-bottom: 30px;
        }

        .toolbar-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tag-chip {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            background: whitesmoke;
            color: black;
            border: 1px solid #eee;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .tag-chip:hover {
            background: #eeeeee;
        }

        .tag-chip.active {
            background: black;
            color: white;
        }

        .sort-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sort-form label {
            font-size: 14px;
            font-weight: 600;
            color: black;
        }

        .sort-form select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            color: black;
        }

        .result-note {
            font-size: 14px;
            color: grey;
        }

        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .recipe-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .recipe-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .recipe-card.removing {
            opacity: 0;
            transform: scale(0.96);
        }

        .recipe-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: white;
        }

        .recipe-body {
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            flex: 1;
        }

        .recipe-top h3 {
            font-size: 20px;
            margin-bottom: 6px;
            color: #1f3b2d;
        }

        .recipe-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 14px;
            color: black;
        }

        .saved-date {
            font-size: 13px;
            color: grey;
        }

        .nutrition-pills,
        .diet-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .pill {
            background: whitesmoke;
            color: black;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }

        .diet-tag {
            background: whitesmoke;
            color: black;
            border: 1px solid #eee;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: auto;
        }

        .btn {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: 0.2s ease;
            text-align: center;
        }

        .btn-primary {
            background: black;
            color: white;
        }

        .btn-primary:hover {
            background: #222;
        }

        .btn-secondary {
            background: whitesmoke;
            color: black;
        }

        .btn-secondary:hover {
            background: #eeeeee;
        }

        .btn-danger {
            background: lightgray;
            color: black;
        }

        .btn-danger:hover {
            background: #d9d9d9;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            border-radius: 14px;
            background: lightgray;
            border: 1px dashed lightgray;
            color: black;
        }

        .empty-state h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #1f3b2d;
        }

        .empty-state p {
            font-size: 15px;
            margin-bottom: 18px;
            color: black;
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

<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<nav class="top-nav">
    <div class="nav-inner">
        <div class="nav-left">
            <a href="dashboard.php" class="brand">MealMate</a>

            <div class="nav-links">
                <a href="recipes.php" class="<?= $currentPage === 'recipes.php' ? 'active' : '' ?>">Recipes</a>
                <a href="fridge.php" class="<?= $currentPage === 'fridge.php' ? 'active' : '' ?>">Fridge</a>
                <a href="meal-planner.php" class="<?= $currentPage === 'meal-planner.php' ? 'active' : '' ?>">Planner</a>
                <a href="favourites.php" class="<?= $currentPage === 'favourites.php' ? 'active' : '' ?>">Favourites</a>
            </div>
        </div>

        <div class="nav-right">
            <a href="profile.php" class="profile-circle" title="My Profile">
                <?= htmlspecialchars($initials) ?>
            </a>
            <a href="logout.php" class="signout-btn">Sign Out</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <div class="header-row">
            <div>
                <h1>My Favourite Recipes</h1>
                <p>All the recipes you’ve saved in one place.</p>
            </div>
            <div class="count-badge"><?= $filteredCount ?></div>
        </div>
    </div>

    <div class="card toolbar">
        <div class="toolbar-top">
            <div class="filter-tags">
                <a href="favourites.php?sort=<?= urlencode($sortKey) ?>" class="tag-chip <?= $filterTag === '' ? 'active' : '' ?>">
                    All
                </a>

                <?php foreach (array_keys($allTags) as $tag): ?>
                    <a
                        href="favourites.php?sort=<?= urlencode($sortKey) ?>&tag=<?= urlencode($tag) ?>"
                        class="tag-chip <?= $filterTag === $tag ? 'active' : '' ?>"
                    >
                        <?= htmlspecialchars($tag) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <form method="GET" class="sort-form">
                <?php if ($filterTag !== ''): ?>
                    <input type="hidden" name="tag" value="<?= htmlspecialchars($filterTag) ?>">
                <?php endif; ?>

                <label for="sort">Sort by</label>
                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="saved_at" <?= $sortKey === 'saved_at' ? 'selected' : '' ?>>Date saved</option>
                    <option value="title" <?= $sortKey === 'title' ? 'selected' : '' ?>>Name A–Z</option>
                </select>
            </form>
        </div>

        <div class="result-note">
            Showing <?= $filteredCount ?> of <?= $totalCount ?> favourite recipe<?= $totalCount === 1 ? '' : 's' ?>.
        </div>
    </div>

    <?php if (!empty($favourites)): ?>
        <div class="recipes-grid" id="recipesGrid">
            <?php foreach ($favourites as $recipe): ?>
                <?php
                    $totalTime = (int)$recipe['prep_time_min'] + (int)$recipe['cook_time_min'];
                    $image = !empty($recipe['image_url']) ? $recipe['image_url'] : 'https://via.placeholder.com/600x400?text=Recipe';
                ?>
                <article class="recipe-card" data-recipe-id="<?= (int)$recipe['recipe_id'] ?>">
                    <img
                        src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($recipe['title']) ?>"
                        class="recipe-image"
                    >

                    <div class="recipe-body">
                        <div class="recipe-top">
                            <h3><?= htmlspecialchars($recipe['title']) ?></h3>
                            <div class="recipe-meta">
                                <span>By <?= htmlspecialchars($recipe['author']) ?></span>
                                <span><?= (int)$recipe['servings'] ?> serving<?= (int)$recipe['servings'] === 1 ? '' : 's' ?></span>
                                <span><?= $totalTime ?> mins</span>
                            </div>
                        </div>

                        <div class="saved-date">
                            Saved on <?= date('d M Y, h:i A', strtotime($recipe['saved_at'])) ?>
                        </div>

                        <div class="nutrition-pills">
                            <span class="pill"><?= (int)$recipe['calories'] ?> kcal</span>
                            <span class="pill"><?= (float)$recipe['protein_g'] ?>g protein</span>
                            <span class="pill"><?= (float)$recipe['carbs_g'] ?>g carbs</span>
                            <span class="pill"><?= (float)$recipe['fat_g'] ?>g fat</span>
                        </div>

                        <?php if (!empty($recipe['tag_array'])): ?>
                            <div class="diet-tags">
                                <?php foreach ($recipe['tag_array'] as $tag): ?>
                                    <span class="diet-tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="card-actions">
                            <a href="recipe-view.php?id=<?= (int)$recipe['recipe_id'] ?>" class="btn btn-primary">
                                View Recipe
                            </a>

                            <a href="meal-planner.php?recipe_id=<?= (int)$recipe['recipe_id'] ?>" class="btn btn-secondary">
                                + Add to Plan
                            </a>

                            <button
                                type="button"
                                class="btn btn-danger remove-favourite-btn"
                                data-recipe-id="<?= (int)$recipe['recipe_id'] ?>"
                            >
                                Remove Favourite
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No favourites found</h2>
            <p>
                <?php if ($filterTag !== ''): ?>
                    You don’t have any saved recipes under the tag <strong><?= htmlspecialchars($filterTag) ?></strong>.
                <?php else: ?>
                    You haven’t saved any recipes yet. Start exploring and add some favourites.
                <?php endif; ?>
            </p>
            <a href="recipes.php" class="btn btn-primary">Browse Recipes</a>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.remove-favourite-btn').forEach(button => {
    button.addEventListener('click', async function () {
        const recipeId = this.dataset.recipeId;
        const card = this.closest('.recipe-card');

        if (!confirm('Remove this recipe from your favourites?')) {
            return;
        }

        this.disabled = true;
        this.textContent = 'Removing...';

        try {
            await fetch('actions/favourite-toggle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'recipe_id=' + encodeURIComponent(recipeId)
            });

            card.classList.add('removing');
            setTimeout(() => {
                card.remove();
                window.location.reload();
            }, 300);

        } catch (error) {
            alert('Something went wrong while removing the favourite.');
            this.disabled = false;
            this.textContent = 'Remove Favourite';
        }
    });
});
</script>

</body>
</html>