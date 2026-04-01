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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users</title>
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

        th,
        td {
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
    <a href="../dashboard.php">Back to Dashboard</a>
    <a href="users-trash.php"> View Deleted Users</a>

    <h1>All Users</h1>

    <?php if (isset($_GET['message'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Avatar URL</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= htmlspecialchars($user['avatar_url'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td><?= htmlspecialchars($user['updated_at']) ?></td>
                        <td class="actions">
                            <a href="user-edit.php?user_id=<?= urlencode($user['user_id']) ?>">Edit</a>

                            <form action="../../actions/users/user-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to move this user to trash?');">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>