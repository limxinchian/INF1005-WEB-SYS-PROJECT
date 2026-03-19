<?php
session_start();
require_once 'db.php'; // Must expose $pdo

$selected_tag = isset($_GET['tag']) ? htmlspecialchars(trim($_GET['tag'])) : '';
// Fallback to user 1 for testing if session isn't set
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

$recipes = [];
$error_msg = "";

$sql = "SELECT DISTINCT r.* FROM recipes r";
$where_clauses = ["r.status = 'approved'"];
$params = [];

// --- SMART ALLERGY DETECTION (Passive Profile Check) ---
// Fetch the logged-in user's dietary preferences including preference_type,
// which tells us whether to EXCLUDE (allergen) or INCLUDE (preference) a tag.
// PDO: use prepare() + execute(array) instead of bind_param()
$stmt_pref = $pdo->prepare("SELECT tag_id, preference_type FROM user_dietary_preference WHERE user_id = ?");
$stmt_pref->execute([$user_id]);

while ($pref_row = $stmt_pref->fetch(PDO::FETCH_ASSOC)) {
    $pref_tag_id = $pref_row['tag_id'];
    $pref_type   = $pref_row['preference_type'];

    if ($pref_type === 'exclude') {
        // Allergen: hide recipes that CONTAIN this tag (e.g. Nut-Free, Gluten-Free)
        $where_clauses[] = "r.recipe_id NOT IN (
            SELECT recipe_id FROM recipe_dietary_tags WHERE tag_id = ?
        )";
    } else {
        // Preference: only show recipes that HAVE this tag (e.g. Vegan, Halal)
        $where_clauses[] = "r.recipe_id IN (
            SELECT recipe_id FROM recipe_dietary_tags WHERE tag_id = ?
        )";
    }

    $params[] = $pref_tag_id;
}
$stmt_pref = null; // PDO: release statement instead of close()
// -------------------------------------------------------

// If they click a sidebar tag (like Vegan), apply that filter too
if (!empty($selected_tag)) {
    // We use an alias (rdt_filter) to not confuse SQL if they also have profile exclusions
    $sql .= " INNER JOIN recipe_dietary_tags rdt_filter ON r.recipe_id = rdt_filter.recipe_id
              INNER JOIN dietary_tags dt_filter ON rdt_filter.tag_id = dt_filter.tag_id";

    $where_clauses[] = "dt_filter.tag_name = ?";
    $params[] = $selected_tag;
}

$sql .= " WHERE " . implode(" AND ", $where_clauses);

// PDO: prepare once, then execute with the full $params array — no bind_param() or type string needed
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recipes[] = $row;
    }
    $stmt = null;
} catch (PDOException $e) {
    $error_msg = "Database error: Unable to load recipes.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Recipes - MealMate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Small custom style to highlight the active sidebar link */
        .list-group-item.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">MealMate</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="recipes.php">Browse All</a></li>
                <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                <li class="nav-item"><a class="nav-link" href="submit-recipe.php">Submit Recipe</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="row">
        
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Filter by Diet</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="recipes.php" class="list-group-item list-group-item-action <?php echo empty($selected_tag) ? 'active' : ''; ?>">
                        All Recipes
                    </a>
                    <a href="recipes.php?tag=Gluten-Free" class="list-group-item list-group-item-action <?php echo ($selected_tag == 'Gluten-Free') ? 'active' : ''; ?>">
                        Gluten-Free
                    </a>
                    <a href="recipes.php?tag=Vegan" class="list-group-item list-group-item-action <?php echo ($selected_tag == 'Vegan') ? 'active' : ''; ?>">
                        Vegan
                    </a>
                    <a href="recipes.php?tag=Vegetarian" class="list-group-item list-group-item-action <?php echo ($selected_tag == 'Vegetarian') ? 'active' : ''; ?>">
                        Vegetarian
                    </a>
                    <a href="recipes.php?tag=Nut-Free" class="list-group-item list-group-item-action <?php echo ($selected_tag == 'Nut-Free') ? 'active' : ''; ?>">
                        Nut-Free
                    </a>
                    <a href="recipes.php?tag=Halal" class="list-group-item list-group-item-action <?php echo ($selected_tag == 'Halal') ? 'active' : ''; ?>">
                        Halal
                    </a>
                    <a href="recipes.php?tag=Dairy-Free" class="list-group-item list-group-item-action <?php echo ($selected_tag == 'Dairy-Free') ? 'active' : ''; ?>">
                        Dairy-Free
                    </a>
                    <a href="recipes.php?tag=Keto" class="list-group-item list-group-item-action <?php echo ($selected_tag == 'Keto') ? 'active' : ''; ?>">
                        Keto
                    </a>
                    <a href="recipes.php?tag=Paleo" class="list-group-item list-group-item-action <?php echo ($selected_tag == 'Paleo') ? 'active' : ''; ?>">
                        Paleo
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <h2 class="mb-4">
                <?php 
                // Dynamic Page Title based on the selected filter
                if (!empty($selected_tag)) {
                    echo htmlspecialchars($selected_tag) . " Recipes";
                } else {
                    echo "All Approved Recipes";
                }
                ?>
            </h2>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="row">
                <?php if (empty($recipes) && empty($error_msg)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            No recipes found for this category. Be the first to <a href="submit-recipe.php" class="alert-link">submit one</a>!
                        </div>
                    </div>
                <?php else: ?>
                    
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="bg-secondary text-white text-center py-5 rounded-top">
                                    <span>Image Placeholder</span>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h5>
                                    <p class="card-text text-muted small mb-2">
                                        Prep Time: <?php echo htmlspecialchars($recipe['prep_time']); ?> mins
                                    </p>
                                    <p class="card-text flex-grow-1">
                                        <?php echo htmlspecialchars(substr($recipe['instructions'], 0, 75)) . '...'; ?>
                                    </p>
                                    <a href="recipe-detail.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-outline-primary mt-auto">View Full Recipe</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>