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
    <div class="member_review card d-flex flex flex-row p-4 min-vh-25">
<img class="rounded-circle" height="75" width="75" src="<?= htmlspecialchars($photo); ?>" alt="<?= htmlspecialchars($name); ?>">        <div class="ms-5">
            <div class="member_name">
                <p class="fs-4"><?php echo htmlspecialchars($name); ?></p>
            </div>
            <div class="review">
                <p><?php echo htmlspecialchars($review); ?></p>
            </div>
        </div>
    </div>
</div>