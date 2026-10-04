<?php
session_start();
include 'common/config.php';

/*
|--------------------------------------------------------------------------
| CODE TOP UP - Google Login
|--------------------------------------------------------------------------
| এখানে আপনার Google OAuth Client ID বসাবেন।
| Client Secret এই ফাইলে রাখবেন না।
|--------------------------------------------------------------------------
*/

$client_id = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';

$redirect_uri = 'https://YOUR-DOMAIN.com/google-callback.php';

if ($client_id === 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com') {
    die('
        <div style="
            font-family:Arial;
            max-width:500px;
            margin:80px auto;
            padding:25px;
            text-align:center;
            border:1px solid #ddd;
            border-radius:15px;
        ">
            <h2>CODE TOP UP</h2>
            <p>Google Login এখনো সেটআপ করা হয়নি।</p>
            <p>Google OAuth Client ID বসিয়ে আবার চেষ্টা করুন।</p>
        </div>
    ');
}

$params = [
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'offline',
    'prompt' => 'select_account'
];

$url = 'https://accounts.google.com/o/oauth2/v2/auth?' .
       http_build_query($params);

header('Location: ' . $url);
exit;
?>
