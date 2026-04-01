<?php
    if(!function_exists('custom_echo')) {
        function custom_echo($x, $length){
            if(strlen($x)<=$length){
                return $x;
            }
            else
            {
                $y=substr($x,0,$length) . '...';
                return $y;
            }
        }
    }
?>

<div class="col-md-4">
    <div class="card">
        <div class="home_recipe_photo">
            <img class="rounded" src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($title); ?>">
        </div>
        <div class="home_recipe_info p-4">
            <p class="fs-4"><?php echo htmlspecialchars($title); ?></p>
            <p><?php echo custom_echo(htmlspecialchars($description), 100); ?></p>
        </div>
        <a class="text-end me-4 mb-4 text-decoration-none" href="#">Go to Recipe →</a>
    </div>
</div>