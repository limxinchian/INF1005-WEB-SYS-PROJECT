<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        // ============================================================
        //  login.php
        //  PURPOSE : Display the login form
        //  OWNER   : Member 1
        // ============================================================
        require_once 'config/session.php';
        require_once 'config/db.php';

        // Read email back from GET parameter if redirected from failed login
        $previousEmail = htmlspecialchars($_GET['email'] ?? '');

        // If already logged in redirect away
        if (isLoggedIn()) {
            if (isAdmin()) {
                redirect('/INF1005-WEB-SYS-PROJECT/admin/dashboard.php');
            } else {
                redirect('/INF1005-WEB-SYS-PROJECT/dashboard.php');
            }
        }

        // Generate CSRF token
        $token = generateCsrfToken();

        // Get reCAPTCHA site key from .env
        $env = parse_ini_file(__DIR__ . '/.env');
        $recaptchaSiteKey = $env['RECAPTCHA_SITE_KEY'] ?? '';

        $title = 'MealMate - Login';
        require_once 'reusables/header.php';
    ?>
    <link rel="stylesheet" href="assets/css/login.css">
    
    <!-- reCAPTCHA Script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="assets/js/login.js" defer></script>
</head>
<body>
    <?php require_once 'reusables/nav.php'; ?>
    <h1 class="text-center my-lg-5">Login to MealMate</h1>
    <div class="container d-flex justify-content-center align-items-center">
        <form method="POST" action="auth/login-process.php" id="loginForm" class="w-70 px-lg-7 mx-lg-5 mx-sm-0">
            <!-- CSRF Token -->
            <input type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($token) ?>">

            <!-- Email -->
            <div class="mb-lg-3">
                <label for="email" class="form-label fw-semibold overpass-mono-normal">
                    Email Address
                </label>
                <div class="input-group">
                    <input
                        type="email"
                        class="form-control rounded overpass-mono-normal"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        value="<?= $previousEmail ?>"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>
                <div id="emailError"
                        class="text-danger small mt-lg-1 overpass-mono-normal"
                        style="display:none;">
                </div>
            </div>

            <!-- Password -->
            <div class="mb-lg-3">
                <label for="password" class="form-label fw-semibold overpass-mono-normal">
                    Password
                </label>
                <div class="input-group">
                    <input
                        type="password"
                        class="form-control overpass-mono-normal"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button
                        type="button"
                        class="btn btn-outline-secondary overpass-mono-normal"
                        id="togglePassword"
                        title="Show password"
                    >
                    <img src="assets/images/icons/visibility.svg" alt="Toggle password visibility">
                    </button>
                </div>
                <div id="passwordError"
                        class="text-danger small mt-lg-1 overpass-mono-normal"
                        style="display:none;">
                </div>
            </div>

            <!-- Remember Me -->
            <div class="mb-lg-3 form-check">
                <input type="checkbox"
                        class="form-check-input"
                        id="remember"
                        name="remember">
                <label class="form-check-label overpass-mono-normal" for="remember">
                    Remember me for 30 days
                </label>
            </div>

            <!-- Google reCAPTCHA -->
            <div class="mb-lg-3">
                <div
                    class="g-recaptcha"
                    data-sitekey="<?= htmlspecialchars($recaptchaSiteKey) ?>"
                    data-callback="onRecaptchaSuccess"
                    data-expired-callback="onRecaptchaExpired"
                ></div>
                <div id="recaptchaError"
                        class="text-danger small mt-lg-1 overpass-mono-normal"
                        style="display:none;">
                    Please complete the reCAPTCHA verification.
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid mt-lg-4-5 mt-sm-3">
                <button type="submit"
                        class="btn btn-success btn-lg overpass-mono-normal"
                        id="submitBtn">
                    Sign In
                </button>
            </div>

        </form>
    </div>
    <?php require_once 'reusables/footer.php'; ?>
</body>
</html>