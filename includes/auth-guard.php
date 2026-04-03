<?php
require_once __DIR__ . '/../config/session.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Please log in to continue.');
    redirect('../login.php');
}