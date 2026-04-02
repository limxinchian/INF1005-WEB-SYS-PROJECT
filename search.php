<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        session_start();
        require_once 'config/db.php';

        $search_keywords = isset($_GET['keyword']) ? explode(' ', htmlspecialchars(trim($_GET['keyword']))) : [];
        $search_tag = isset($_GET['tag']) ? htmlspecialchars(trim($_GET['tag'])) : '';
        $result = null;

        if (empty($search_keywords)) {
            $stmt = "SELECT r.recipe_id, r.title, r.description,
                r.prep_time_min, r.cook_time_min, r.servings,
                r.calories, r.protein_g, r.carbs_g, r.fat_g,
                u.username AS author FROM recipes r
                JOIN users u ON r.submitted_by = u.user_id
                LIMIT 100";
            $result = $pdo->query($stmt)->fetchAll();
        } else {
            $stmt =  "SELECT r.recipe_id, r.title, r.description,
                r.prep_time_min, r.cook_time_min, r.servings,
                r.calories, r.protein_g, r.carbs_g, r.fat_g,
                u.username AS author FROM recipes r
                JOIN users u ON r.submitted_by = u.user_id
                WHERE ";
            $where_statement = implode(" OR ", array_map(function($kw) {
                return "(title LIKE ? OR description LIKE ?)";
            }, $search_keywords));

            $stmt .= $where_statement . " LIMIT 100";

            $prepared = $pdo->prepare($stmt);

            $params = [];
            foreach ($search_keywords as $kw) {
                $params[] = "%$kw%";
                $params[] = "%$kw%";
            }

            $prepared->execute($params);
            $result = $prepared->fetchAll();
        }

        $stmt = "SELECT recipe_id FROM favourite_recipes WHERE user_id = ?";
        if (isset($_SESSION['user_id'])) {
            $prepared = $pdo->prepare($stmt);
            $prepared->execute([$_SESSION['user_id']]);
            $favourite_recipes = $prepared->fetchAll();
        }

        $title = "MealMate - Search Results";
        require_once 'includes/header.php';
        require_once 'helper/get-image-link.php';
    ?>
    <script src="assets/js/search.js" defer></script>
    <link rel="stylesheet" href="assets/css/search.css">
</head>
<body>
    <?php include_once 'includes/nav.php'; ?>
    <div class="container">
        <h1 class="mb-0">Search Results For</h1>
        <p class="fs-4 mb-4"><?php echo implode(' ', $search_keywords); ?></p>
        <div class="row g-4">
            <?php if (!empty($result)): ?>
                <?php foreach ($result as $recipe): ?>
                    <?php
                        $photo = getImageLink($recipe['title'], $recipe['recipe_id']);
                        $title = $recipe['title'] ?? 'Untitled Recipe';
                        $description = $recipe['description'] ?? '';
                    ?>
                    <div class="col-12">
                    <div class="card p-3 d-flex flex-column flex-lg-row gap-4 align-items-center fav-card" data-title="<?= htmlspecialchars(strtolower($recipe['title'])) ?>">
                        <div class="images">
                            <img class="rounded" src="<?= htmlspecialchars(getImageLink($recipe['title'], $recipe['recipe_id'])) ?>" alt="">
                        </div>
                        <div class="information">    
                            <a href="recipe-detail.php?id=<?= htmlspecialchars($recipe['recipe_id']) ?>" class="text-decoration-none fs-2 mb-md-0 ms-md-1"><?= htmlspecialchars($recipe['title']) ?></a>
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
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="align-self-start">
                            <?php $is_fav = !empty($favourite_recipes) && in_array($recipe['recipe_id'], array_column($favourite_recipes, 'recipe_id')); ?>
                            <button type="button" class="btn btn-<?= $is_fav ? 'danger' : 'primary' ?> favourite-btn" data-recipe-id="<?= (int)$recipe['recipe_id'] ?>">
                                <?= $is_fav ? 'Remove from Favourites' : 'Add to Favourites' ?>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No recipes found matching your search criteria.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php include_once 'includes/footer.php'; ?>
</body>
</html>