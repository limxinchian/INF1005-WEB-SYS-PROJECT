<?php 
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'); 
    $root = realpath(__DIR__ . '/..');
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
    $basePath = '/' . ltrim(str_replace('\\', '/', substr($root, strlen($docRoot))), '/');
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($title) ? $title : 'MealMate'; ?></title>
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/custom.css">
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/global.css">

<script src="<?= $basePath ?>/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>