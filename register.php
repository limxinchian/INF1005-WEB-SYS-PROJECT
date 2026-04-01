<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        // ============================================================
        //  register.php
        //  PURPOSE : Display the registration form
        //  OWNER   : Member 1
        // ============================================================
        require_once 'config/session.php';
        require_once 'config/db.php';

        // If already logged in, redirect away
        if (isLoggedIn()) {
            if (isAdmin()) {
                redirect('/INF1005-WEB-SYS-PROJECT/admin/dashboard.php');
            } else {
                redirect('/INF1005-WEB-SYS-PROJECT/dashboard.php');
            }
        }

        // Generate CSRF token
        $token = generateCsrfToken();

        // Load dietary tags BEFORE any HTML output
        $tags = $pdo->query("SELECT tag_id, tag_name FROM dietary_tags ORDER BY tag_name")->fetchAll();

        $title = 'MealMate - Register';
        require_once 'reusables/header.php';
    ?>
    <link rel="stylesheet" href="assets/css/register.css">
    
    <!-- reCAPTCHA Script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="assets/js/register.js" defer></script>
</head>
<body>
    <?php require_once 'reusables/nav.php'; ?>
    <h1 class="text-center my-lg-5">Register your Account</h1>
    <div class="container d-flex justify-content-center align-items-center">
        <form method="POST" action="auth/register-process.php" id="registerForm" class="w-70 px-lg-7 mx-lg-5 mx-sm-0" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">

            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold overpass-mono-normal">Username</label>
                <div class="input-group">   
                    <input
                        type="text"
                        class="form-control overpass-mono-normal"
                        id="username"
                        name="username"
                        placeholder="e.g. johndoe"
                        minlength="3"
                        maxlength="60"
                        required
                        autofocus
                    >
                </div>
                <div class="form-text overpass-mono-normal">3-60 characters. Letters, numbers, underscores only.</div>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold overpass-mono-normal">Email Address</label>
                <div class="input-group">
                    <input
                        type="email"
                        class="form-control overpass-mono-normal"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        required
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold overpass-mono-normal">Password</label>
                <div class="input-group">
                    <input
                        type="password"
                        class="form-control overpass-mono-normal"
                        id="password"
                        name="password"
                        placeholder="At least 8 characters"
                        minlength="8"
                        required
                    >
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                        <img src="assets/images/icons/visibility.svg" alt="Show password">
                    </button>
                </div>
                <div class="mt-2">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" id="strengthBar" role="progressbar" style="width: 0%" aria-label="progress bar"></div>
                    </div>
                    <small id="strengthText" class="text-muted"></small>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
                <label for="confirm_password" class="form-label fw-semibold overpass-mono-normal">
                    Confirm Password
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light">&#128274;</span>
                    <input
                        type="password"
                        class="form-control overpass-mono-normal"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Repeat your password"
                        required
                    >
                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirm">
                        <img src="assets/images/icons/visibility.svg" alt="Show password">
                    </button>
                </div>
                <small id="matchText" class="text-muted"></small>
            </div>

            <!-- Dietary Preferences -->
            <div class="mb-3">
                <label class="form-label fw-semibold overpass-mono-normal">
                    Dietary Preferences
                    <span class="text-muted fw-normal">(optional)</span>
                </label>
                <div class="row g-2">
                    <?php foreach ($tags as $tag): ?>
                    <div class="col-6 col-md-4">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="dietary_tags[]"
                                value="<?= (int)$tag['tag_id'] ?>"
                                id="tag_<?= (int)$tag['tag_id'] ?>"
                            >
                            <label class="form-check-label overpass-mono-normal"
                                    for="tag_<?= (int)$tag['tag_id'] ?>">
                                <?= htmlspecialchars($tag['tag_name']) ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Terms -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input"
                        id="terms" name="terms" required>
                <label class="form-check-label overpass-mono-normal" for="terms">
                    I agree to the
                    <a  target="_blank" rel="noopener noreferrer" href="tos.php" class="text-success">Terms of Service</a>
                </label>
            </div>

            <!-- Submit -->
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-success btn-lg">
                    Create Account
                </button>
            </div>
        </form>
    </div>
    <?php require_once 'reusables/footer.php'; ?>
</body>
</html>
