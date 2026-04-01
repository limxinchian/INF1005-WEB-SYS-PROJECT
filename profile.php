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
        <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>
    <?php require_once 'includes/nav.php'; ?>
    <main>
        <div class="container w-100">
            <div class="page-header mt-3 d-flex flex-row justify-content-between align-items-center">
                <h1>Profile</h1>
                <a href="edit_profile.php" class="btn btn-primary overpass-mono-normal">Edit Profile</a>
            </div>
            <div class="container d-flex flex-column flex-lg-row gap-4 mt-4">
                <div class="col-auto">
                    <div class="profile_image col-md-4">
                        <div class="container text-center p-2 mb-4">
                            <?php if (!empty($user['avatar_url'])): ?>
                                <img src="<?= htmlspecialchars(str_replace('=s96-c', '=s164-c', $user['avatar_url'])) ?>"
                                        alt="Avatar"
                                        class="rounded-circle profile_avatar"
                                        onerror="this.style.display='none';this.nextElementSibling.style.display='';">
                                <div class="rounded-circle bg-success text-white d-inline-flex
                                            align-items-center justify-content-center profile_avatar_placeholder" style="display:none;">
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
                <div class="flex-grow-1">
                    <div class="row align-items-center mb-3">
                        <div class="col">
                            <h3>Profile Information</h3>
                            <p class="overpass-mono-normal my-1"><b>Username:</b> <?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="overpass-mono-normal my-1"><b>Email:</b> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="overpass-mono-normal my-1"><b>Member Since:</b> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <div class="col">
                            <h3>Dietary Preferences</h3>
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
                                            disabled
                                        >
                                        <label class="form-check-label text-black overpass-mono-normal"
                                                for="ptag_<?= (int)$tag['tag_id'] ?>">
                                            <?= htmlspecialchars($tag['tag_name']) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </form>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <div class="col">
                            <h3>My Stats</h3>
                            <div class="card border-0 shadow-sm p-4">
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
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
