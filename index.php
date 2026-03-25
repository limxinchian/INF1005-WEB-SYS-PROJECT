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
    <?php include 'nav.php'; ?>
    <div id="hero" class="container mt-3 p-5 py-10 d-flex flex-column align-items-center justify-content-center text-center gap-3">
        <h1 class="display-4 fw-bold text-white">2 Million Recipes for You to Choose</h1>
        <p class="lead text-white">2 Million Recipes, by professional chefs and the community around you. Join now to experience freedom of choice when it comes to cooking.</p>
        <a href="#" class="btn btn-primary btn-lg"><span class="overpass-mono-light text-white fw-bold">Get Started</span></a>
    </div>

    <section class="our_new_recipes">
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
            <div id="featuresCarousel" class="carousel slide" data-bs-ride="carousel">
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
                <button class="carousel-control-prev" type="button" data-bs-target="#featuresCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuresCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </section>
</body>
</html>