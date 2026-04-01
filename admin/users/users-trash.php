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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        table {
            border-collapse: collapse;
            margin-top: 16px;
            width: 100%;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }

        .top-links {
            margin-bottom: 16px;
        }

        .top-links a {
            margin-right: 12px;
        }

        .message {
            color: green;
            font-weight: bold;
            margin: 12px 0;
        }

        .actions form {
            display: inline-block;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="top-links">
        <a href="../dashboard.php">Back to Dashboard</a>
        <a href="users.php">Back to All Users</a>
    </div>

    <h1>Deleted Users</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($deletedUsers)): ?>
        <p>No deleted users found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Deleted At</th>
                    <th>Days Since Deleted</th>
                    <th>Restore</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deletedUsers as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= htmlspecialchars($user['deleted_at']) ?></td>
                        <td><?= htmlspecialchars($user['days_since_deleted']) ?></td>
                        <td>
                            <?php if ((int)$user['days_since_deleted'] < 30): ?>
                                <form action="../../actions/users/user-restore.php" method="POST">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                                    <button type="submit">Restore</button>
                                </form>
                            <?php else: ?>
                                <span>Restore expired</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>