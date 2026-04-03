<!DOCTYPE html>
<html lang="en">
<head>
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
            redirect('./dashboard.php');
        }

        $token = generateCsrfToken();

        $title = 'MealMate - Forgot Password';
        require_once 'includes/header.php';
        ?>
        <script src="./assets/js/forgotpassword.js" defer></script>
</head>
<body>
    <?php require_once 'includes/nav.php'; ?>
    <main>
    <div class="container d-flex justify-content-center align-items-center flex-column gap-4">
        <h1 class="text-center my-5">Reset your Password</h1>
        <form method="POST"
                action="./auth/forgot-password-process.php"
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
        <p class="text-center mt-3">
            Remembered your password?
            <a href="login.php">Login here</a>
        </p>
    </div>
    </main>
    <?php require_once 'includes/footer.php' ?>
</body>
</html>