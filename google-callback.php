<?php
session_start();
include 'common/config.php';

/*
|--------------------------------------------------------------------------
| CODE TOP UP - Google OAuth Callback
|--------------------------------------------------------------------------
*/

$client_id = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';
$client_secret = 'YOUR_GOOGLE_CLIENT_SECRET';

$redirect_uri = 'https://YOUR-DOMAIN.com/google-callback.php';

if (!isset($_GET['code'])) {
    die('Google Login failed: Authorization code missing.');
}

$code = $_GET['code'];

/*
|--------------------------------------------------------------------------
| Exchange authorization code for access token
|--------------------------------------------------------------------------
*/

$postData = [
    'code' => $code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code'
];

$ch = curl_init('https://oauth2.googleapis.com/token');

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);

if (
    !$tokenData ||
    !isset($tokenData['access_token'])
) {
    die('Google Login failed: Could not get access token.');
}

$access_token = $tokenData['access_token'];

/*
|--------------------------------------------------------------------------
| Get Google User Information
|--------------------------------------------------------------------------
*/

$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token
]);

$userResponse = curl_exec($ch);
curl_close($ch);

$googleUser = json_decode($userResponse, true);

if (
    !$googleUser ||
    !isset($googleUser['email'])
) {
    die('Google Login failed: Could not get user information.');
}

$name = $googleUser['name'] ?? '';
$email = $googleUser['email'] ?? '';

if ($email === '') {
    die('Google account email not found.');
}

/*
|--------------------------------------------------------------------------
| Find existing user
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT * FROM users WHERE email = ? LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Existing User
|--------------------------------------------------------------------------
*/

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'] ?? $name;
    $_SESSION['user_email'] = $user['email'];

    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Create New User
|--------------------------------------------------------------------------
*/

$password = password_hash(
    bin2hex(random_bytes(16)),
    PASSWORD_DEFAULT
);

$phone = '';

$stmt = $conn->prepare(
    "INSERT INTO users (name, phone, email, password)
     VALUES (?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssss",
    $name,
    $phone,
    $email,
    $password
);

if (!$stmt->execute()) {
    die('Could not create CODE TOP UP account.');
}

/*
|--------------------------------------------------------------------------
| Login newly created user
|--------------------------------------------------------------------------
*/

$_SESSION['user_id'] = $conn->insert_id;
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email;

header('Location: index.php');
exit;
?>
