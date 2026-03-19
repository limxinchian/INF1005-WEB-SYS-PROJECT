<?php
// Start session to access the logged-in user's ID
session_start();
require_once 'db.php';

// 1. AUTHENTICATION CHECK 
// For testing purposes, fallback to user_id = 1 if the session isn't built yet.
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

$success_msg = "";
$error_msg = "";

// 2. HANDLE DELETE REQUEST 
// check if a POST request was made specifically for deleting a recipe.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_recipe_id'])) {
    
    // Validate that the ID is an integer
    $delete_id = filter_input(INPUT_POST, 'delete_recipe_id', FILTER_VALIDATE_INT);
    
    if ($delete_id) {
       
        $stmt_delete = $conn->prepare("DELETE FROM recipes WHERE recipe_id = ? AND user_id = ?");
        
        if ($stmt_delete) {
            $stmt_delete->bind_param("ii", $delete_id, $user_id);
            if ($stmt_delete->execute()) {
                $success_msg = "Recipe deleted successfully.";
            } else {
                $error_msg = "Failed to delete recipe. Please try again.";
            }
            $stmt_delete->close();
        } else {
            $error_msg = "Database error during deletion.";
        }
    } else {
        $error_msg = "Invalid Recipe ID.";
    }
}

// 3. FETCH THE USER'S RECIPES 
$my_recipes = [];
// Select all recipes belonging to this specific user, ordered by newest first
$stmt_fetch = $conn->prepare("SELECT * FROM recipes WHERE user_id = ? ORDER BY recipe_id DESC");

if ($stmt_fetch) {
    $stmt_fetch->bind_param("i", $user_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $my_recipes[] = $row;
    }
    $stmt_fetch->close();
} else {
    $error_msg = "Database error: Unable to load your recipes.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Recipes - MealMate</title>
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
                <li class="nav-item"><a class="nav-link active" href="my-recipes.php">My Profile</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>My Recipe Dashboard</h2>
            <a href="submit-recipe.php" class="btn btn-primary">+ Add New Recipe</a>
        </div>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($my_recipes)): ?>
                <div class="p-5 text-center">
                    <h5 class="text-muted">You haven't submitted any recipes yet!</h5>
                    <p>Share your favorite meals with the MealMate community.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Recipe Title</th>
                                <th>Prep Time</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_recipes as $recipe): ?>
                                <tr>
                                    <td class="ps-4 fw-medium">
                                        <?php echo htmlspecialchars($recipe['title']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($recipe['prep_time']); ?> mins
                                    </td>
                                    <td>
                                        <?php if ($recipe['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($recipe['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($recipe['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        
                                        <a href="recipe-detail.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-sm btn-outline-primary me-2">View</a>
                                        
                                        <form action="my-recipes.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to completely delete this recipe? This cannot be undone.');">
                                            <input type="hidden" name="delete_recipe_id" value="<?php echo $recipe['recipe_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>