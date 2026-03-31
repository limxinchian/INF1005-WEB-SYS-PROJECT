<?php
require_once __DIR__ . '/../config/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealMate</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >
    <script
        defer
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="/INF1005-WEB-SYS-PROJECT/assets/css/styles.css">
    <script defer src="/INF1005-WEB-SYS-PROJECT/assets/js/main.js"></script>
</head>
<body>

    <!-- ── Navbar ──────────────────────────────────────── -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">

            <!-- Brand -->
            <a href="/INF1005-WEB-SYS-PROJECT/index.php"
               class="navbar-brand fw-bold">
                &#127859; MealMate
            </a>

            <!-- Hamburger toggle for mobile -->
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <!-- Always visible -->
                    <li class="nav-item">
                        <a class="nav-link"
                           href="/INF1005-WEB-SYS-PROJECT/recipes.php">
                            Recipes
                        </a>
                    </li>

                    <?php if (isLoggedIn()): ?>

                        <!-- Logged in links -->
                        <li class="nav-item">
                            <a class="nav-link"
                               href="/INF1005-WEB-SYS-PROJECT/dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="/INF1005-WEB-SYS-PROJECT/favourites.php">
                                Favourites
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="/INF1005-WEB-SYS-PROJECT/fridge.php">
                                My Fridge
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="/INF1005-WEB-SYS-PROJECT/meal-planner.php">
                                Meal Planner
                            </a>
                        </li>

                        <?php if (isAdmin()): ?>
                        <!-- Admin only -->
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold"
                               href="/INF1005-WEB-SYS-PROJECT/admin/dashboard.php">
                                &#9881; Admin
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- User dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle"
                               href="#"
                               id="userDropdown"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                &#128100;
                                <?= htmlspecialchars(currentUsername()) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end"
                                aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item"
                                       href="/INF1005-WEB-SYS-PROJECT/profile.php">
                                        &#128100; My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="/INF1005-WEB-SYS-PROJECT/my-recipes.php">
                                        &#127859; My Recipes
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger"
                                       href="/INF1005-WEB-SYS-PROJECT/auth/logout.php"
                                       onclick="return confirm('Are you sure you want to logout?')">
                                        &#128274; Logout
                                    </a>
                                </li>
                            </ul>
                        </li>

                    <?php else: ?>

                        <!-- Visitor links -->
                        <li class="nav-item">
                            <a class="nav-link"
                               href="/INF1005-WEB-SYS-PROJECT/login.php">
                                Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm px-3 ms-2"
                               href="/INF1005-WEB-SYS-PROJECT/register.php">
                                Register
                            </a>
                        </li>

                    <?php endif; ?>

                </ul>
            </div>

        </div>
    </nav>
    <!-- ── End Navbar ──────────────────────────────────── -->

    <!-- ── Main Content ────────────────────────────────── -->
    <main class="container my-4">

        <?php showFlash(); ?>