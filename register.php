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

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

        <div class="card shadow-sm border-0 mt-4">

            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0 fw-bold">&#127859; MealMate</h4>
                <small>Create your free account</small>
            </div>

            <div class="card-body p-4">

                <form method="POST" action="auth/register-process.php" novalidate>

                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($token) ?>">

                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">&#128100;</span>
                            <input
                                type="text"
                                class="form-control"
                                id="username"
                                name="username"
                                placeholder="e.g. johndoe"
                                minlength="3"
                                maxlength="60"
                                required
                                autofocus
                            >
                        </div>
                        <div class="form-text">3-60 characters. Letters, numbers, underscores only.</div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">&#128231;</span>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="you@example.com"
                                required
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">&#128274;</span>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="At least 8 characters"
                                minlength="8"
                                required
                            >
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                &#128065;
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" id="strengthBar"
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="strengthText" class="text-muted"></small>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-semibold">
                            Confirm Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">&#128274;</span>
                            <input
                                type="password"
                                class="form-control"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Repeat your password"
                                required
                            >
                            <button type="button" class="btn btn-outline-secondary" id="toggleConfirm">
                                &#128065;
                            </button>
                        </div>
                        <small id="matchText" class="text-muted"></small>
                    </div>

                    <!-- Dietary Preferences -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
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
                                    <label class="form-check-label"
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
                        <label class="form-check-label" for="terms">
                            I agree to the
                            <a href="#" class="text-success">Terms &amp; Conditions</a>
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

            <div class="card-footer text-center bg-light py-3">
                <small class="text-muted">
                    Already have an account?
                    <a href="login.php" class="text-success fw-semibold">Sign in here</a>
                </small>
            </div>

        </div>

    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const pwd = document.getElementById('password');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
});

document.getElementById('toggleConfirm').addEventListener('click', function () {
    const pwd = document.getElementById('confirm_password');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
});

document.getElementById('password').addEventListener('input', function () {
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
});

document.getElementById('confirm_password').addEventListener('input', function () {
    const pwd     = document.getElementById('password').value;
    const confirm = this.value;
    const text    = document.getElementById('matchText');

    if (confirm === '') {
        text.textContent = '';
    } else if (pwd === confirm) {
        text.textContent = 'Passwords match';
        text.className   = 'text-success small';
    } else {
        text.textContent = 'Passwords do not match';
        text.className   = 'text-danger small';
    }
});

document.querySelector('form').addEventListener('submit', function (e) {
    const pwd     = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const terms   = document.getElementById('terms').checked;

    if (pwd !== confirm) {
        e.preventDefault();
        alert('Passwords do not match.');
        return;
    }
    if (pwd.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters.');
        return;
    }
    if (!terms) {
        e.preventDefault();
        alert('You must agree to the Terms and Conditions.');
        return;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>