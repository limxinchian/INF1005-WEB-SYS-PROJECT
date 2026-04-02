<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            die('User ID is required.');
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $role = trim($_POST['role'] ?? '');
                $avatarUrl = trim($_POST['avatar_url'] ?? '');

                if ($username === '' || $email === '' || !in_array($role, ['admin', 'member'], true)) {
                    die('Invalid form input.');
                }

                $updateStmt = $pdo->prepare("
                    UPDATE users
                    SET
                        username = ?,
                        email = ?,
                        role = ?,
                        avatar_url = ?,
                        updated_at = NOW()
                    WHERE user_id = ?
                    AND deleted_at IS NULL
                ");

                $updateStmt->execute([
                    $username,
                    $email,
                    $role,
                    $avatarUrl !== '' ? $avatarUrl : null,
                    $userId
                ]);

                header('Location: users.php?message=' . urlencode('User updated successfully.'));
                exit();
            }

            $stmt = $pdo->prepare("
                SELECT *
                FROM users
                WHERE user_id = ?
                AND deleted_at IS NULL
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                die('User not found.');
            }
        } catch (Throwable $e) {
            die('Failed to load user: ' . $e->getMessage());
        }

        $title = "MealMate - Edit User";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <div class="container-fluid mt-3">
        <h1>Edit User</h1>

        <form method="POST" class="d-flex flex-column pe-lg-20">
            <label for="username" class="form-label mt-3 fs-large">Username</label>
            <input class="form-control" type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <label for="email" class="form-label mt-3 fs-large">Email</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label for="role" class="form-label mt-3 fs-large">Role</label>
            <select id="role" name="role" class="form-select" required>
                <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>

            <label for="avatar_url" class="form-label mt-3 fs-large">Avatar URL</label>
            <input type="text" id="avatar_url" name="avatar_url" class="form-control" value="<?= htmlspecialchars($user['avatar_url'] ?? '') ?>">

            <div class="actions mt-3 d-flex justify-content-end gap-2">
                <a href="users.php" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>
</html>