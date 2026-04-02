<?php
    $base = '/MealMate/INF1005-WEB-SYS-PROJECT';
    $adminBase = $base . '/admin';
?>
<nav class="navbar navbar-expand-lg navbar-light border-bottom border-primary border-5">
    <div class="container-fluid d-flex align-items-center">
        <div>
            <a href="<?= $adminBase ?>/dashboard.php" class="navbar-brand ps-3 fs-4 fw-bold fs-2 text-decoration-none">Mealmate Admin</a>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavLinks" aria-controls="adminNavLinks" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavLinks">
            <ul class="navbar-nav ms-lg-auto d-flex gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $adminBase ?>/dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Recipes</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= $adminBase ?>/recipes/pending.php">Pending Recipes</a></li>
                        <li><a class="dropdown-item" href="<?= $adminBase ?>/recipes/recipes.php">All Recipes</a></li>
                        <li><a class="dropdown-item" href="<?= $adminBase ?>/recipes/recipes-trash.php">Deleted Recipes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $adminBase ?>/ingredients/ingredients.php">Ingredients</a></li>
                        <li><a class="dropdown-item" href="<?= $adminBase ?>/allergens/allergens.php">Allergens</a></li>
                        <li><a class="dropdown-item" href="<?= $adminBase ?>/tags/tags.php">Tags</a></li>
                        
                    
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Users</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= $adminBase ?>/users/users.php">All Users</a></li>
                        <li><a class="dropdown-item" href="<?= $adminBase ?>/users/users-trash.php">Deleted Users</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>