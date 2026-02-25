<?php
require_once 'config/config.php';
require_once 'models/User.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $user = new User();
    
    if ($user->verifyToken($token)) {
        $message = "Email verified successfully!";
        $alert = "success";
        $btn_text = "Login Now";
        $btn_link = "login.php";
    } else {
        $message = "Invalid or expired verification link.";
        $alert = "danger";
        $btn_text = "Go Home";
        $btn_link = "index.php";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <?php if($alert == 'success'): ?>
                                <div class="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="bi bi-check-lg display-4"></i>
                                </div>
                            <?php else: ?>
                                <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="bi bi-x-lg display-4"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="fw-bold mb-3"><?php echo $alert == 'success' ? 'Verified!' : 'Verification Failed'; ?></h3>
                        <p class="text-muted mb-4"><?php echo $message; ?></p>
                        
                        <a href="<?php echo $btn_link; ?>" class="btn btn-primary w-100 py-2 fw-bold"><?php echo $btn_text; ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
