<?php
require_once 'config/config.php';
require_once 'models/User.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $user = new User();
    
    if ($user->verifyToken($token)) {
        $message = "Email verified successfully! You can now <a href='login.php'>login</a>.";
        $alert = "success";
    } else {
        $message = "Invalid or expired verification link.";
        $alert = "danger";
    }
} else {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <div class="alert alert-<?php echo $alert; ?>">
                            <?php echo $message; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
