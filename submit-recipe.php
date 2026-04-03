<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        session_start();
        require_once 'config/db.php'; 

        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        // Fetch all available ingredients for the dropdown
        $stmt = $pdo->query("SELECT ingredient_id, ingredient_name FROM ingredients ORDER BY ingredient_name ASC");
        $all_ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $success_msg = "";
        $error_msg   = "";
        $old         = []; 

        if (isset($_SESSION['flash_success'])) {
            $success_msg = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']); 
        }
        if (isset($_SESSION['flash_error'])) {
            $error_msg = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }
        if (isset($_SESSION['old_input'])) {
            $old = $_SESSION['old_input'];
            unset($_SESSION['old_input']);
        }
        $title = "MealMate - Submit Recipe";
        require_once 'includes/header.php';
    ?>
    <script>
        const allIngredients = <?= json_encode(array_map(fn($i) => [
            'id' => (int)$i['ingredient_id'],
            'name' => $i['ingredient_name']
        ], $all_ingredients), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="assets/js/submit-recipe.js" defer></script>
</head>
<body>
    <?php include_once 'includes/nav.php'; ?>
    <div class="container my-4">
        <h1>Submit a Recipe</h1>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="actions/recipe-submit.php" method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label for="title" class="form-label">Recipe Title</label>
                            <input type="text" class="form-control" id="title" name="title"
                                   required maxlength="100" placeholder="e.g., Spicy Vegan Tacos"
                                   value="<?php echo htmlspecialchars($old['title'] ?? ''); ?>">
                        </div>

                        <div class="d-flex flex-row gap-4 mb-3 align-items-center">
                            <div class="flex-grow-1">
                                <label for="prep_time" class="form-label">Prep Time (Minutes)</label>
                                <input type="number" class="form-control" id="prep_time" name="prep_time_min"
                                    required min="1" placeholder="e.g., 30"
                                    value="<?php echo htmlspecialchars($old['prep_time_min'] ?? ''); ?>">
                            </div>
                            <div class="flex-grow-1">
                                <label for="cook_time" class="form-label">Cook Time (Minutes)</label>
                                <input type="number" class="form-control" id="cook_time" name="cook_time_min"
                                    required min="1" placeholder="e.g., 30"
                                    value="<?php echo htmlspecialchars($old['cook_time_min'] ?? ''); ?>">
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Ingredients</label>
                            <div id="ingredients-container">
                                <div class="mb-3 d-flex flex-row justify-content-between align-items-center gap-2">
                                    
                                    <div class="ingredient-picker position-relative flex-grow-1">
                                        <input type="hidden" name="ingredient_ids[]" class="ingredient-id-input" required>
                                        <input type="text" class="form-control ingredient-search" placeholder="Type to search ingredient..." autocomplete="off">
                                        <div class="ingredient-dropdown list-group position-absolute w-100 shadow" style="z-index:1050; max-height:200px; overflow-y:auto; display:none;"></div>
                                    </div>

                                    <input type="number" step="0.1" min="0" class="form-control" name="ingredient_amounts[]" placeholder="Amount (e.g. 100)" required style="width: 130px;">
                                    
                                    <select class="form-select" name="ingredient_units[]" required style="width: 100px;">
                                        <option value="">Unit</option>
                                        <option value="g">g</option>
                                        <option value="kg">kg</option>
                                        <option value="ml">ml</option>
                                        <option value="L">L</option>
                                        <option value="tbsp">tbsp</option>
                                        <option value="tsp">tsp</option>
                                        <option value="cup">cup</option>
                                        <option value="pcs">pcs</option>
                                        <option value="whole">whole</option>
                                        <option value="pinch">pinch</option>
                                    </select>

                                    <button type="button" class="btn btn-danger remove-btn" disabled>X</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-ingredient-btn">+ Add Ingredient</button>
                            
                            <hr>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Instructions / Steps</label>
                                <div id="steps-container">
                                    <div class="mb-3 d-flex flex-row justify-content-between align-items-top gap-2 dynamic-row step-row">
                                        <div class="step-number-badge step-counter">1</div>
                                        <textarea class="form-control" name="steps[]" rows="1" placeholder="Describe this step..." required></textarea>
                                        <button type="button" class="btn btn-danger remove-btn" disabled>X</button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-step-btn">+ Add Step</button>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <label class="form-label fw-bold d-block">Dietary Tags</label>
                                <?php
                                $old_tags    = $old['tags'] ?? [];
                                $tag_options = ['Vegan', 'Vegetarian', 'Halal', 'Nut-Free', 'Gluten-Free', 'Dairy-Free', 'Keto', 'Paleo'];
                                
                                foreach ($tag_options as $tag):
                                    $safe_id = 'tag_' . strtolower(str_replace([' ', '-'], ['', ''], $tag));
                                    $checked = in_array($tag, $old_tags) ? 'checked' : '';
                                ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox"
                                                id="<?php echo htmlspecialchars($safe_id); ?>"
                                                name="tags[]"
                                                value="<?php echo htmlspecialchars($tag); ?>"
                                                <?php echo $checked; ?>>
                                        <label class="form-check-label" for="<?php echo htmlspecialchars($safe_id); ?>">
                                            <?php echo htmlspecialchars($tag); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex flex-row gap-3 mb-3">
                                <div class="flex-grow-1">
                                    <label for="calories" class="form-label">Calories</label>
                                    <input type="number" class="form-control" id="calories" name="calories"
                                        required min="1" placeholder="e.g., 200"
                                        value="<?php echo htmlspecialchars($old['calories'] ?? ''); ?>">
                                </div>
                                <div class="flex-grow-1">
                                    <label for="proteins_g" class="form-label">Proteins (g)</label>
                                    <input type="number" class="form-control" id="proteins_g" name="proteins_g"
                                        required min="0" placeholder="e.g., 10"
                                        value="<?php echo htmlspecialchars($old['proteins_g'] ?? ''); ?>">
                                </div>
                                <div class="flex-grow-1">
                                    <label for="carbs_g" class="form-label">Carbohydrates (g)</label>
                                    <input type="number" class="form-control" id="carbs_g" name="carbs_g"
                                        required min="0" placeholder="e.g., 10"
                                        value="<?php echo htmlspecialchars($old['carbs_g'] ?? ''); ?>">
                                </div>
                                <div class="flex-grow-1">
                                    <label for="fat_g" class="form-label">Fat (g)</label>
                                    <input type="number" class="form-control" id="fat_g" name="fat_g"
                                        required min="0" placeholder="e.g., 10"
                                        value="<?php echo htmlspecialchars($old['fat_g'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="recipe_image" class="form-label fw-bold">Recipe Image</label>
                                <input type="file" class="form-control" id="recipe_image" name="recipe_image" accept=".png,.jpg,.jpeg" required>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Submit Recipe</button>
                            </div>

        </form>
    </div>
    <?php include_once 'includes/footer.php'; ?>
</body>
</html>