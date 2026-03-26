<?php
// ============================================================
//  profile.php
//  PURPOSE : View and update user profile
//  OWNER   : Member 1
// ============================================================
require_once 'config/session.php';
require_once 'config/db.php';
require_once 'includes/auth-guard.php';

// Fetch current user data from database
$stmt = $pdo->prepare("
    SELECT user_id, username, email, role, avatar_url, created_at
    FROM users
    WHERE user_id = ?
");
$stmt->execute([currentUserId()]);
$user = $stmt->fetch();

// If user not found redirect to logout
if (!$user) {
    redirect('/INF1005-WEB-SYS-PROJECT/auth/logout.php');
}

// Fetch user's current dietary preferences
$prefStmt = $pdo->prepare("
    SELECT tag_id FROM user_dietary_preferences
    WHERE user_id = ?
");
$prefStmt->execute([currentUserId()]);
$userTagIds = $prefStmt->fetchAll(\PDO::FETCH_COLUMN);

// Fetch all dietary tags
$allTags = $pdo->query("
    SELECT tag_id, tag_name FROM dietary_tags ORDER BY tag_name
")->fetchAll();

// Fetch user's recipe stats
$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*)                                    AS total_recipes,
        SUM(status = 'approved')                   AS approved_recipes,
        SUM(status = 'pending')                    AS pending_recipes
    FROM recipes
    WHERE submitted_by = ?
");
$statsStmt->execute([currentUserId()]);
$stats = $statsStmt->fetch();

// Fetch favourite count
$favStmt = $pdo->prepare("
    SELECT COUNT(*) AS total_favourites
    FROM favourite_recipes
    WHERE user_id = ?
");
$favStmt->execute([currentUserId()]);
$favCount = $favStmt->fetch()['total_favourites'];

// Generate CSRF token
$token = generateCsrfToken();

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">

        <!-- Page Title -->
        <div class="d-flex align-items-center mb-4">
            <h2 class="fw-bold mb-0">&#128100; My Profile</h2>
            <span class="badge bg-success ms-3">
                <?= ucfirst(htmlspecialchars($user['role'])) ?>
            </span>
        </div>

        <div class="row g-4">

            <!-- LEFT COLUMN — Avatar + Stats -->
            <div class="col-md-4">

                <!-- Avatar Card -->
                <div class="card border-0 shadow-sm text-center p-4 mb-4">
                    <div class="mb-3">
                        <?php if (!empty($user['avatar_url'])): ?>
                            <img src="<?= htmlspecialchars($user['avatar_url']) ?>"
                                 alt="Avatar"
                                 class="rounded-circle border border-success border-3"
                                 style="width:100px;height:100px;object-fit:cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-success text-white d-inline-flex
                                        align-items-center justify-content-center border border-3
                                        border-success"
                                 style="width:100px;height:100px;font-size:2.5rem;">
                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold mb-1">
                        <?= htmlspecialchars($user['username']) ?>
                    </h5>
                    <p class="text-muted small mb-2">
                        <?= htmlspecialchars($user['email']) ?>
                    </p>
                    <p class="text-muted small mb-0">
                        Member since
                        <?= date('F Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>

                <!-- Stats Card -->
                <div class="card border-0 shadow-sm p-4">
                    <h6 class="fw-bold text-success mb-3">&#128202; My Stats</h6>
                    <div class="row text-center g-2">
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-4 fw-bold text-success">
                                    <?= (int)$stats['total_recipes'] ?>
                                </div>
                                <small class="text-muted">Recipes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-4 fw-bold text-success">
                                    <?= (int)$favCount ?>
                                </div>
                                <small class="text-muted">Favourites</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-4 fw-bold text-success">
                                    <?= (int)$stats['approved_recipes'] ?>
                                </div>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-4 fw-bold text-warning">
                                    <?= (int)$stats['pending_recipes'] ?>
                                </div>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN — Edit Forms -->
            <div class="col-md-8">

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="profileTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab"
                           href="#tab-info">
                            &#128221; Account Info
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab"
                           href="#tab-password">
                            &#128274; Change Password
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab"
                           href="#tab-dietary">
                            &#129367; Dietary Preferences
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- ── TAB 1: Account Info ── -->
                    <div class="tab-pane fade show active" id="tab-info">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4">Update Account Info</h5>

                                <form method="POST"
                                      action="actions/profile-update.php"
                                      novalidate>

                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars($token) ?>">
                                    <input type="hidden" name="action"
                                           value="update_info">

                                    <!-- Username -->
                                    <div class="mb-3">
                                        <label for="username"
                                               class="form-label fw-semibold">
                                            Username
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="username"
                                            name="username"
                                            value="<?= htmlspecialchars($user['username']) ?>"
                                            minlength="3"
                                            maxlength="60"
                                            required
                                        >
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label for="email"
                                               class="form-label fw-semibold">
                                            Email Address
                                        </label>
                                        <input
                                            type="email"
                                            class="form-control"
                                            id="email"
                                            name="email"
                                            value="<?= htmlspecialchars($user['email']) ?>"
                                            required
                                        >
                                    </div>

                                    <!-- Avatar URL -->
                                    <div class="mb-3">
                                        <label for="avatar_url"
                                               class="form-label fw-semibold">
                                            Avatar URL
                                            <span class="text-muted fw-normal">
                                                (optional — paste an image link)
                                            </span>
                                        </label>
                                        <input
                                            type="url"
                                            class="form-control"
                                            id="avatar_url"
                                            name="avatar_url"
                                            placeholder="https://example.com/photo.jpg"
                                            value="<?= htmlspecialchars($user['avatar_url'] ?? '') ?>"
                                        >
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit"
                                                class="btn btn-success">
                                            Save Changes
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB 2: Change Password ── -->
                    <div class="tab-pane fade" id="tab-password">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4">Change Password</h5>

                                <form method="POST"
                                      action="actions/profile-update.php"
                                      novalidate>

                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars($token) ?>">
                                    <input type="hidden" name="action"
                                           value="change_password">

                                    <!-- Current Password -->
                                    <div class="mb-3">
                                        <label for="current_password"
                                               class="form-label fw-semibold">
                                            Current Password
                                        </label>
                                        <div class="input-group">
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="current_password"
                                                name="current_password"
                                                placeholder="Enter current password"
                                                required
                                            >
                                            <button type="button"
                                                    class="btn btn-outline-secondary"
                                                    onclick="togglePwd('current_password')">
                                                &#128065;
                                            </button>
                                        </div>
                                    </div>

                                    <!-- New Password -->
                                    <div class="mb-3">
                                        <label for="new_password"
                                               class="form-label fw-semibold">
                                            New Password
                                        </label>
                                        <div class="input-group">
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="new_password"
                                                name="new_password"
                                                placeholder="At least 8 characters"
                                                minlength="8"
                                                required
                                            >
                                            <button type="button"
                                                    class="btn btn-outline-secondary"
                                                    onclick="togglePwd('new_password')">
                                                &#128065;
                                            </button>
                                        </div>
                                        <!-- Strength bar -->
                                        <div class="mt-2">
                                            <div class="progress" style="height:6px;">
                                                <div class="progress-bar"
                                                     id="pwdStrengthBar"
                                                     style="width:0%">
                                                </div>
                                            </div>
                                            <small id="pwdStrengthText"
                                                   class="text-muted">
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Confirm New Password -->
                                    <div class="mb-3">
                                        <label for="confirm_new_password"
                                               class="form-label fw-semibold">
                                            Confirm New Password
                                        </label>
                                        <div class="input-group">
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="confirm_new_password"
                                                name="confirm_new_password"
                                                placeholder="Repeat new password"
                                                required
                                            >
                                            <button type="button"
                                                    class="btn btn-outline-secondary"
                                                    onclick="togglePwd('confirm_new_password')">
                                                &#128065;
                                            </button>
                                        </div>
                                        <small id="pwdMatchText"
                                               class="text-muted">
                                        </small>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit"
                                                class="btn btn-success">
                                            Update Password
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB 3: Dietary Preferences ── -->
                    <div class="tab-pane fade" id="tab-dietary">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4">
                                    Dietary Preferences
                                </h5>
                                <p class="text-muted small mb-3">
                                    These are used to personalise your
                                    recipe suggestions.
                                </p>

                                <form method="POST"
                                      action="actions/profile-update.php"
                                      novalidate>

                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars($token) ?>">
                                    <input type="hidden" name="action"
                                           value="update_dietary">

                                    <div class="row g-2 mb-4">
                                        <?php foreach ($allTags as $tag): ?>
                                        <div class="col-6 col-md-4">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="dietary_tags[]"
                                                    value="<?= (int)$tag['tag_id'] ?>"
                                                    id="ptag_<?= (int)$tag['tag_id'] ?>"
                                                    <?= in_array($tag['tag_id'], $userTagIds)
                                                        ? 'checked' : '' ?>
                                                >
                                                <label class="form-check-label"
                                                       for="ptag_<?= (int)$tag['tag_id'] ?>">
                                                    <?= htmlspecialchars($tag['tag_name']) ?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit"
                                                class="btn btn-success">
                                            Save Preferences
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- end tab-content -->

            </div>
            <!-- end right column -->

        </div>
        <!-- end row -->

    </div>
</div>

<script>
// Toggle show/hide password
function togglePwd(fieldId) {
    const field = document.getElementById(fieldId);
    field.type  = field.type === 'password' ? 'text' : 'password';
}

// Password strength bar for new password
document.getElementById('new_password').addEventListener('input', function () {
    const val  = this.value;
    const bar  = document.getElementById('pwdStrengthBar');
    const text = document.getElementById('pwdStrengthText');
    let strength = 0;

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

// Password match checker
document.getElementById('confirm_new_password').addEventListener('input', function () {
    const newPwd  = document.getElementById('new_password').value;
    const confirm = this.value;
    const text    = document.getElementById('pwdMatchText');

    if (confirm === '') {
        text.textContent = '';
    } else if (newPwd === confirm) {
        text.textContent = 'Passwords match';
        text.className   = 'text-success small';
    } else {
        text.textContent = 'Passwords do not match';
        text.className   = 'text-danger small';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>