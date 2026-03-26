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
$recaptchaSiteKey = $_ENV['RECAPTCHA_SITE_KEY'] ?? '';

require_once 'includes/header.php';
?>

<!-- reCAPTCHA Script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">

        <div class="card shadow-sm border-0 mt-4">

            <!-- Card Header -->
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0 fw-bold">&#127859; MealMate</h4>
                <small>Sign in to your account</small>
            </div>

            <!-- Card Body -->
            <div class="card-body p-4">

                <form method="POST"
                      action="auth/login-process.php"
                      id="loginForm"
                      novalidate>

                    <!-- CSRF Token -->
                    <input type="hidden"
                           name="csrf_token"
                           value="<?= htmlspecialchars($token) ?>">

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            Email Address
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                &#128231;
                            </span>
                            <input
                                type="email"
                                class="form-control"
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
                             class="text-danger small mt-1"
                             style="display:none;">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                &#128274;
                            </span>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                id="togglePassword"
                                title="Show password"
                            >
                                &#128065;
                            </button>
                        </div>
                        <div id="passwordError"
                             class="text-danger small mt-1"
                             style="display:none;">
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-3 form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="remember"
                               name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me for 30 days
                        </label>
                    </div>

                    <!-- Google reCAPTCHA -->
                    <div class="mb-3">
                        <div
                            class="g-recaptcha"
                            data-sitekey="<?= htmlspecialchars($recaptchaSiteKey) ?>"
                            data-callback="onRecaptchaSuccess"
                            data-expired-callback="onRecaptchaExpired"
                        ></div>
                        <div id="recaptchaError"
                             class="text-danger small mt-1"
                             style="display:none;">
                            Please complete the reCAPTCHA verification.
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mt-3">
                        <button type="submit"
                                class="btn btn-success btn-lg"
                                id="submitBtn">
                            Sign In
                        </button>
                    </div>

                </form>

            </div>
            <!-- Card Footer -->
            <div class="card-footer text-center bg-light py-3">
                <small class="text-muted">
                    Don't have an account?
                    <a href="register.php" class="text-success fw-semibold">
                        Register here
                    </a>
                </small>
            </div>
            <div class="card-footer text-center bg-light py-3">
                <small class="text-muted">
                    Forgot Password?
                    <a href="forgotpassword.php" class="text-success fw-semibold">
                        Forgot Password
                    </a>
                </small>
            </div>

        </div>

    </div>
</div>

<script>
// ── reCAPTCHA State ───────────────────────────────────────
let recaptchaDone = false;

function onRecaptchaSuccess() {
    recaptchaDone = true;
    document.getElementById('recaptchaError').style.display = 'none';
}

function onRecaptchaExpired() {
    recaptchaDone = false;
}

// ── Show/Hide Password ────────────────────────────────────
document.getElementById('togglePassword').addEventListener('click', function () {
    const pwd  = document.getElementById('password');
    pwd.type   = pwd.type === 'password' ? 'text' : 'password';
    this.title = pwd.type === 'password' ? 'Show password' : 'Hide password';
});

// ── Client-side Validation ────────────────────────────────
document.getElementById('loginForm').addEventListener('submit', function (e) {

    let valid = true;

    const email    = document.getElementById('email');
    const password = document.getElementById('password');

    // Reset all errors
    setError(email,    'emailError',    null);
    setError(password, 'passwordError', null);
    document.getElementById('recaptchaError').style.display = 'none';

    // Validate email
    if (email.value.trim() === '') {
        setError(email, 'emailError', 'Email address is required.');
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        setError(email, 'emailError', 'Please enter a valid email address.');
        valid = false;
    }

    // Validate password
    if (password.value.trim() === '') {
        setError(password, 'passwordError', 'Password is required.');
        valid = false;
    }

    // Validate reCAPTCHA
    if (!recaptchaDone) {
        document.getElementById('recaptchaError').style.display = 'block';
        valid = false;
    }

    // Stop form if any error
    if (!valid) {
        e.preventDefault();
        return;
    }

    // Show loading spinner on button
    const btn     = document.getElementById('submitBtn');
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"' +
                   ' role="status" aria-hidden="true"></span>Signing in...';
});

// ── Show or Clear Field Error ─────────────────────────────
function setError(input, errorId, message) {
    const errorDiv = document.getElementById(errorId);
    if (message) {
        input.classList.add('is-invalid');
        errorDiv.textContent   = message;
        errorDiv.style.display = 'block';
    } else {
        input.classList.remove('is-invalid');
        errorDiv.textContent   = '';
        errorDiv.style.display = 'none';
    }
}

// ── Clear Error When User Starts Typing ──────────────────
document.getElementById('email').addEventListener('input', function () {
    setError(this, 'emailError', null);
});

document.getElementById('password').addEventListener('input', function () {
    setError(this, 'passwordError', null);
});
</script>

<?php require_once 'includes/footer.php'; ?>