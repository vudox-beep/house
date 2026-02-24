<?php
require_once 'config/config.php';
require_once 'models/User.php';

if (isset($_GET['code'])) {
    $token_url = 'https://oauth2.googleapis.com/token';
    $data = [
        'code' => $_GET['code'],
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URL,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (isset($token_data['access_token'])) {
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token_data['access_token'];
        $user_info = json_decode(file_get_contents($user_info_url), true);

        if (isset($user_info['email'])) {
            $userModel = new User();
            // Ensure method exists or fallback
            if (method_exists($userModel, 'loginWithGoogle')) {
                $user = $userModel->loginWithGoogle($user_info);
            } else {
                // Fallback to manual check if method missing (should not happen if User.php is updated)
                // This block is a safeguard.
                $user = false; 
            }

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                
                // Redirect based on role
                if ($user['role'] == 'dealer') {
                    header("Location: dealer/dashboard.php");
                } elseif ($user['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                header("Location: login.php?error=" . urlencode("Failed to login with Google."));
            }
        }
    }
}

header("Location: login.php?error=" . urlencode("Google login failed."));
exit;
?>