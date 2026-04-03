<!DOCTYPE html>
<html lang="en">
<head>
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
            SELECT user_id, username, email, role, avatar_url, created_at, oauth_id
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
        <link rel="stylesheet" href="assets/css/edit_profile.css">
        <script src="assets/js/edit_profile.js" defer></script>
</head>
<body>
    <?php require_once 'includes/nav.php'; ?>
    <main class="container">
        <div class="page-header mt-3 d-flex flex-row justify-content-between align-items-center">
                    <h1>Edit Profile</h1>
                    <a href="profile.php" class="btn btn-primary overpass-mono-normal">View Profile</a>
                </div>

                <div class="row g-4">
                    <!-- LEFT COLUMN — Avatar + Stats -->
                    <div class="col-md-4">

                        <!-- Avatar Card -->
                        <div class="card border-0 shadow-sm text-center p-4 mb-4">
                            <div class="mb-3">
                                <?php if (!empty($user['avatar_url'])): ?>
                                    <img src="<?= htmlspecialchars(str_replace('=s96-c', '=s164-c', $user['avatar_url'])) ?>"
                                            alt="Avatar"
                                            class="rounded-circle profile_avatar"
                                            onerror="this.classList.add('d-none');var p=this.nextElementSibling;p.classList.remove('d-none');p.classList.add('d-inline-flex');">
                                    <div class="rounded-circle bg-success text-white
                                                align-items-center justify-content-center profile_avatar_placeholder d-none">
                                        <span><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="rounded-circle bg-success text-white d-inline-flex
                                                align-items-center justify-content-center profile_avatar_placeholder">
                                        <span><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
                                    </div>
                                <?php endif; ?>
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
                                    Account Info
                                </a>
                            </li>
                            <?php if($user['oauth_id'] == null or $user['oauth_id'] == ''){?>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab"
                                    href="#tab-password">
                                        Change Password
                                    </a>
                                </li>
                            <?php } ?>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab"
                                href="#tab-dietary">
                                    Dietary Preferences
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
                                            enctype="multipart/form-data"
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

                                            <!-- Avatar Upload -->
                                            <div class="mb-3">
                                                <label for="avatar_file"
                                                    class="form-label fw-semibold">
                                                    Upload Avatar
                                                    <span class="text-muted fw-normal">
                                                        (optional — JPG, PNG, GIF, max 2 MB)
                                                    </span>
                                                </label>
                                                <input
                                                    type="file"
                                                    class="form-control"
                                                    id="avatar_file"
                                                    name="avatar_file"
                                                    accept="image/jpeg,image/png,image/gif"
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
                            
                            <?php if($user['oauth_id'] == null or $user['oauth_id'] == ''){?>
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
                                                        <img src="assets/images/icons/visibility.svg"
                                                            alt="Toggle password visibility">
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
                                                        <img src="assets/images/icons/visibility.svg"
                                                            alt="Toggle password visibility">
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
                                                        <img src="assets/images/icons/visibility.svg"
                                                            alt="Toggle password visibility">
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
                            <?php } ?>
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
    </main>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
