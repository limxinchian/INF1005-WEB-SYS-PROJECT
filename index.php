<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealMate</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/index.css">
    
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
    <?php include 'reusables/nav.php'; ?>
    <div id="hero" class="container mt-3 p-5 py-10 d-flex flex-column align-items-center justify-content-center text-center gap-3">
        <h1 class="display-4 fw-bold text-white">2 Million Recipes for You to Choose</h1>
        <p class="lead text-white">2 Million Recipes, by professional chefs and the community around you. Join now to experience freedom of choice when it comes to cooking.</p>
        <a href="#" class="btn btn-primary btn-lg"><span class="overpass-mono-light text-white fw-bold">Get Started</span></a>
    </div>

    <section class="our_new_recipes home_info">
        <h2 class="text-center mt-4">Our New Recipes</h2>
        <div class="container my-3 d-none d-md-block">
            <div class="row g-4">
            <?php
                $title = "Low-Fat Berry Blue Frozen Dessert";
                $description = "A refreshing low-fat blueberry frozen dessert bursting with natural sweetness and vibrant color. Perfect for a guilt-free treat on a hot day.";
                $photo = "assets/images/recipe_1.jpg";
                include 'reusables/home_recipe.php';
            ?>
            <?php
                $title = "Roast Prime Rib au Poivre with Mixed Peppercorns";
                $description = "White, black, green, and pink peppercorns add wonderful flavor to this very special prime rib. If possible, search out a butcher who carries dry-aged beef-it&rsquo;s more tender, flavorful, and juicy than the non-aged variety. A full-bodied California Cabernet Sauvignon or French Bordeaux is the perfect wine to serve. As for vegetables, mix butter and tarragon with cooked baby carrots and green beans for a delicious accompaniment.";
                $photo = "assets/images/recipe_2.png";
                include 'reusables/home_recipe.php';
            ?>
            <?php
                $title = "Matcha Oat Milk Latte";
                $description = "A refreshing matcha oat milk latte which strikes a harmonious balance between deep matcha flavors, nice creaminess and a light, balanced natural sweetness.";
                $photo = "assets/images/recipe_3.jpg";
                include 'reusables/home_recipe.php';
            ?>
            </div>
        </div>

        <div class="container my-5 d-md-none">
            <div id="recipesCarousel" class="carousel slide" data-bs-ride="true">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <?php
                            $title = "Low-Fat Berry Blue Frozen Dessert";
                            $description = "A refreshing low-fat blueberry frozen dessert bursting with natural sweetness and vibrant color. Perfect for a guilt-free treat on a hot day.";
                            $photo = "assets/images/recipe_1.jpg";
                            include 'reusables/home_recipe.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $title = "Roast Prime Rib au Poivre with Mixed Peppercorns";
                            $description = "White, black, green, and pink peppercorns add wonderful flavor to this very special prime rib. If possible, search out a butcher who carries dry-aged beef-it&rsquo;s more tender, flavorful, and juicy than the non-aged variety. A full-bodied California Cabernet Sauvignon or French Bordeaux is the perfect wine to serve. As for vegetables, mix butter and tarragon with cooked baby carrots and green beans for a delicious accompaniment.";
                            $photo = "assets/images/recipe_2.png";
                            include 'reusables/home_recipe.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $title = "Matcha Oat Milk Latte";
                            $description = "A refreshing matcha oat milk latte which strikes a harmonious balance between deep matcha flavors, nice creaminess and a light, balanced natural sweetness.";
                            $photo = "assets/images/recipe_3.jpg";
                            include 'reusables/home_recipe.php';
                        ?>
                    </div>
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
                include 'reusables/home_user_recommendation.php';
            ?>
            <?php
                $name = "James Oliver";
                $review = "The recipe variety is incredible and easy to follow. My family has never eaten this well before!";
                $photo = "assets/images/customer_2.jpg";
                include 'reusables/home_user_recommendation.php';
            ?>
            <?php
                $name = "Kevin Chen";
                $review = "I love how I can plan meals around what's already in my fridge. It's saved me so much money on groceries.";
                $photo = "assets/images/customer_3.jpg";
                include 'reusables/home_user_recommendation.php';
            ?>
            <?php
                $name = "Daniel Reyes";
                $review = "As someone with food allergies, MealMate makes it so easy to find safe recipes. It's been a lifesaver for me.";
                $photo = "assets/images/customer_4.jpg";
                include 'reusables/home_user_recommendation.php';
            ?>
            <?php
                $name = "Priya Sharma";
                $review = "I've discovered so many new cuisines through this platform. Cooking has become my favourite hobby thanks to MealMate.";
                $photo = "assets/images/customer_5.jpg";
                include 'reusables/home_user_recommendation.php';
            ?>
            <?php
                $name = "Marcus Johnson";
                $review = "The meal planner keeps me on track with my fitness goals. I've never felt more organised in the kitchen.";
                $photo = "assets/images/customer_6.jpg";
                include 'reusables/home_user_recommendation.php';
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
                            include 'reusables/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "James Oliver";
                            $review = "The recipe variety is incredible and easy to follow. My family has never eaten this well before!";
                            $photo = "assets/images/customer_2.jpg";
                            include 'reusables/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "Kevin Chen";
                            $review = "I love how I can plan meals around what's already in my fridge. It's saved me so much money on groceries.";
                            $photo = "assets/images/customer_3.jpg";
                            include 'reusables/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "Daniel Reyes";
                            $review = "As someone with food allergies, MealMate makes it so easy to find safe recipes. It's been a lifesaver for me.";
                            $photo = "assets/images/customer_4.jpg";
                            include 'reusables/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "Priya Sharma";
                            $review = "I've discovered so many new cuisines through this platform. Cooking has become my favourite hobby thanks to MealMate.";
                            $photo = "assets/images/customer_5.jpg";
                            include 'reusables/home_user_recommendation.php';
                        ?>
                    </div>
                    <div class="carousel-item">
                        <?php
                            $name = "Marcus Johnson";
                            $review = "The meal planner keeps me on track with my fitness goals. I've never felt more organised in the kitchen.";
                            $photo = "assets/images/customer_6.jpg";
                            include 'reusables/home_user_recommendation.php';
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
        <button class="btn btn-primary btn-lg">
            <a class="text-white text-decoration-none" href="#">Contact Us</a>
        </button>   
    </section>

    <?php include 'reusables/footer.php' ?>
</body>
</html>