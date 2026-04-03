<?php
// ============================================================
//  auth/reset-password-process.php
//  PURPOSE : Update password using reset token
//  OWNER   : Member 1
// ============================================================
require_once '../config/session.php';
require_once '../config/db.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('./forgot-password.php');
}

verifyCsrfToken();

$token           = trim($_POST['token']            ?? '');
$newPassword     = trim($_POST['new_password']     ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

// Validate token present
if (empty($token)) {
    setFlash('danger', 'Invalid reset link. Please request a new one.');
    redirect('./forgot-password.php');
}

// Validate passwords
if (empty($newPassword) || empty($confirmPassword)) {
    setFlash('warning', 'Please fill in both password fields.');
    redirect('./reset-password.php?token=' . urlencode($token));
}

if (strlen($newPassword) < 8) {
    setFlash('warning', 'Password must be at least 8 characters.');
    redirect('./reset-password.php?token=' . urlencode($token));
}

if ($newPassword !== $confirmPassword) {
    setFlash('warning', 'Passwords do not match.');
    redirect('./reset-password.php?token=' . urlencode($token));
}

// Look up token in users table
try {
    $stmt = $pdo->prepare("
        SELECT user_id, username, reset_token_expiry
        FROM users
        WHERE reset_token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

} catch (PDOException $e) {
    error_log('Reset process lookup error: ' . $e->getMessage());
    setFlash('danger', 'A server error occurred. Please try again.');
    redirect('./forgot-password.php');
}

// Token not found
if (!$user) {
    setFlash('danger', 'Invalid reset link. Please request a new one.');
    redirect('./forgot-password.php');
}

// Token expired
$expiryTs = strtotime($user['reset_token_expiry'] ?? '');
if (empty($user['reset_token_expiry']) || $expiryTs === false || $expiryTs < time()) {
    // Clear expired token from users table
    $pdo->prepare("
        UPDATE users
        SET reset_token        = NULL,
            reset_token_expiry = NULL
        WHERE user_id = ?
    ")->execute([$user['user_id']]);

    setFlash('warning', 'This reset link has expired. Please request a new one.');
    redirect('./forgot-password.php');
}

// Hash new password
$newHash = password_hash($newPassword, PASSWORD_BCRYPT);

// Update password AND clear the token in one query
try {
    $stmt = $pdo->prepare("
        UPDATE users
        SET password_hash      = ?,
            reset_token        = NULL,
            reset_token_expiry = NULL
        WHERE user_id = ?
    ");
    $stmt->execute([$newHash, $user['user_id']]);

} catch (PDOException $e) {
    error_log('Password update error: ' . $e->getMessage());
    setFlash('danger', 'Failed to reset password. Please try again.');
    redirect('./reset-password.php?token=' . urlencode($token));
}

// Success
setFlash('success', 'Password reset successfully! Please log in with your new password.');
redirect('./login.php');