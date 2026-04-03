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
                    deleted_at,
                    TIMESTAMPDIFF(DAY, deleted_at, NOW()) AS days_since_deleted
                FROM users
                WHERE deleted_at IS NOT NULL
                ORDER BY deleted_at DESC
            ");

            $deletedUsers = $stmt->fetchAll();
        } catch (Throwable $e) {
            die('Failed to load deleted users: ' . $e->getMessage());
        }
        $title = "MealMate - Deleted Users";
        include_once '../../includes/header.php';
    ?>
</head>
<body>
    <?php include_once '../../includes/admin_nav.php'; ?>
    <main class="container-fluid px-3 py-4">
    <h1 class="mt-3">Deleted Users</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($deletedUsers)): ?>
        <p>No deleted users found.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" class="fw-bold text-nowrap">User ID</th>
                    <th scope="col" class="fw-bold text-nowrap">Username</th>
                    <th scope="col" class="fw-bold text-nowrap">Email</th>
                    <th scope="col" class="fw-bold text-nowrap">Role</th>
                    <th scope="col" class="fw-bold text-nowrap">Deleted At</th>
                    <th scope="col" class="fw-bold text-nowrap">Days Since Deleted</th>
                    <th scope="col" class="fw-bold text-nowrap">Restore</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deletedUsers as $user): ?>
                    <tr>
                        <td class="fw-bold text-nowrap"><?= htmlspecialchars($user['user_id']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['role']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['deleted_at']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($user['days_since_deleted']) ?></td>
                        <td>
                            <?php if ((int)$user['days_since_deleted'] < 30): ?>
                                <form class="mt-1 d-flex justify-content-center" action="../../actions/users/user-restore.php" method="POST">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                                    <button type="submit" class="btn btn-warning">Restore</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Restore expired</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    </main>
</body>
</html>