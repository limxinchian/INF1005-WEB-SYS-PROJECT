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
    redirect('./login.php');
}

// Verify CSRF token
verifyCsrfToken();

// Sanitize inputs
$email    = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL));
$password = trim(filter_input(INPUT_POST, 'password', FILTER_DEFAULT));

// Validate not empty
if (empty($email) || empty($password)) {
    setFlash('warning', 'Please enter both your email and password.');
    redirect('./login.php?email=' . urlencode($email ?? ''));
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('warning', 'Please enter a valid email address.');
    redirect('./login.php?email=' . urlencode($email));
}

// ── Verify reCAPTCHA ──────────────────────────────────────
$env = parse_ini_file(__DIR__ . '/../.env');
$recaptchaSecret = $env['RECAPTCHA_SECRET_KEY'] ?? '';
$recaptchaResponse = trim($_POST['g-recaptcha-response'] ?? '');

// Check reCAPTCHA response exists
if (empty($recaptchaResponse)) {
    setFlash('warning', 'Please complete the reCAPTCHA verification.');
    redirect('./login.php?email=' . urlencode($email));
}

// Verify with Google API
$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify?secret='
           . urlencode($recaptchaSecret)
           . '&response=' . urlencode($recaptchaResponse)
           . '&remoteip=' . urlencode($_SERVER['REMOTE_ADDR'] ?? '');

$recaptchaRaw    = file_get_contents($verifyUrl);
$recaptchaResult = json_decode($recaptchaRaw, true);

if (!isset($recaptchaResult['success']) || $recaptchaResult['success'] !== true) {
    setFlash('warning', 'reCAPTCHA verification failed. Please try again.');
    redirect('./login.php?email=' . urlencode($email));
}
// ── End reCAPTCHA Verification ────────────────────────────

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
    redirect('./login.php?email=' . urlencode($email));
}

// Verify password
// Same message for wrong email or wrong password — security best practice
if (!$user || !password_verify($password, $user['password_hash'])) {
    setFlash('danger', 'Incorrect email or password. Please try again.');
    redirect('./login.php?email=' . urlencode($email));
}

// Regenerate session ID to prevent session fixation attack
session_regenerate_id(true);

// Set session variables
$_SESSION['user_id']  = (int) $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];

// Handle Remember Me cookie (30 days)
if (!empty($_POST['remember'])) {
    $cookieValue  = base64_encode(
        $user['user_id'] . ':' . hash('sha256', $user['password_hash'])
    );
    $cookieExpiry = time() + (30 * 24 * 60 * 60);
    setcookie(
        'remember_me',
        $cookieValue,
        $cookieExpiry,
        './',
        '',
        false,
        true
    );
}

// Flash welcome message
setFlash('success', 'Welcome back, ' . htmlspecialchars($user['username']) . '!');

// Redirect based on role
if ($user['role'] === 'admin') {
    redirect('../admin/dashboard.php');
} else {
    redirect('../dashboard.php');
}
