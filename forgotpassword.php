<?php
// ============================================================
//  forgot-password.php
//  PURPOSE : Show form to request password reset email
//  OWNER   : Member 1
// ============================================================
require_once 'config/session.php';
require_once 'config/db.php';

// If already logged in redirect away
if (isLoggedIn()) {
    redirect('/INF1005-WEB-SYS-PROJECT/dashboard.php');
}

$token = generateCsrfToken();

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">

        <div class="card shadow-sm border-0 mt-4">

            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0 fw-bold">&#127859; MealMate</h4>
                <small>Reset your password</small>
            </div>

            <div class="card-body p-4">

                <p class="text-muted small mb-4">
                    Enter your registered email address and we will
                    send you a password reset link.
                </p>

                <form method="POST"
                      action="auth/forgot-password-process.php"
                      id="forgotForm"
                      novalidate>

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= htmlspecialchars($token) ?>">

                    <div class="mb-4">
                        <label for="email"
                               class="form-label fw-semibold">
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

                    <div class="d-grid">
                        <button type="submit"
                                class="btn btn-success btn-lg"
                                id="submitBtn">
                            &#128140; Send Reset Link
                        </button>
                    </div>

                </form>

            </div>

            <div class="card-footer text-center bg-light py-3">
                <small class="text-muted">
                    Remember your password?
                    <a href="login.php"
                       class="text-success fw-semibold">
                        Sign in here
                    </a>
                </small>
            </div>

        </div>

    </div>
</div>

<script>
document.getElementById('forgotForm').addEventListener('submit', function (e) {
    let valid  = true;
    const email = document.getElementById('email');
    setError(email, 'emailError', null);

    if (email.value.trim() === '') {
        setError(email, 'emailError', 'Email address is required.');
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        setError(email, 'emailError', 'Please enter a valid email address.');
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
        return;
    }

    const btn     = document.getElementById('submitBtn');
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"' +
                   ' role="status" aria-hidden="true"></span>Sending...';
});

function setError(input, errorId, message) {
    const div = document.getElementById(errorId);
    if (message) {
        input.classList.add('is-invalid');
        div.textContent   = message;
        div.style.display = 'block';
    } else {
        input.classList.remove('is-invalid');
        div.textContent   = '';
        div.style.display = 'none';
    }
}

document.getElementById('email').addEventListener('input', function () {
    setError(this, 'emailError', null);
});
</script>

<?php require_once 'includes/footer.php'; ?>