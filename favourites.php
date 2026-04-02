<!DOCTYPE html>
<html lang="en">
<head>
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
                r.recipe_id, r.title, r.description,
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
            GROUP BY r.recipe_id, r.title, r.description,
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

        $title = "MealMate - My Favourites";
        require_once 'includes/header.php';
    ?>
    <script src="assets/js/favourites.js" defer></script>
    <link rel="stylesheet" href="assets/css/favourites.css">
</head>
<body>
    <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        require_once 'includes/nav.php';
    ?>

    <div class="container mt-3 mb-5">
        <div class="page-header">
            <div class="header-row">
                <h1>My Favourite Recipes</h1>
                <p class="mt-1">Current Recipes in Favourites: <span class="count-badge"><?= $filteredCount ?></span></p>
            </div>
        </div>
        
        <div class="d-flex flex-row justify-content-between align-items-center mb-3">
            <h2>All Recipes</h2>
            <a href="search.php" class="btn btn-primary fs-lg-5">Add more favourites</a>
        </div>
        <div class="card mt-1">
            <div class="d-flex flex-column flex-lg-row gap-2 mb-2 px-3 pt-3 align-items-center justify-content-between">
                <div>
                    <p>Showing: <?= $filteredCount ?> of <?= $totalCount ?> favourite recipe<?= $totalCount === 1 ? '' : 's' ?>.</p>
                </div>
                <div>
                    <select class="form-select" onchange="window.location.href='favourites.php?sort='+this.value+'<?= $filterTag ? '&tag='.urlencode($filterTag) : '' ?>'">
                        <?php
                            $sortLabels = ['saved_at' => 'Date Saved', 'title' => 'Name A–Z', 'calories' => 'Calories', 'time' => 'Total Time'];
                            foreach ($sortLabels as $key => $label): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $sortKey === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if (empty($favourites)): ?>
                <div class="card-body text-center">
                    <p class="fs-4">You haven't saved any favourite recipes yet.</p>
                    <a href="search.php" class="btn btn-primary">Browse Recipes</a>
                </div>
            <?php else: ?>
                <?php foreach ($favourites as $recipe): ?>
                    <?php
                        $recipeImgName = str_replace([' ', '/', ':', '?', ','], '_', $recipe['title']);
                        $recipeImgName = $recipe['recipe_id'] . '_' . $recipeImgName;
                        $recipeImage = "https://storage.googleapis.com/mealmate_recipe_images/{$recipeImgName}";
                    ?>
                    <div class="card m-3 p-3 d-flex flex-column flex-lg-row gap-4 align-items-center fav-card" data-title="<?= htmlspecialchars(strtolower($recipe['title'])) ?>">
                        <div class="images">
                            <img class="rounded" src="<?= htmlspecialchars($recipeImage) ?>" alt="">
                        </div>
                        <div class="information">    
                            <h2 class="mb-md-0 ms-md-1"><?= htmlspecialchars($recipe['title']) ?></h2>
                            <p class="mb-md-0 ms-md-1">By <?= htmlspecialchars($recipe['author']) ?></p>
                            <div class="nutrition mb-1 d-flex flex-column flex-sm-row gap-1 gap-md-3 ms-0">
                                <div>
                                    <img src="assets/images/icons/calories.svg" alt="calories"><span class="fs-small">Calories</span>
                                    <span><?= htmlspecialchars($recipe['calories']) ?> kcal</span>
                                </div>
                                <div>
                                    <img src="assets/images/icons/protein.svg" alt="protein"><span class="fs-small">Protein</span>
                                    <span><?= htmlspecialchars($recipe['protein_g']) ?> g</span>
                                </div>
                                <div>
                                    <img src="assets/images/icons/carbs.svg" alt="carbs"><span class="fs-small">Carbs</span>
                                    <span><?= htmlspecialchars($recipe['carbs_g']) ?> g</span>
                                </div>
                                <div>
                                    <img src="assets/images/icons/fat.svg" alt="fat"><span class="fs-small">Fats</span>
                                    <span><?= htmlspecialchars($recipe['fat_g']) ?> g</span>
                                </div>
                            </div>
                            <div class="nutrition mb-2 d-flex flex-column flex-sm-row gap-1 gap-md-3 ms-0">
                                <div>
                                    <img src="assets/images/icons/prep_time.svg" alt="prep_time"><span class="fs-small">Prep Time</span>
                                    <span><?= htmlspecialchars($recipe['prep_time_min']) ?> mins</span>
                                </div>
                                <div>
                                    <img src="assets/images/icons/cook_time.svg" alt="cook_time"><span class="fs-small">Cook Time</span>
                                    <span><?= htmlspecialchars($recipe['cook_time_min']) ?> mins</span>
                                </div>
                                <div>
                                    <img src="assets/images/icons/servings.svg" alt="servings"><span class="fs-small">Servings</span>
                                    <span><?= htmlspecialchars($recipe['servings']) ?></span>
                                </div>
                            </div>
                            <div class="description w-md-75 w-90 ms-1">
                                <p><?= htmlspecialchars($recipe['description']) ?></p>
                            </div>
                        </div>
                        <div class="align-self-start">
                            <button type="button" class="btn btn-danger remove-favourite-btn" data-recipe-id="<?= (int)$recipe['recipe_id'] ?>">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- <div class="card toolbar">
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
        </div> -->
    </div>
</body>
</html>