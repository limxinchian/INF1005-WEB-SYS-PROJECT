<?php
require_once __DIR__ . '/../config/session.php';

if (!isAdmin()) {
    setFlash('danger', 'You do not have permission to access that page.');
    redirect('/INF1005-WEB-SYS-PROJECT/index.php');
}