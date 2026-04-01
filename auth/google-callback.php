<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../vendor/autoload.php';

$env = parse_ini_file(__DIR__ . '/../.env');

if (isset($_GET['error'])) {
    setFlash('danger', 'Google login was cancelled or failed.');
    redirect('/INF1005-WEB-SYS-PROJECT/login.php');
}

if (!isset($_GET['code'])) {
    setFlash('danger', 'No authorization code received.');
    redirect('/INF1005-WEB-SYS-PROJECT/login.php');
}

$client = new Google\Client();
$client->setClientId($env['GOOGLE_CLIENT_ID']);
$client->setClientSecret($env['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri('http://localhost/INF1005-WEB-SYS-PROJECT/auth/google-callback.php');

try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
} catch (Exception $e) {
    error_log('Google OAuth error: ' . $e->getMessage());
    setFlash('danger', 'Failed to authenticate with Google. Please try again.');
    redirect('/INF1005-WEB-SYS-PROJECT/login.php');
}

if (isset($token['error'])) {
    error_log('Google OAuth token error: ' . $token['error']);
    setFlash('danger', 'Failed to authenticate with Google. Please try again.');
    redirect('/INF1005-WEB-SYS-PROJECT/login.php');
}

$client->setAccessToken($token);

$oauth = new Google\Service\Oauth2($client);
$googleUser = $oauth->userinfo->get();

$email  = $googleUser->email;
$name   = $googleUser->name ?? explode('@', $email)[0];
$avatar = $googleUser->picture ?? null;
$oauth_id = $googleUser->id;

if (empty($email)) {
    redirect('/INF1005-WEB-SYS-PROJECT/login.php');
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE oauth_id = ? AND deleted_at IS NULL');
$stmt->execute([$oauth_id]);
$user = $stmt->fetch();

if (!$user) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND deleted_at IS NULL');
    $stmt->execute([$email]);
    $existingEmailUser = $stmt->fetch();
    if ($existingEmailUser) {
        setFlash('warning', 'An account with that email already exists. Please log in using your email and password.');
        redirect('/INF1005-WEB-SYS-PROJECT/login.php');
    }else{
        $username = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);

        $randomHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);

        $stmt = $pdo->prepare('
            INSERT INTO users (username, email, password_hash, role, avatar_url, oauth_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$username, $email, $randomHash, 'member', $avatar, $oauth_id]);
        $newUserId = (int) $pdo->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['user_id']  = $newUserId;
        $_SESSION['username'] = $username;
        $_SESSION['role']     = 'member';
    }
}else{
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int) $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
}


    if ($_SESSION['role'] === 'admin') {
        redirect('/INF1005-WEB-SYS-PROJECT/admin/dashboard.php');
    } else {
        redirect('/INF1005-WEB-SYS-PROJECT/index.php');
    }


?>