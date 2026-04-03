<?php
// ============================================================
//  config/session.php
//  PURPOSE : Start session + helper functions for role checks
//  OWNER   : Member 1
//  USED BY : Every PHP page in the project
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// AUTH HELPERS
// ============================================================

/**
 * Check if a user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is a member OR admin
 */
function isMember(): bool {
    return isset($_SESSION['role']) &&
           in_array($_SESSION['role'], ['member', 'admin']);
}

/**
 * Check if user is an admin only
 */
function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is a visitor (not logged in)
 */
function isVisitor(): bool {
    return !isLoggedIn();
}

// ============================================================
// CURRENT USER HELPERS
// ============================================================

/**
 * Get current logged-in user's ID
 * Returns null if not logged in
 */
function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current logged-in user's username
 */
function currentUsername(): ?string {
    return $_SESSION['username'] ?? null;
}

/**
 * Get current logged-in user's role
 */
function currentRole(): ?string {
    return $_SESSION['role'] ?? null;
}

// ============================================================
// SESSION MESSAGE HELPERS  (Flash Messages)
// ============================================================

/**
 * Set a one-time flash message
 * Usage: setFlash('success', 'Recipe saved!')
 *        setFlash('danger', 'Login failed.')
 *        setFlash('warning', 'Please fill all fields.')
 *        setFlash('info', 'Check your email.')
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type,      // success | danger | warning | info
        'message' => $message
    ];
}

/**
 * Display and clear the flash message
 * Call this once inside header.php so it shows on every page
 */
function showFlash(): void {
    if (isset($_SESSION['flash'])) {
        $type    = htmlspecialchars($_SESSION['flash']['type']);
        $message = htmlspecialchars($_SESSION['flash']['message']);
        echo "
        <div class='alert alert-{$type} alert-dismissible fade show mt-2' role='alert'>
            {$message}
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        unset($_SESSION['flash']); // clear after showing - shows only once
    }
}

// ============================================================
// REDIRECT HELPER
// ============================================================

/**
 * Redirect to a URL and stop execution
 * Usage: redirect('login.php');
 *        redirect('/mealmate/dashboard.php');
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

// ============================================================
// CSRF PROTECTION HELPERS
// ============================================================

/**
 * Generate a CSRF token and store it in session
 * Call this in every form page
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify the CSRF token submitted with a form
 * Call this at the top of every action/process file
 */
function verifyCsrfToken(): void {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
    ) {
        setFlash('danger', 'Invalid form submission. Please try again.');
        redirect('../index.php');
    }
}