<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

// Check Subscription & Verification
$userModel = new User();
$dealerProfile = $userModel->getDealerProfile($_SESSION['user_id']);
$userProfile = $userModel->getUserById($_SESSION['user_id']); // Get generic user data for verification status

// Check Identity Verification First (Blocking Screen)
if ($userProfile['identity_verified'] != 1) {
    // Show same blocking screen as add_property.php
    
    // Check if doc uploaded
    $upload_success = '';
    $upload_error = '';
    $is_pending = false;
    
    // Check if verification_doc is not null
    // But if status is 2 (Rejected), we should treat it as rejected unless they just re-uploaded
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dealer_verification_doc'])) {
        // ... Upload Logic ...
        $target_dir = "../assets/images/dealer_docs/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES["dealer_verification_doc"]["name"], PATHINFO_EXTENSION));
        $new_filename = 'dealer_' . $_SESSION['user_id'] . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($file_extension, $allowed)) {
            $upload_error = "Only JPG, PNG & PDF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["dealer_verification_doc"]["tmp_name"], $target_file)) {
                require_once '../includes/SimpleMailer.php';
                $mailer = new SimpleMailer();
                $subject = "Dealer Verification Request - " . $_SESSION['user_name'];
                $body = "User " . $_SESSION['user_name'] . " (ID: " . $_SESSION['user_id'] . ") has uploaded a verification document.<br>File: " . SITE_URL . "/assets/images/dealer_docs/" . $new_filename;
                $mailer->send(SMTP_FROM, $subject, $body);
                
                try {
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    // Reset to 0 (Pending)
                    $stmt = $pdo->prepare("UPDATE users SET verification_doc = :doc, identity_verified = 0 WHERE id = :id");
                    $stmt->execute([':doc' => "assets/images/dealer_docs/" . $new_filename, ':id' => $_SESSION['user_id']]);
                    
                    // Update local variable to reflect change immediately
                    $userProfile['identity_verified'] = 0; 
                } catch (Exception $e) {}
                
                $upload_success = "Document uploaded successfully.";
                $is_pending = true;
            } else {
                $upload_error = "Failed to upload file.";
            }
        }
    }
    
    // Determine display state
    $is_rejected = ($userProfile['identity_verified'] == 2);
    // If we just uploaded successfully, it is pending, not rejected (locally)
    if ($upload_success) {
        $is_rejected = false;
        $is_pending = true;
    } elseif ($userProfile['identity_verified'] == 0 && !empty($userProfile['verification_doc'])) {
        $is_pending = true;
    }

    echo "
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card border-danger shadow-lg'>
                    <div class='card-header bg-danger text-white py-3'>
                        <h4 class='mb-0 fw-bold'><i class='bi bi-shield-lock-fill me-2'></i>Account Verification Required</h4>
                    </div>
                    <div class='card-body p-5 text-center'>
                        <div class='mb-4'>
                            <i class='bi bi-person-badge display-1 text-danger'></i>
                        </div>
                        <h3 class='fw-bold mb-3'>Verify Identity to Manage Properties</h3>
                        <p class='lead mb-4'>You cannot view or manage properties until your identity is verified.</p>
                        
                        " . ($is_rejected ? "
                            <div class='alert alert-danger border-danger text-start p-4 mb-4'>
                                <h4 class='alert-heading fw-bold'><i class='bi bi-x-circle-fill'></i> Verification Rejected</h4>
                                <p class='mb-0 lead'>Your previous verification attempt was rejected. Please upload a new photo.</p>
                            </div>
                        " : "") . "

                        " . ($is_pending ? "
                             <div class='alert alert-info border-info text-start p-4'>
                                <h4 class='alert-heading fw-bold'><i class='bi bi-clock-history'></i> Verification Pending</h4>
                                <p class='mb-0 lead'>Your verification photo is under review. Please wait for approval.</p>
                            </div>
                        " : "
                            " . (!$is_rejected ? "
                            <div class='alert alert-warning border-warning text-start'>
                                <h5 class='alert-heading fw-bold'><i class='bi bi-exclamation-triangle-fill'></i> Action Required:</h5>
                                <p class='mb-0'>Please upload a photo of <strong>yourself standing next to your property</strong> to proceed.</p>
                            </div>" : "") . "
                            
                            " . ($upload_error ? "<div class='alert alert-danger'>$upload_error</div>" : "") . "
                            
                            <form method='POST' enctype='multipart/form-data' class='mt-4 p-4 border rounded bg-light'>
                                <div class='mb-3 text-start'>
                                    <label class='form-label fw-bold'>Upload Verification Photo</label>
                                    <input type='file' class='form-control' name='dealer_verification_doc' required accept='.jpg,.jpeg,.png,.pdf'>
                                    <div class='form-text'>Photo of you + property. Formats: JPG, PNG</div>
                                </div>
                                <button type='submit' class='btn btn-danger w-100 fw-bold'>Submit Verification Photo</button>
                            </form>
                        ") . "
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    exit;
}

