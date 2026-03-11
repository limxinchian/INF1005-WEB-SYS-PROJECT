<?php
    require_once __DIR__ . '/../config/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealMate</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/mealmate/assets/css/styles.css">
    <script defer src="/mealmate/assets/js/main.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a href="/index.php" class="navbar-brand fw-bold">MealMate</a>
            <div class="navbar-nav ms-auto">
                <?php
                    if(isLoggedIn()){
                ?>
                    <a href="/dashboard.php" class="nav-link">Dashboard</a>
                    <a href="/logout.php" class="nav-link">Logout</a>
                <?php
                    }else{
                ?>
                    <a href="/login.php" class="nav-link">Login</a>
                    <a href="/register.php" class="nav-link">Register</a>
                <?php
                    }
                ?>
            </div>
        </div>
    </nav>
</body>
</html>