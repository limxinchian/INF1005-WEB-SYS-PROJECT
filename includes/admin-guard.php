<?php
require_once __DIR__ . '/../config/session.php';

if (!isAdmin()) {
    setFlash('danger', 'You do not have permission to access that page.');
    $_projectRoot = realpath(__DIR__ . '/..');
    $_callingDir = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
    $_relPath = ltrim(str_replace('\\', '/', substr($_callingDir, strlen($_projectRoot))), '/');
    $_depth = $_relPath === '' ? 0 : substr_count($_relPath, '/') + 1;
    $_basePath = $_depth > 0 ? implode('/', array_fill(0, $_depth, '..')) : '.';
    redirect($_basePath . '/admin/dashboard.php');
}