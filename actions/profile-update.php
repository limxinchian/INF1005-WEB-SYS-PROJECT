<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
}

if (!isLoggedIn()) {
    redirect('/INF1005-WEB-SYS-PROJECT/login.php');
}

verifyCsrfToken();


$userId = currentUserId();
$action = $_POST['action'] ?? '';

if ($action === 'update_info') {
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $avatarUrl = trim($_POST['avatar_url'] ?? '');

    if ($username === '' || $email === '') {
        setFlash('danger', 'Username and email are required.');
        redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('danger', 'Please enter a valid email address.');
        redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
    }

    if (mb_strlen($username) < 3 || mb_strlen($username) > 60) {
        setFlash('danger', 'Username must be between 3 and 60 characters.');
        redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
    }

    // Check for duplicate email (exclude current user)
    $dup = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $dup->execute([$email, $userId]);
    if ($dup->fetch()) {
        setFlash('danger', 'That email is already in use by another account.');
        redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
    }

    // Check for duplicate username (exclude current user)
    $dup2 = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $dup2->execute([$username, $userId]);
    if ($dup2->fetch()) {
        setFlash('danger', 'That username is already taken.');
        redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
    }

    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] == UPLOAD_ERR_OK) {
        $file     = $_FILES['avatar_file'];
        $maxSize  = 10 * 1024 * 1024; // 10 MB
        $allowed  = ['image/jpeg', 'image/png', 'image/gif'];

        // Verify MIME type using fileinfo
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowed, true)) {
            setFlash('danger', 'Avatar must be a JPG, PNG, or GIF image.');
            redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
        }

        if ($file['size'] > $maxSize) {
            setFlash('danger', 'Avatar file must be under 2 MB.');
            redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
        }

        // Build safe filename
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
        };
        $newName = 'avatar_' . $userId . '.' . $extension;

        // Ensure upload directory exists
        $uploadDir = __DIR__ . '/../assets/images/uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . '/' . $newName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            setFlash('danger', 'Failed to save avatar file. Please try again.');
            redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
        }

        // Delete old uploaded avatar if it exists
        $oldStmt = $pdo->prepare("SELECT avatar_url FROM users WHERE user_id = ?");
        $oldStmt->execute([$userId]);
        $oldAvatar = $oldStmt->fetchColumn();
        if ($oldAvatar && str_contains($oldAvatar, '/assets/images/uploads/avatars/')) {
            $oldFile = __DIR__ . '/../' . ltrim(str_replace('/INF1005-WEB-SYS-PROJECT/', '', $oldAvatar), '/');
            if (is_file($oldFile)) {
                unlink($oldFile);
            }
        }

        $avatarUrl = '/INF1005-WEB-SYS-PROJECT/assets/images/uploads/avatars/' . $newName;

        $storage = new StorageClient();
        if (!$file = fopen(__DIR__ . "/../assets/images/uploads/avatars/" . $newName, 'r')) {
            throw new \InvalidArgumentException('Unable to open file for reading');
        }
        $bucket = $storage->bucket("mealmate_profile_pictures");
        $object = $bucket->upload($file, [
        'name' => 'avatar_' . $userId
        ]);
    }else{
        // No new file uploaded
        if (!isset($_POST['avatar_url'])) {
        // No URL provided either — check if one exists in DB
            $existingStmt = $pdo->prepare("SELECT avatar_url FROM users WHERE user_id = ?");
            $existingStmt->execute([$userId]);
            $existingAvatar = $existingStmt->fetchColumn();

            if ($existingAvatar) {
                // Keep the existing avatar
                $avatarUrl = $existingAvatar;
            } else {
                // No avatar anywhere — ensure it's removed
                $avatarUrl = '';
            }
        }
    }

    // Update database
    $stmt = $pdo->prepare("
        UPDATE users
        SET username   = ?,
            email      = ?,
            avatar_url = ?
        WHERE user_id  = ?
    ");
    $stmt->execute([$username, $email, $avatarUrl ?: null, $userId]);

    // Update session username in case it changed
    $_SESSION['username'] = $username;

    setFlash('success', 'Profile updated successfully.');
    redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
}

// ============================================================
// ACTION: change_password
// ============================================================
if ($action === 'change_password') {

    $currentPwd = $_POST['current_password'] ?? '';
    $newPwd     = $_POST['new_password'] ?? '';
    $confirmPwd = $_POST['confirm_new_password'] ?? '';

    if ($currentPwd === '' || $newPwd === '' || $confirmPwd === '') {
        setFlash('danger', 'All password fields are required.');
        redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
    }

    if ($newPwd !== $confirmPwd) {
        setFlash('danger', 'New passwords do not match.');
        redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
    }

    if (mb_strlen($newPwd) < 8) {
        setFlash('danger', 'New password must be at least 8 characters.');
        redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
    }

    // Fetch current hash
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($currentPwd, $hash)) {
        setFlash('danger', 'Current password is incorrect.');
        redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
    }

    // Update to new password
    $newHash = password_hash($newPwd, PASSWORD_DEFAULT);
    $update  = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $update->execute([$newHash, $userId]);

    setFlash('success', 'Password changed successfully.');
    redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
}

// ============================================================
// ACTION: update_dietary
// ============================================================
if ($action === 'update_dietary') {

    $tags = $_POST['dietary_tags'] ?? [];
    $tags = array_values(
        array_filter(
            array_map('intval', (array) $tags),
            fn($id) => $id > 0
        )
    );

    // Validate tag IDs against actual tags in DB
    $validIds = [];
    if (!empty($tags)) {
        $placeholders = implode(',', array_fill(0, count($tags), '?'));
        $check = $pdo->prepare(
            "SELECT tag_id FROM dietary_tags WHERE tag_id IN ($placeholders)"
        );
        $check->execute($tags);
        $validIds = array_column($check->fetchAll(), 'tag_id');
        $validIds = array_map('intval', $validIds);
    }

    // Replace preferences in a transaction
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM user_dietary_preferences WHERE user_id = ?")
        ->execute([$userId]);

    if (!empty($validIds)) {
        $ins = $pdo->prepare(
            "INSERT INTO user_dietary_preferences (user_id, tag_id) VALUES (?, ?)"
        );
        foreach ($validIds as $tagId) {
            $ins->execute([$userId, $tagId]);
        }
    }
    $pdo->commit();

    setFlash('success', 'Dietary preferences saved.');
    redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
}

// Unknown action
setFlash('danger', 'Invalid action.');
redirect('/INF1005-WEB-SYS-PROJECT/edit_profile.php');
