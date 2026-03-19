<?php
// ============================================================
//  auth/login-process.php
//  PURPOSE : Handle login form POST submission
//  OWNER   : Member 1
// ============================================================
require_once '../config/session.php';
require_once '../config/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/INF1005-WEB-SYS-PROJECT/login.php');
}

// Verify CSRF token
verifyCsrfToken();

// Sanitize inputs
$email    = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL));
$password = trim(filter_input(INPUT_POST, 'password', FILTER_DEFAULT));

// Basic validation — pass email back so login form can prefill it
if (empty($email) || empty($password)) {
    setFlash('warning', 'Please enter both your email and password.');
    redirect('/INF1005-WEB-SYS-PROJECT/login.php?email=' . urlencode($email ?? ''));
}

// Email format validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('warning', 'Please enter a valid email address.');
    redirect('/INF1005-WEB-SYS-PROJECT/login.php?email=' . urlencode($email));
}

// Look up user in database
try {
    $stmt = $pdo->prepare("
        SELECT user_id, username, email, password_hash, role
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

} catch (PDOException $e) {
    error_log('Login DB error: ' . $e->getMessage());
    setFlash('danger', 'A server error occurred. Please try again.');
    redirect('/INF1005-WEB-SYS-PROJECT/login.php?email=' . urlencode($email));
}

// Verify password — same message whether email or password is wrong (security)
if (!$user || !password_verify($password, $user['password_hash'])) {
    setFlash('danger', 'Incorrect email or password. Please try again.');
    redirect('/INF1005-WEB-SYS-PROJECT/login.php?email=' . urlencode($email));
}

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Set session variables
$_SESSION['user_id']  = (int) $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];

// Handle Remember Me cookie (30 days)
if (!empty($_POST['remember'])) {
    $cookieValue  = base64_encode($user['user_id'] . ':' . hash('sha256', $user['password_hash']));
    $cookieExpiry = time() + (30 * 24 * 60 * 60);
    setcookie('remember_me', $cookieValue, $cookieExpiry, '/INF1005-WEB-SYS-PROJECT/', '', false, true);
}

// Flash welcome message
setFlash('success', 'Welcome back, ' . htmlspecialchars($user['username']) . '!');

// Redirect based on role
if ($user['role'] === 'admin') {
    redirect('/INF1005-WEB-SYS-PROJECT/admin/dashboard.php');
} else {
    redirect('/INF1005-WEB-SYS-PROJECT/dashboard.php');
}