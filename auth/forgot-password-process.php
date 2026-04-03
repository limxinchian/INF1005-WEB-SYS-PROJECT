<?php
// ============================================================
//  auth/forgot-password-process.php
//  PURPOSE : Generate reset token and send email
//  OWNER   : Member 1
// ============================================================
require_once '../config/session.php';
require_once '../config/db.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../forgot-password.php');
}

verifyCsrfToken();

$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('warning', 'Please enter a valid email address.');
    redirect('../forgot-password.php');
}

// Always show same message - never reveal if email exists
$genericMessage = 'If that email is registered, a reset link has been sent. Please check your inbox.';

// Look up user
try {
    $stmt = $pdo->prepare("
        SELECT user_id, username, email
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

} catch (PDOException $e) {
    error_log('Forgot password lookup error: ' . $e->getMessage());
    setFlash('info', $genericMessage);
    redirect('../forgot-password.php');
}

// Email not found - show generic message anyway
if (!$user) {
    setFlash('info', $genericMessage);
    redirect('../forgot-password.php');
}

// Generate secure token and expiry (1 hour from now)
$resetToken  = bin2hex(random_bytes(32));
$tokenExpiry = date('Y-m-d H:i:s', time() + 3600);

// Store token directly in users table - no new table needed
try {
    $stmt = $pdo->prepare("
        UPDATE users
        SET reset_token        = ?,
            reset_token_expiry = ?
        WHERE user_id = ?
    ");
    $stmt->execute([$resetToken, $tokenExpiry, $user['user_id']]);

} catch (PDOException $e) {
    error_log('Reset token store error: ' . $e->getMessage());
    setFlash('info', $genericMessage);
    redirect('../forgot-password.php');
}

error_log('Token stored for user_id: ' . $user['user_id']);

// Send reset email
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptDir = rtrim($scriptDir, '/\\');
$resetUrl  = $protocol . '://' . $host . $scriptDir . '/reset-password.php?token=' . $resetToken;
error_log('Reset URL generated: ' . $resetUrl);
$safeUsername = htmlspecialchars($user['username']);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    try {
        require_once '../config/mailer.php';

        $mail = createMailer();
        $mail->addAddress($user['email'], $user['username']);
        $mail->Subject = 'MealMate - Password Reset Request';
        $mail->isHTML(true);

        $mail->Body = '
            <div style="font-family:Arial,sans-serif;
                        max-width:600px;margin:0 auto;">
                <div style="background:#2E7D32;padding:20px;
                            text-align:center;">
                    <h1 style="color:white;margin:0;">
                        &#127859; MealMate
                    </h1>
                </div>
                <div style="padding:30px;background:#f9f9f9;">
                    <h2>Hi ' . $safeUsername . ',</h2>
                    <p>We received a request to reset your
                       MealMate password.</p>
                    <p>Click the button below to set a new password.
                       This link expires in
                       <strong>1 hour</strong>.</p>
                    <div style="text-align:center;margin:30px 0;">
                        <a href="' . $resetUrl . '"
                           style="background:#2E7D32;color:white;
                                  padding:14px 30px;
                                  text-decoration:none;
                                  border-radius:5px;
                                  font-weight:bold;
                                  font-size:16px;">
                            Reset My Password
                        </a>
                    </div>
                    <p style="color:#999;font-size:13px;">
                        If you did not request this, you can safely
                        ignore this email. Your password will not change.
                    </p>
                    <p style="color:#999;font-size:13px;">
                        Link expires at: ' . $tokenExpiry . '
                    </p>
                </div>
                <div style="padding:15px;text-align:center;
                            color:#999;font-size:12px;">
                    <p>MealMate 2025. All rights reserved.</p>
                </div>
            </div>
        ';

        $mail->AltBody = 'Hi ' . $safeUsername .
                         ', reset your password here: ' .
                         $resetUrl .
                         ' (expires in 1 hour)';
        $mail->send();

    } catch (Exception $e) {
        error_log('Reset email send error: ' . $e->getMessage());
    }
} else {
    error_log('Composer vendor not found - reset email skipped.');
}

setFlash('info', $genericMessage);
redirect('../forgot-password.php');