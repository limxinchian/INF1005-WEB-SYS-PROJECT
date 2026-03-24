<?php
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../includes/admin-guard.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        form {
            max-width: 700px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        .actions {
            margin-top: 20px;
        }

        .actions button,
        .actions a {
            margin-right: 12px;
        }
    </style>
</head>
<body>
    <h1>Edit User</h1>

    <p>
        <a href="users.php">Back to All Users</a>
    </p>

    <form method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>>Member</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>

        <label for="avatar_url">Avatar URL</label>
        <input type="text" id="avatar_url" name="avatar_url" value="<?= htmlspecialchars($user['avatar_url'] ?? '') ?>">

        <div class="actions">
            <button type="submit">Save Changes</button>
            <a href="users.php">Cancel</a>
        </div>
    </form>
</body>
</html>