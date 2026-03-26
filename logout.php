<?php
// ============================================================
//  auth/logout.php
//  PURPOSE : Destroy session and log the user out
//  OWNER   : Member 1
// ============================================================
require_once 'config/session.php';

// Only log out if actually logged in
if (!isLoggedIn()) {
    redirect('/INF1005-WEB-SYS-PROJECT/login.php');
}

// Save username for goodbye message before destroying session
$username = currentUsername();

// Step 1 — Clear all session variables
$_SESSION = [];

// Step 2 — Destroy the session cookie (PHPSESSID)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Step 3 — Destroy the session on the server
session_destroy();

// Step 4 — Clear the Remember Me cookie if it exists
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 42000, '/INF1005-WEB-SYS-PROJECT/');
}

// Step 5 — Start a NEW session just to show the flash message
session_start();
setFlash('success', 'You have been logged out successfully. See you soon, ' . htmlspecialchars($username) . '!');

// Step 6 — Redirect to login page
redirect('/INF1005-WEB-SYS-PROJECT/login.php');