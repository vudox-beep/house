<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../includes/ActivityLogger.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $url = 'https://oauth2.googleapis.com/token';
    
    $params = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URL,
        'grant_type' => 'authorization_code',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost dev only
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);

    if (isset($tokenData['access_token'])) {
        // Get User Info
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $tokenData['access_token'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $userInfo = curl_exec($ch);
        curl_close($ch);
        
        $googleUser = json_decode($userInfo, true);
        
        if (isset($googleUser['email'])) {
            $userModel = new User();
            
            // Check Action Intent (set in google_login.php)
            $authAction = $_SESSION['auth_action'] ?? 'login';
            
            // First check if user exists by email
            if ($userModel->emailExists($googleUser['email'])) {
                // USER EXISTS
                
                if ($authAction === 'signup') {
                    // User clicked "Sign Up" but account exists -> ERROR
                    header("Location: ../login.php?error=" . urlencode("An account with this email already exists. Please log in."));
                    exit;
                } else {
                    // User clicked "Login" and account exists -> LOGIN SUCCESS
                    $user = $userModel->loginWithGoogle($googleUser);
                }
                
            } else {
                // NEW USER
                
                if ($authAction === 'login') {
                    // User clicked "Login" but no account found -> ERROR (or redirect to signup)
                    // User requested: "if account doesnt eit say no rocord found register first"
                    header("Location: ../register.php?error=" . urlencode("No record found. Please register first."));
                    exit;
                } else {
                    // User clicked "Sign Up" and no account found -> SIGNUP SUCCESS
                    $_SESSION['google_signup_data'] = $googleUser;
                    header("Location: complete_profile.php");
                    exit;
                }
            }
            
            if ($user) {
                // Check if banned
                if ($user['is_banned'] == 1) {
                    echo "Your account has been banned. Please contact support. <a href='../login.php'>Back to Login</a>";
                    exit;
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                
                // Log Login
                $logger = new ActivityLogger();
                $logger->log($user['id'], $user['role'], 'login', 'User logged in via Google');

                if ($user['role'] == 'dealer') {
                    header("Location: ../dealer/dashboard.php");
                } elseif ($user['role'] == 'admin') {
                    header("Location: ../admin/dashboard.php");
                } elseif ($user['role'] == 'user') {
                    // Redirect Tenants/Users to Tenant Dashboard
                    header("Location: ../tenant/dashboard.php");
                } else {
                    header("Location: ../index.php");
                }
                exit;
            }
        }
    }
}

echo "Google Login Failed. <a href='../login.php'>Try Again</a>";
?>
