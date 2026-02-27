<?php
require_once '../config/config.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

$userModel = new User();
$error = '';
$success = '';

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Update basic info
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $whatsapp = htmlspecialchars($_POST['whatsapp']);
    
    $profile_image_path = null;

    // Handle Profile Picture Upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Generate unique name
            $new_name = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            // Define upload path (relative to root)
            $upload_dir = '../assets/images/users/';
            $upload_path = $upload_dir . $new_name;
            
            // Create directory if not exists
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                // Save relative path for DB
                $profile_image_path = 'assets/images/users/' . $new_name;
                // Update session
                $_SESSION['profile_image'] = $profile_image_path;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image format. Allowed: JPG, PNG, WEBP.";
        }
    }

    // Update Password logic
    $password_updated = false;
    if (!empty($_POST['password'])) {
        if ($_POST['password'] === $_POST['confirm_password']) {
            if ($userModel->updatePassword($_SESSION['user_id'], $_POST['password'])) {
                $password_updated = true;
            } else {
                $error = "Failed to update password.";
            }
        } else {
            $error = "Passwords do not match.";
        }
    } 
    
    if (empty($error)) {
        // Update Profile Info in DB
        // We need to fetch current image if new one is not uploaded to avoid overwriting with null if the model expects it
        // But our User model updateProfile method usually handles "if null, keep old".
        // Let's check User.php later. For now assume passing null means no change or we handle it.
        // Actually, looking at typical implementations, we should probably fetch old image if new one is null.
        
        $currentUser = $userModel->getUserById($_SESSION['user_id']);
        if (!$profile_image_path) {
            $profile_image_path = $currentUser['profile_image'];
        }

        if ($userModel->updateProfile($_SESSION['user_id'], $name, $phone, $whatsapp, $profile_image_path)) {
            // Update session name if changed
            $_SESSION['user_name'] = $name;
            $success = "Profile updated successfully.";
            if ($password_updated) {
                $success .= " Password changed.";
            }
        } else {
            $error = "Failed to update profile details.";
        }
    }
}

// Fetch latest user data
$currentUser = $userModel->getUserById($_SESSION['user_id']);

// Determine profile picture URL
$profilePic = !empty($currentUser['profile_image']) && file_exists('../' . $currentUser['profile_image']) 
    ? '../' . $currentUser['profile_image'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($currentUser['name']) . '&background=random&size=256';

?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-white border-0 py-3">
                    <h4 class="mb-0 fw-bold">My Profile</h4>
                </div>
                <div class="card-body p-4">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- Profile Image Section -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="<?php echo $profilePic; ?>" class="rounded-circle mb-3 border" width="120" height="120" alt="Profile" style="object-fit: cover;" id="previewImg">
                                <label for="profile_pic" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 32px;">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                            <input type="file" id="profile_pic" name="profile_pic" class="d-none" onchange="previewFile()">
                            <div class="small text-muted">Click camera icon to change</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold small">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold small">Email Address</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($currentUser['email']); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-bold small">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="+260..." value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="whatsapp" class="form-label fw-bold small">WhatsApp Number</label>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp" placeholder="+260..." value="<?php echo htmlspecialchars($currentUser['whatsapp_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3">Change Password</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-bold small">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current">
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-bold small">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewFile() {
    const preview = document.getElementById('previewImg');
    const file = document.querySelector('input[type=file]').files[0];
    const reader = new FileReader();

    reader.addEventListener("load", function () {
        preview.src = reader.result;
    }, false);

    if (file) {
        reader.readAsDataURL(file);
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>