$canAddProperty = false;
if ($dealerProfile && $dealerProfile['subscription_status'] === 'active') {
    if (!empty($dealerProfile['subscription_expiry'])) {
        $expiryDate = new DateTime($dealerProfile['subscription_expiry']);
        $now = new DateTime();
        if ($expiryDate > $now) {
            $canAddProperty = true;
        }
    } else {
        $canAddProperty = true;
    }
}

$dealer_id = $_SESSION['user_id'];
$propertyModel = new Property();
$properties = $propertyModel->getByDealer($dealer_id);
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">My Properties</h4>
        <?php if ($canAddProperty): ?>
            <a href="add_property.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add New Property</a>
        <?php else: ?>
            <button class="btn btn-secondary" disabled title="Subscription Expired"><i class="bi bi-lock-fill"></i> Add New Property</button>
        <?php endif; ?>
    </div>

    <!-- Properties Table -->
    <?php if (!$canAddProperty): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
            <div>
                <strong>Subscription Expired</strong><br>
                You can view your properties, but you cannot edit or add new ones. <a href="subscribe.php" class="fw-bold text-dark">Renew Now</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Property</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($properties) > 0): ?>
                            <?php foreach ($properties as $prop): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded p-2 me-2" style="width: 50px; height: 50px;">
                                                <i class="bi bi-house text-muted fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($prop['title']); ?></div>
                                                <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($prop['location']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo ucfirst($prop['property_type']); ?></td>
                                    <td class="fw-bold text-primary"><?php echo $prop['currency'] . ' ' . number_format($prop['price']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><i class="bi bi-eye-fill text-muted"></i> <?php echo number_format($prop['views'] ?? 0); ?></span></td>
                                    <td>
                                        <?php if($prop['status'] == 'available'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill"><?php echo ucfirst($prop['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($prop['created_at'])); ?></td>
                                    <td>
                                        <a href="edit_property.php?id=<?php echo $prop['id']; ?>" class="btn btn-sm btn-light border me-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="property_history.php?id=<?php echo $prop['id']; ?>" class="btn btn-sm btn-light border me-1" title="History"><i class="bi bi-clock-history"></i></a>
                                        <a href="../property_details.php?id=<?php echo $prop['id']; ?>" target="_blank" class="btn btn-sm btn-light border me-1" title="View"><i class="bi bi-eye"></i></a>
                                        <button class="btn btn-sm btn-light border text-danger" title="Delete" onclick="deleteProperty(<?php echo $prop['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <h5 class="fw-bold text-muted">No Properties Found</h5>
                                    <p class="text-muted">Start by adding your first property listing.</p>
                                    <a href="add_property.php" class="btn btn-primary mt-2">Add Property</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" action="delete_property.php" method="POST" style="display: none;">
    <input type="hidden" name="property_id" id="deletePropertyId">
</form>

<script>
function deleteProperty(id) {
    if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
        document.getElementById('deletePropertyId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>