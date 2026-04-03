
<?php
    function getImageLink($title, $recipeId) {
        $recipeImgName = str_replace([' ', '/', ':', '?', ','], '_', $title);
        $recipeImgName = $recipeId . '_' . $recipeImgName;
        $recipeImage = "https://storage.googleapis.com/mealmate_recipe_images/{$recipeImgName}";
        return $recipeImage;
    }
?>