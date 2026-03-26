<?php
session_start();
require_once 'db.php'; // Must expose $pdo

$search_keyword = isset($_GET['keyword']) ? htmlspecialchars(trim($_GET['keyword'])) : '';
$search_tag = isset($_GET['tag']) ? htmlspecialchars(trim($_GET['tag'])) : '';

$recipes = [];
$error_msg = "";

$sql = "SELECT DISTINCT r.* FROM recipes r";
$where_clauses = ["r.status = 'approved'"];
$params = [];

// --- SMART ALLERGY DETECTION (Active Search — Ingredient-Based) ---
// Instead of checking dietary tag names, we query the ingredients table directly
// using boolean flags (is_nut, is_gluten). This is more precise because a recipe
// could contain nuts without being explicitly tagged "Nut-Free".
if (!empty($search_tag)) {
    if ($search_tag === 'Nut-Free') {
        $where_clauses[] = "r.recipe_id NOT IN (
            SELECT ri.recipe_id
            FROM recipe_ingredients ri
            INNER JOIN ingredients i ON i.ingredient_id = ri.ingredient_id
            WHERE i.is_nut = 1
        )";
    } elseif ($search_tag === 'Gluten-Free') {
        $where_clauses[] = "r.recipe_id NOT IN (
            SELECT ri.recipe_id
            FROM recipe_ingredients ri
            INNER JOIN ingredients i ON i.ingredient_id = ri.ingredient_id
            WHERE i.is_gluten = 1
        )";
    } elseif ($search_tag === 'Dairy-Free') {
        $where_clauses[] = "r.recipe_id NOT IN (
            SELECT ri.recipe_id
            FROM recipe_ingredients ri
            INNER JOIN ingredients i ON i.ingredient_id = ri.ingredient_id
            WHERE i.is_dairy = 1
        )";
    } elseif ($search_tag === 'Paleo') {
        // EXCLUSION: Hide any recipe containing an ingredient flagged as non-paleo (is_paleo = 1)
        $where_clauses[] = "r.recipe_id NOT IN (
            SELECT ri.recipe_id
            FROM recipe_ingredients ri
            INNER JOIN ingredients i ON i.ingredient_id = ri.ingredient_id
            WHERE i.is_paleo = 1
        )";
    } else {
        // INCLUSION: For Vegan, Vegetarian, Halal, Keto
        $sql .= " INNER JOIN recipe_dietary_tags rdt ON r.recipe_id = rdt.recipe_id
                  INNER JOIN dietary_tags dt ON rdt.tag_id = dt.tag_id";
        $where_clauses[] = "dt.tag_name = ?";
        $params[] = $search_tag;
    }
}
// -------------------------------------------------------------------

if (!empty($search_keyword)) {
    $where_clauses[] = "(r.title LIKE ? OR r.instructions LIKE ?)";
    $like_keyword = "%" . $search_keyword . "%";
    $params[] = $like_keyword;
    $params[] = $like_keyword;
}

$sql .= " WHERE " . implode(" AND ", $where_clauses);

// PDO: prepare + execute with array — no bind_param() or type string needed
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recipes[] = $row;
    }
    $stmt = null;
} catch (PDOException $e) {
    $error_msg = "Database error: Unable to prepare search query.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Recipes - MealMate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">MealMate</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="recipes.php">Browse All</a></li>
                <li class="nav-item"><a class="nav-link active" href="search.php">Search</a></li>
                <li class="nav-item"><a class="nav-link" href="submit-recipe.php">Submit Recipe</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">Find Your Perfect Meal</h2>
            
            <form action="search.php" method="GET" class="card p-4 shadow-sm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="keyword" class="form-label">Search Keyword</label>
                        <input type="text" class="form-control" id="keyword" name="keyword" 
                               placeholder="e.g., Chicken, Pasta, Spicy..." 
                               value="<?php echo $search_keyword; ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="tag" class="form-label">Dietary Restriction</label>
                        <select class="form-select" id="tag" name="tag">
                            <option value="">Any</option>
                            <option value="Gluten-Free"  <?php if($search_tag == 'Gluten-Free')  echo 'selected'; ?>>Gluten-Free</option>
                            <option value="Vegan"        <?php if($search_tag == 'Vegan')        echo 'selected'; ?>>Vegan</option>
                            <option value="Vegetarian"   <?php if($search_tag == 'Vegetarian')   echo 'selected'; ?>>Vegetarian</option>
                            <option value="Nut-Free"     <?php if($search_tag == 'Nut-Free')     echo 'selected'; ?>>Nut-Free</option>
                            <option value="Halal"        <?php if($search_tag == 'Halal')        echo 'selected'; ?>>Halal</option>
                            <option value="Dairy-Free"   <?php if($search_tag == 'Dairy-Free')   echo 'selected'; ?>>Dairy-Free</option>
                            <option value="Keto"         <?php if($search_tag == 'Keto')         echo 'selected'; ?>>Keto</option>
                            <option value="Paleo"        <?php if($search_tag == 'Paleo')        echo 'selected'; ?>>Paleo</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($recipes)): ?>
            <div class="col-12 text-center mt-5">
                <h4 class="text-muted">No recipes found matching your criteria.</h4>
                <p>Try adjusting your search terms or filters!</p>
            </div>
        <?php else: ?>
            <?php foreach ($recipes as $recipe): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h5>
                            <p class="card-text text-muted small">
                                Prep time: <?php echo htmlspecialchars($recipe['prep_time']); ?> mins
                            </p>
                            <p class="card-text">
                                <?php echo htmlspecialchars(substr($recipe['instructions'], 0, 80)) . '...'; ?>
                            </p>
                            <a href="recipe-detail.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-outline-primary btn-sm">View Full Recipe</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>