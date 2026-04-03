<?php
// ============================================================
//  reset-password.php
//  PURPOSE : Show form to set new password using reset token
//  OWNER   : Member 1
// ============================================================
require_once 'config/session.php';
require_once 'config/db.php';

// If already logged in redirect away
if (isLoggedIn()) {
    redirect('./dashboard.php');
}

$token = trim($_GET['token'] ?? '');

// No token
if (empty($token)) {
    setFlash('danger', 'Invalid or missing reset link.');
    redirect('./forgot-password.php');
}

// Look up token in users table
try {
    $stmt = $pdo->prepare("
        SELECT user_id, username,
               reset_token_expiry
        FROM users
        WHERE reset_token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

} catch (PDOException $e) {
    error_log('Reset token lookup error: ' . $e->getMessage());
    setFlash('danger', 'A server error occurred. Please try again.');
    redirect('./forgot-password.php');
}

// Token not found
if (!$user) {
    setFlash('danger', 'This reset link is invalid. Please request a new one.');
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

// Token is valid - show reset form
$safeUsername = htmlspecialchars($user['username']);
$csrfToken    = generateCsrfToken();

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">

        <div class="card shadow-sm border-0 mt-4">

            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0 fw-bold">&#127859; MealMate</h4>
                <small>Set your new password</small>
            </div>

            <div class="card-body p-4">

                <p class="text-muted small mb-4">
                    Hi <strong><?= $safeUsername ?></strong>,
                    please enter your new password below.
                </p>

                <form method="POST"
                      action="auth/reset-password-process.php"
                      id="resetForm"
                      novalidate>

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= htmlspecialchars($csrfToken) ?>">

                    <input type="hidden"
                           name="token"
                           value="<?= htmlspecialchars($token) ?>">

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="new_password"
                               class="form-label fw-semibold">
                            New Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                &#128274;
                            </span>
                            <input
                                type="password"
                                class="form-control"
                                id="new_password"
                                name="new_password"
                                placeholder="At least 8 characters"
                                required
                                autofocus
                            >
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    id="toggleNew">
                                &#128065;
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar"
                                     id="strengthBar"
                                     style="width:0%">
                                </div>
                            </div>
                            <small id="strengthText"
                                   class="text-muted">
                            </small>
                        </div>
                        <div id="newPasswordError"
                             class="text-danger small mt-1"
                             style="display:none;">
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="confirm_password"
                               class="form-label fw-semibold">
                            Confirm New Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                &#128274;
                            </span>
                            <input
                                type="password"
                                class="form-control"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Repeat your new password"
                                required
                            >
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    id="toggleConfirm">
                                &#128065;
                            </button>
                        </div>
                        <div id="confirmPasswordError"
                             class="text-danger small mt-1"
                             style="display:none;">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit"
                                class="btn btn-success btn-lg"
                                id="submitBtn">
                            &#128274; Reset Password
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
document.getElementById('toggleNew').addEventListener('click', function () {
    const pwd = document.getElementById('new_password');
    pwd.type  = pwd.type === 'password' ? 'text' : 'password';
});

document.getElementById('toggleConfirm').addEventListener('click', function () {
    const pwd = document.getElementById('confirm_password');
    pwd.type  = pwd.type === 'password' ? 'text' : 'password';
});

document.getElementById('new_password').addEventListener('input', function () {
    const val      = this.value;
    const bar      = document.getElementById('strengthBar');
    const text     = document.getElementById('strengthText');
    let   strength = 0;

    if (val.length >= 8)           strength++;
    if (/[A-Z]/.test(val))         strength++;
    if (/[0-9]/.test(val))         strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const levels = [
        { width: '0%',   cls: '',           label: '' },
        { width: '25%',  cls: 'bg-danger',  label: 'Weak' },
        { width: '50%',  cls: 'bg-warning', label: 'Fair' },
        { width: '75%',  cls: 'bg-info',    label: 'Good' },
        { width: '100%', cls: 'bg-success', label: 'Strong' },
    ];

    bar.style.width  = levels[strength].width;
    bar.className    = 'progress-bar ' + levels[strength].cls;
    text.textContent = levels[strength].label;

    setError(this, 'newPasswordError', null);
});

document.getElementById('resetForm').addEventListener('submit', function (e) {
    let valid  = true;
    const pwd  = document.getElementById('new_password');
    const conf = document.getElementById('confirm_password');

    setError(pwd,  'newPasswordError',     null);
    setError(conf, 'confirmPasswordError', null);

    if (pwd.value.trim() === '') {
        setError(pwd, 'newPasswordError', 'New password is required.');
        valid = false;
    } else if (pwd.value.length < 8) {
        setError(pwd, 'newPasswordError',
                 'Password must be at least 8 characters.');
        valid = false;
    }

    if (conf.value.trim() === '') {
        setError(conf, 'confirmPasswordError',
                 'Please confirm your new password.');
        valid = false;
    } else if (pwd.value !== conf.value) {
        setError(conf, 'confirmPasswordError',
                 'Passwords do not match.');
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
        return;
    }

    const btn     = document.getElementById('submitBtn');
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"' +
                   ' role="status" aria-hidden="true"></span>Resetting...';
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

document.getElementById('confirm_password').addEventListener('input', function () {
    setError(this, 'confirmPasswordError', null);
});
</script>

<?php require_once 'includes/footer.php'; ?>