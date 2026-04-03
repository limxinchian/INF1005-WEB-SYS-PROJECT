<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../includes/admin-guard.php';

        try {
            $stmt = $pdo->query("
                SELECT
                    user_id,
                    username,
                    email,
                    role,
                    avatar_url,
                    created_at,
                    updated_at
                FROM users
                WHERE deleted_at IS NULL
                ORDER BY created_at DESC
            ");

            $users = $stmt->fetchAll();
        } catch (Throwable $e) {
            die('Failed to load users: ' . $e->getMessage());
        }

        $title = "MealMate - All Users";
        include_once '../../includes/header.php';
    ?>
</head>

<body>
    <?php include_once '../../includes/admin_nav.php'; ?>

    <main class="container-fluid px-3 py-4">
    <h1 class="mt-3">All Users</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" class="fw-bold text-nowrap">User ID</th>
                    <th scope="col" class="fw-bold text-nowrap">Username</th>
                    <th scope="col" class="fw-bold text-nowrap">Email</th>
                    <th scope="col" class="fw-bold text-nowrap">Role</th>
                    <th scope="col" class="fw-bold text-wrap">Avatar URL</th>
                    <th scope="col" class="fw-bold text-nowrap">Created At</th>
                    <th scope="col" class="fw-bold text-nowrap">Updated At</th>
                    <th scope="col" class="fw-bold text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="fw-bold text-nowrap"><?= htmlspecialchars($user['user_id']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['role']) ?></td>
                        <td class="text-wrap"><?= htmlspecialchars($user['avatar_url'] ?? '') ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['created_at']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['updated_at']) ?></td>
                        <td class="actions">
                            <a href="user-edit.php?user_id=<?= urlencode($user['user_id']) ?>" class="btn btn-warning w-100">Edit</a>

                            <form class="mt-1" action="../../actions/users/user-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to move this user to trash?');">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                                <button type="submit" class="btn btn-danger w-100">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    </main>
</body>

</html>