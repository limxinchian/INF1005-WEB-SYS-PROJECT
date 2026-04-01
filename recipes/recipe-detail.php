<?php
session_start();
require_once '../config/db.php';

// 1. VALIDATE & SANITIZE THE INPUT
$recipe_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$recipe_id) {
    die("Invalid Recipe ID.");
}

// 2. FETCH MAIN RECIPE DATA
// V2 update: Added deleted_at IS NULL for soft deletes
$stmt_recipe = $pdo->prepare("SELECT * FROM recipes WHERE recipe_id = ? AND status = 'approved' AND deleted_at IS NULL");
$stmt_recipe->execute([$recipe_id]);
$recipe = $stmt_recipe->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    die("Recipe not found or not yet approved.");
}

// 3. FETCH DIETARY TAGS
$tags = [];
// V2 update: Fixed column names to tag_name and tag_id
$stmt_tags = $pdo->prepare("
    SELECT dt.tag_name 
    FROM recipe_dietary_tags rdt
    INNER JOIN dietary_tags dt ON rdt.tag_id = dt.tag_id
    WHERE rdt.recipe_id = ?
");
$stmt_tags->execute([$recipe_id]);
while ($row = $stmt_tags->fetch(PDO::FETCH_ASSOC)) {
    $tags[] = $row['tag_name'];
}

// 4. FETCH INGREDIENTS & ALLERGENS
$ingredients = [];
// V2 update: Upgraded to use your V2 schema's advanced Allergen warning query!
$stmt_ing = $pdo->prepare("
    SELECT i.ingredient_name, ri.quantity, ri.notes,
           GROUP_CONCAT(a.allergen_name SEPARATOR ', ') AS allergens
    FROM recipe_ingredients ri
    JOIN ingredients i ON i.ingredient_id = ri.ingredient_id
    LEFT JOIN ingredient_allergens ia ON ia.ingredient_id = i.ingredient_id
    LEFT JOIN allergens a ON a.allergen_id = ia.allergen_id
    WHERE ri.recipe_id = ?
    GROUP BY i.ingredient_id, ri.quantity, ri.notes
");
$stmt_ing->execute([$recipe_id]);
while ($row = $stmt_ing->fetch(PDO::FETCH_ASSOC)) {
    $ingredients[] = $row;
}

// 5. FETCH STEPS 
$steps = [];
$stmt_steps = $pdo->prepare("SELECT step_order, instruction FROM recipe_steps WHERE recipe_id = ? ORDER BY step_order ASC");
$stmt_steps->execute([$recipe_id]);
while ($row = $stmt_steps->fetch(PDO::FETCH_ASSOC)) {
    $steps[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - MealMate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">MealMate</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="recipes.php">Browse All</a></li>
                <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                <li class="nav-item"><a class="nav-link" href="submit-recipe.php">Submit Recipe</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($recipe['title']); ?></h1>
            <p class="text-muted fs-5">Preparation Time: <?php echo htmlspecialchars($recipe['prep_time_min']); ?> minutes</p>
            
            <div class="mb-3">
                <?php foreach ($tags as $tag): ?>
                    <span class="badge bg-success fs-6 me-2"><?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body">
                    <h3 class="card-title h4 mb-3 border-bottom pb-2">Ingredients</h3>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($ingredients)): ?>
                            <li class="list-group-item text-muted">Ingredients list not available.</li>
                        <?php else: ?>
                            <?php foreach ($ingredients as $ing): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <?php echo htmlspecialchars($ing['ingredient_name']); ?>
                                        
                                        <?php if (!empty($ing['allergens'])): ?>
                                            <div class="mt-1">
                                                <span class="badge bg-danger bg-opacity-75 text-white" style="font-size: 0.7em;">
                                                    ⚠️ Contains: <?php echo htmlspecialchars($ing['allergens']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <span class="badge bg-secondary rounded-pill">
                                        <?php 
                                        $qty_str = $ing['quantity'];
                                        if (!empty($ing['notes'])) {
                                            $qty_str .= ' ' . $ing['notes'];
                                        }
                                        echo htmlspecialchars(trim($qty_str)); 
                                        ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body">
                    <h3 class="card-title h4 mb-4 border-bottom pb-2">Description / Instructions</h3>
                    
                    <?php if (empty($steps)): ?>
                        <p style="white-space: pre-line;"><?php echo htmlspecialchars($recipe['description']); ?></p>
                    <?php else: ?>
                        <div class="list-group list-group-flush list-group-numbered">
                            <?php foreach ($steps as $step): ?>
                                <li class="list-group-item border-0 mb-2 px-0 d-flex align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold text-primary mb-1">Step <?php echo htmlspecialchars($step['step_order']); ?></div>
                                        <?php echo htmlspecialchars($step['instruction']); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>