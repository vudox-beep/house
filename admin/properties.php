<?php
require_once '../config/config.php';
require_once '../models/Property.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'verify_property') {
            $prop_id = $_POST['property_id'];
            $status = $_POST['verify_status']; // 1 or 0
            
            $stmt = $pdo->prepare("UPDATE properties SET is_verified = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $prop_id]);
            
            // Redirect to avoid resubmission
            header("Location: properties.php?success=Verification updated");
            exit;
        }
    }

    $propertyModel = new Property();
    // Modified to get ALL properties regardless of dealer status for admin review
    $stmt = $pdo->query("SELECT p.*, u.name as dealer_name FROM properties p LEFT JOIN users u ON p.dealer_id = u.id ORDER BY p.created_at DESC");
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Topbar -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="mb-0 fw-bold text-dark">Property Management</h4>
            <div class="d-flex align-items-center">
                <div class="me-3 text-end">
                    <p class="mb-0 fw-bold text-dark"><?php echo $_SESSION['user_name']; ?></p>
                    <small class="text-muted">Administrator</small>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-person-fill fs-5"></i>
                </div>
            </div>
        </div>

        <!-- Properties Table -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Property</th>
                                <th>Dealer</th>
                                <th>Verification</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($properties) > 0): ?>
                                <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded p-2 me-2" style="width: 50px; height: 50px;">
                                                    <?php if(!empty($property['verification_image'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($property['verification_image']); ?>" class="w-100 h-100 object-fit-cover rounded cursor-pointer" onclick="viewVerification('../<?php echo htmlspecialchars($property['verification_image']); ?>')" title="Click to view verification photo">
                                                    <?php else: ?>
                                                        <i class="bi bi-house text-muted fs-4"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($property['title']); ?></div>
                                                    <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['location']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-medium"><?php echo htmlspecialchars($property['dealer_name'] ?? 'Unknown'); ?></span>
                                        </td>
                                        <td>
                                            <?php if($property['is_verified']): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Pending</span>
                                            <?php endif; ?>
                                            
                                            <?php if(empty($property['verification_image'])): ?>
                                                <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle"></i> No Photo</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($property['status'] == 'available'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Available</span>
                                            <?php elseif($property['status'] == 'sold'): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Sold</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill"><?php echo ucfirst($property['status']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="../property_details.php?id=<?php echo $property['id']; ?>" target="_blank" class="btn btn-sm btn-light border me-1" title="View"><i class="bi bi-eye"></i></a>
                                            
                                            <?php if(!$property['is_verified']): ?>
                                                <button class="btn btn-sm btn-outline-success border me-1" onclick="verifyProperty(<?php echo $property['id']; ?>, 1)" title="Approve"><i class="bi bi-check-lg"></i></button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary border me-1" onclick="verifyProperty(<?php echo $property['id']; ?>, 0)" title="Revoke"><i class="bi bi-x-lg"></i></button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-light border text-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <h5 class="fw-bold text-muted">No Properties Found</h5>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Verification Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalVerifyImg" src="" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>

    <!-- Verify Form -->
    <form id="verifyForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="verify_property">
        <input type="hidden" name="property_id" id="verify_prop_id">
        <input type="hidden" name="verify_status" id="verify_status">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewVerification(src) {
            document.getElementById('modalVerifyImg').src = src;
            new bootstrap.Modal(document.getElementById('verificationModal')).show();
        }

        function verifyProperty(id, status) {
            const action = status === 1 ? 'Approve' : 'Revoke';
            if (confirm(`Are you sure you want to ${action} this property verification?`)) {
                document.getElementById('verify_prop_id').value = id;
                document.getElementById('verify_status').value = status;
                document.getElementById('verifyForm').submit();
            }
        }
    </script>
</body>
</html>