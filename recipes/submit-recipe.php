<?php
// Start the session to access logged-in user data AND read flash messages
session_start();

// =========================================================
// READ & CLEAR SESSION FLASH MESSAGES
// Flash messages are set by actions/recipe-submit.php before
// it redirects here. We read them once, then immediately 
// destroy them so they don't reappear on page refresh.
// =========================================================
$success_msg = "";
$error_msg   = "";
$old         = []; // Holds previously submitted form values (sticky form)

if (isset($_SESSION['flash_success'])) {
    $success_msg = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']); // Clear after reading — "flash" behaviour
}

if (isset($_SESSION['flash_error'])) {
    $error_msg = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// Restore old form input so the user doesn't lose their typed data on error
if (isset($_SESSION['old_input'])) {
    $old = $_SESSION['old_input'];
    unset($_SESSION['old_input']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Recipe - MealMate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">MealMate</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="recipes.php">Browse</a></li>
                <li class="nav-item"><a class="nav-link active" href="submit-recipe.php">Submit Recipe</a></li>
                <li class="nav-item"><a class="nav-link" href="my-recipes.php">My Profile</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Share Your Recipe</h4>
                </div>
                <div class="card-body">

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

                    <form action="../actions/recipe-submit.php" method="POST">

                        <div class="mb-3">
                            <label for="title" class="form-label">Recipe Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title"
                                   required maxlength="100" placeholder="e.g., Spicy Vegan Tacos"
                                   value="<?php echo htmlspecialchars($old['title'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="prep_time" class="form-label">Preparation Time (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="prep_time" name="prep_time_min"
                                   required min="1" placeholder="e.g., 30"
                                   value="<?php echo htmlspecialchars($old['prep_time_min'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description"
                                      rows="6" required
                                      placeholder="1. Chop the vegetables..."><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block">Dietary Tags</label>
                            <?php
                            $old_tags    = $old['tags'] ?? [];
                            $tag_options = ['Vegan', 'Vegetarian', 'Halal', 'Nut-Free', 'Gluten-Free', 'Dairy-Free', 'Keto', 'Paleo'];
                            
                            foreach ($tag_options as $tag):
                                // Automatically generate a safe ID like "tag_glutenfree"
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

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Submit Recipe</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>