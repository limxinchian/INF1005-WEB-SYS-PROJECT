<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/config/session.php';
        require_once 'config/db.php';

        if (isLoggedIn()) {
            redirect('dashboard.php');
        }
        
        $stmt = $pdo->prepare("SELECT recipe_id, title, description
            FROM recipes
            WHERE status = 'approved'
            LIMIT 3");

        $stmt->execute();
        $results = $stmt->fetchAll();

        $title = "MealMate - Home";
        include_once 'includes/header.php';
        require_once 'helper/get-image-link.php';
    ?>    
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body class="bg-light">
    <?php include 'includes/nav.php'; ?>
    <div id="hero" class="container mt-3 p-5 py-10 d-flex flex-column align-items-center justify-content-center text-center gap-3">
        <h1 class="display-4 fw-bold text-white">2 Million Recipes for You to Choose</h1>
        <p class="lead text-white">2 Million Recipes, by professional chefs and the community around you. Join now to experience freedom of choice when it comes to cooking.</p>
        <a href="login.php" class="btn btn-primary btn-lg"><span class="overpass-mono-light text-white fw-bold">Get Started</span></a>
    </div>

    <section class="our_new_recipes home_info">
        <h2 class="text-center mt-4">Our New Recipes</h2>
        <div class="container my-3 d-none d-md-block">
            <div class="row g-4">
                <?php foreach($results as $recipe):
                    $id = $recipe['recipe_id'];
                    $title = $recipe['title'];
                    $description = $recipe['description'];
                    $photo = getImageLink($recipe['title'], $recipe['recipe_id']);
                    include 'includes/home_recipe.php';
                endforeach; ?>
            </div>
        </div>

        <div class="container my-5 d-md-none">
            <div id="recipesCarousel" class="carousel slide" data-bs-ride="true">
                <div class="carousel-inner">
                <?php foreach($results as $index => $recipe): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <?php
                            $id = $recipe['recipe_id'];
                            $title = $recipe['title'];
                            $description = $recipe['description'];
                            $photo = getImageLink($recipe['title'], $recipe['recipe_id']);
                            include 'includes/home_recipe.php';
                        ?>
                    </div>
                <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#recipesCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#recipesCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </section>    

    <section class="member_reviews home_info">
        <h2 class="text-center mt-4">What our Members Say</h2>
        <div class="container my-3 d-none d-md-block">
            <div class="row g-4">
            <?php
                $name = "Ryan Mitchell";
                $review = "MealMate completely changed my weekly routine. I no longer stress about what to cook after a long day at work.";
                $photo = "assets/images/customer_1.jpg";
                include 'includes/home_user_recommendation.php';
            ?>
            <?php
                $name = "James Oliver";
                $review = "The recipe variety is incredible and easy to follow. My family has never eaten this well before!";
                $photo = "assets/images/customer_2.jpg";
                include 'includes/home_user_recommendation.php';
            ?>
            <?php
                $name = "Kevin Chen";
                $review = "I love how I can plan meals around what's already in my fridge. It's saved me so much money on groceries.";
                $photo = "assets/images/customer_3.jpg";
                include 'includes/home_user_recommendation.php';
            ?>
            <?php
                $name = "Daniel Reyes";
                $review = "As someone with food allergies, MealMate makes it so easy to find safe recipes. It's been a lifesaver for me.";
                $photo = "assets/images/customer_4.jpg";
                include 'includes/home_user_recommendation.php';
            ?>
            <?php
                $name = "Priya Sharma";
                $review = "I've discovered so many new cuisines through this platform. Cooking has become my favourite hobby thanks to MealMate.";
                $photo = "assets/images/customer_5.jpg";
                include 'includes/home_user_recommendation.php';
            ?>
            <?php
                $name = "Marcus Johnson";
                $review = "The meal planner keeps me on track with my fitness goals. I've never felt more organised in the kitchen.";
                $photo = "assets/images/customer_6.jpg";
                include 'includes/home_user_recommendation.php';
            ?>
            </div>
        </div>

        <div class="container my-5 d-md-none">
            <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <?php
                            $name = "Ryan Mitchell";
                            $review = "MealMate completely changed my weekly routine. I no longer stress about what to cook after a long day at work.";
                            $photo = "assets/images/customer_1.jpg";
                            include 'includes/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "James Oliver";
                            $review = "The recipe variety is incredible and easy to follow. My family has never eaten this well before!";
                            $photo = "assets/images/customer_2.jpg";
                            include 'includes/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "Kevin Chen";
                            $review = "I love how I can plan meals around what's already in my fridge. It's saved me so much money on groceries.";
                            $photo = "assets/images/customer_3.jpg";
                            include 'includes/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "Daniel Reyes";
                            $review = "As someone with food allergies, MealMate makes it so easy to find safe recipes. It's been a lifesaver for me.";
                            $photo = "assets/images/customer_4.jpg";
                            include 'includes/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "Priya Sharma";
                            $review = "I've discovered so many new cuisines through this platform. Cooking has become my favourite hobby thanks to MealMate.";
                            $photo = "assets/images/customer_5.jpg";
                            include 'includes/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "Marcus Johnson";
                            $review = "The meal planner keeps me on track with my fitness goals. I've never felt more organised in the kitchen.";
                            $photo = "assets/images/customer_6.jpg";
                            include 'includes/home_user_recommendation.php';
                        ?>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </section>

    <section class="about_us home_info d-flex flex-column align-items-center justify-content-center text-center gap-3">
        <h2 class="text-center mt-4">About Us</h2>
        <p class="about_us text-center">MealMate was born from a simple idea: everyone deserves to enjoy home-cooked meals without the stress of planning. We bring together a passionate community of home cooks and professional chefs who share their favourite recipes from around the world. Our platform helps you discover new dishes, plan your weekly meals, and make the most of the ingredients already in your kitchen. Whether you're a beginner learning the basics or a seasoned cook exploring new cuisines, MealMate is designed to make your time in the kitchen easier and more enjoyable.</p>
        <a class="btn btn-secondary" href="about_us.php"><span class="text-white text-decoration-none overpass-mono-normal">About Us</span></a> 
    </section>

    <?php include 'includes/footer.php' ?>
</body>
</html>