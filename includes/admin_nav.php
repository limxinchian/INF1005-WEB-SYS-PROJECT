<nav class="navbar navbar-expand-lg navbar-light border-bottom border-primary border-5">
    <div class="container-fluid d-flex align-items-center">
        <div>
            <a href="dashboard.php" class="navbar-brand ps-3 fs-4 fw-bold fs-2 text-decoration-none">Admin Dashboard</a>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavLinks" aria-controls="adminNavLinks" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavLinks">
            <div class="navbar-nav ms-lg-auto d-flex gap-2">
                <a class="nav-link" href="users/users.php">All Users</a>
                <a class="nav-link" href="ingredients/ingredients.php">Ingredients</a>
                <a class="nav-link" href="allergens/allergens.php">Allergens</a>
                <a class="nav-link" href="tags/tags.php">Tags</a>
                <a class="nav-link" href="recipes/recipes-trash.php">View Deleted Recipes</a>
                <a class="nav-link" href="users/users-trash.php">View Deleted Users</a>
            </div>
        </div>
    </div>
</nav>