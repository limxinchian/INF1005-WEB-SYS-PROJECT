<?php
// ============================================================
//  auth/register-process.php
//  PURPOSE : Handle registration form POST submission
//  OWNER   : Member 1
// ============================================================
require_once '../config/session.php';
require_once '../config/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('./register.php');
}

// Verify CSRF token
verifyCsrfToken();

// Sanitize inputs
$username         = trim(filter_input(INPUT_POST, 'username',         FILTER_SANITIZE_SPECIAL_CHARS));
$email            = trim(filter_input(INPUT_POST, 'email',            FILTER_SANITIZE_EMAIL));
$password         = trim(filter_input(INPUT_POST, 'password',         FILTER_DEFAULT));
$confirm_password = trim(filter_input(INPUT_POST, 'confirm_password', FILTER_DEFAULT));
$terms            = isset($_POST['terms']);
$dietary_tags     = isset($_POST['dietary_tags']) ? $_POST['dietary_tags'] : [];

// Terms check
if (!$terms) {
    setFlash('warning', 'You must agree to the Terms and Conditions.');
    redirect('./register.php');
}

// Username checks
if (empty($username)) {
    setFlash('warning', 'Username is required.');
    redirect('./register.php');
}
if (strlen($username) < 3 || strlen($username) > 60) {
    setFlash('warning', 'Username must be between 3 and 60 characters.');
    redirect('./register.php');
}
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    setFlash('warning', 'Username can only contain letters, numbers, and underscores.');
    redirect('./register.php');
}

// Email check
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('warning', 'Please enter a valid email address.');
    redirect('./register.php');
}

// Password checks
if (strlen($password) < 8) {
    setFlash('warning', 'Password must be at least 8 characters.');
    redirect('./register.php');
}
if ($password !== $confirm_password) {
    setFlash('warning', 'Passwords do not match.');
    redirect('./register.php');
}

// Check duplicates
try {
    $stmt = $pdo->prepare("
        SELECT
            SUM(username = ?) AS username_taken,
            SUM(email    = ?) AS email_taken
        FROM users
    ");
    $stmt->execute([$username, $email]);
    $check = $stmt->fetch();

    if ($check['username_taken'] > 0) {
        setFlash('warning', 'That username is already taken. Please choose another.');
        redirect('./register.php');
    }
    if ($check['email_taken'] > 0) {
        setFlash('warning', 'An account with that email already exists. Try logging in.');
        redirect('./register.php');
    }

} catch (PDOException $e) {
    error_log('Register check error: ' . $e->getMessage());
    setFlash('danger', 'A server error occurred. Please try again.');
    redirect('./register.php');
}

// Hash password
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Insert user
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, role)
        VALUES (?, ?, ?, 'member')
    ");
    $stmt->execute([$username, $email, $passwordHash]);
    $newUserId = (int) $pdo->lastInsertId();

    // Save dietary preferences
    if (!empty($dietary_tags)) {
        $prefStmt = $pdo->prepare("
            INSERT INTO user_dietary_preferences (user_id, tag_id)
            VALUES (?, ?)
        ");
        foreach ($dietary_tags as $tagId) {
            if (is_numeric($tagId)) {
                $prefStmt->execute([$newUserId, (int)$tagId]);
            }
        }
    }

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Register insert error: ' . $e->getMessage());
    setFlash('danger', 'Registration failed. Please try again.');
    redirect('./register.php');
}

// Send welcome email ONLY if vendor exists
// Wrapped in output buffering to prevent any accidental output
ob_start();
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    try {
        require_once '../config/mailer.php';
        $mail         = createMailer();
        $safeUsername = htmlspecialchars($username);
        $protocol     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host         = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $loginUrl     = $protocol . '://' . $host . '/login.php';

        $mail->addAddress($email, $username);
        $mail->Subject = 'Welcome to MealMate!';
        $mail->isHTML(true);
        $mail->Body    = '
            <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
                <div style="background:#2E7D32;padding:20px;text-align:center;">
                    <h1 style="color:white;margin:0;">MealMate</h1>
                </div>
                <div style="padding:30px;background:#f9f9f9;">
                    <h2>Welcome, ' . $safeUsername . '!</h2>
                    <p>Your account has been created successfully.</p>
                    <ul>
                        <li>Browse and search recipes</li>
                        <li>Save your favourite recipes</li>
                        <li>Use the Whats in My Fridge feature</li>
                        <li>Create weekly meal plans</li>
                    </ul>
                    <div style="text-align:center;margin-top:30px;">
                        <a href="' . $loginUrl . '"
                           style="background:#2E7D32;color:white;padding:12px 30px;
                                  text-decoration:none;border-radius:5px;font-weight:bold;">
                            Login to MealMate
                        </a>
                    </div>
                </div>
                <div style="padding:15px;text-align:center;color:#999;font-size:12px;">
                    <p>MealMate 2025. All rights reserved.</p>
                </div>
            </div>
        ';
        $mail->AltBody = 'Welcome to MealMate, ' . $safeUsername . '! Login at: ' . $loginUrl;
        $mail->send();

    } catch (Exception $e) {
        error_log('Welcome email failed: ' . $e->getMessage());
    }
}
ob_end_clean(); // discard any accidental output from mailer

// Set session
session_regenerate_id(true);
$_SESSION['user_id']  = $newUserId;
$_SESSION['username'] = $username;
$_SESSION['role']     = 'member';

// Redirect to dashboard
setFlash('success', 'Welcome to MealMate, ' . htmlspecialchars($username) . '! Your account has been created.');
redirect('./dashboard.php');
