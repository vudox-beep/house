<?php
require_once '../config/config.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

$userModel = new User();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // $userModel = new User(); // Removed redundant init
    
    // Update basic info
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $whatsapp = htmlspecialchars($_POST['whatsapp']);
    
    // Handle Profile Picture Upload
    $profile_image = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $upload_path = '../assets/images/users/' . $new_name;
            
            // Create directory if not exists
            if (!file_exists('../assets/images/users/')) {
                mkdir('../assets/images/users/', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                $profile_image = 'assets/images/users/' . $new_name;
                // Update session
                $_SESSION['profile_image'] = $profile_image;
            }
        } else {
            $error = "Invalid image format. Allowed: JPG, PNG, WEBP.";
        }
    }

    // Update Password
    if (!empty($_POST['password'])) {
        if ($_POST['password'] === $_POST['confirm_password']) {
            $userModel->updatePassword($_SESSION['user_id'], $_POST['password']);
            $success = "Password updated successfully.";
        } else {
            $error = "Passwords do not match.";
        }
    } 
    
    if (empty($error)) {
        // Update Profile Info
        if ($userModel->updateProfile($_SESSION['user_id'], $name, $phone, $whatsapp, $profile_image)) {
            // Update session name if changed
            $_SESSION['user_name'] = $name;
            $success = $success ? $success . " Profile updated." : "Profile updated successfully.";
        } else {
            $error = "Failed to update profile.";
        }
    }
}

// Fetch latest user data
$currentUser = $userModel->getUserById($_SESSION['user_id']);
$profilePic = !empty($currentUser['profile_image']) && file_exists('../' . $currentUser['profile_image']) 
    ? '../' . $currentUser['profile_image'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($currentUser['name']) . '&background=random&size=256';
?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-white border-0 py-3">
                    <h4 class="mb-0 fw-bold">My Profile</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <img src="<?php echo $profilePic; ?>" class="rounded-circle mb-3" width="100" height="100" alt="Profile" style="object-fit: cover;">
                            <div>
                                <label for="profile_pic" class="btn btn-sm btn-outline-primary">Change Photo</label>
                                <input type="file" id="profile_pic" name="profile_pic" class="d-none" onchange="document.querySelector('.rounded-circle').src = window.URL.createObjectURL(this.files[0])">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($currentUser['email']); ?>" disabled>
                            <small class="text-muted">Email cannot be changed.</small>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="+260..." value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="whatsapp" class="form-label fw-bold">WhatsApp Number</label>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp" placeholder="+260..." value="<?php echo htmlspecialchars($currentUser['whatsapp_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3">Change Password</h5>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>