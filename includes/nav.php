<?php
    require_once __DIR__ . '/../config/session.php';
?>
<nav class="navbar navbar-expand-lg navbar-light border-bottom border-primary border-5">
    <div class="container-fluid d-flex align-items-center gap-3">
        <div>
            <a href="index.php" class="navbar-brand ps-3">
                <img src="assets/images/logo.png" alt="Logo" width="65">
            </a>
        </div>

        <div class="flex-grow-1 d-none d-lg-block">
            <form action="search.php" method="get">
                <input type="text" name="keyword" placeholder="Butter Chicken Curry" class="form-control bg-white">
            </form>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="d-lg-none my-3">
                <form action="search.php" method="get">
                    <input type="text" name="keyword" placeholder="Butter Chicken Curry" class="form-control bg-white">
                </form>
            </div>
            <?php if (!isLoggedIn()){ ?>
                <div class="d-flex gap-3 ms-lg-auto flex-row flex-lg-row justify-content-end">
                    <a href="login.php" class="btn btn-secondary w-nav_button"><span class="text-white overpass-mono-normal">Login</span></a>
                    <a href="register.php" class="btn btn-secondary w-nav_button"><span class="text-white overpass-mono-normal">Register</span></a>
                </div>
            <?php }else{?>
            <div class="d-flex gap-3 ms-lg-auto me-lg-3 flex-row flex-lg-row justify-content-end align-items-center">
                <div class="dropdown">
                    <a class="btn btn-primary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Welcome, <?php echo htmlspecialchars(currentUsername()); ?>!
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="text-end dropdown-item" href="dashboard.php">View Dashboard</a></li>
                        <li><a class="text-end dropdown-item" href="meal-planner.php">Meal Planner</a></li>
                        <li><a class="text-end dropdown-item" href="fridge.php">Fridge</a></li>
                        <li><a class="text-end dropdown-item" href="favourites.php">Favourites</a></li>
                        <li><a class="text-end dropdown-item" href="recipes.php">My Recipes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="text-end dropdown-item" href="profile.php">View Profile</a></li>
                        <li><a class="text-end dropdown-item" href="edit_profile.php">Edit Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="text-end dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</nav>

<?php showFlash(); ?>