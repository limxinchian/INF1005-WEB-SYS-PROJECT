<?php
if (!isset($basePath)) {
    $_projectRoot = realpath(__DIR__ . '/..');
    $_callingDir = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
    $_relPath = ltrim(str_replace('\\', '/', substr($_callingDir, strlen($_projectRoot))), '/');
    $_depth = $_relPath === '' ? 0 : substr_count($_relPath, '/') + 1;
    $basePath = $_depth > 0 ? implode('/', array_fill(0, $_depth, '..')) : '.';
}
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($title) ? $title : 'MealMate'; ?></title>
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/custom.css">
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/global.css">

<script src="<?= $basePath ?>/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>