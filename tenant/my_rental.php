<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

// Fetch Active Rental
// NOTE: 'image' column might be in property_images table or properties table.
// Checking Property.php model, there is no 'image' column in create() method, only video_url.
// Images are stored in property_images table.
// Let's join with property_images table to get the main image.

$sql = "SELECT r.*, p.title, p.location, p.price, 
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_main DESC, id ASC LIMIT 1) as image,
        u.name as dealer_name, u.email as dealer_email, u.phone as dealer_phone, u.bank_details
        FROM rentals r
        JOIN properties p ON r.property_id = p.id
        JOIN users u ON r.dealer_id = u.id
        WHERE r.tenant_id = :tenant_id AND r.status = 'active'
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute([':tenant_id' => $user_id]);
$rental = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">My Rental</h4>
            <p class="text-muted small mb-0">Details of your current property.</p>
        </div>
    </div>

    <?php if ($rental): ?>
        <div class="row">
            <!-- Property Card -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <?php 
                    $imageUrl = !empty($rental['image']) && file_exists('../' . $rental['image']) 
                        ? '../' . $rental['image'] 
                        : 'https://via.placeholder.com/600x400?text=No+Image';
                    ?>
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="card-img-top" alt="Property Image" style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($rental['title']); ?></h5>
                        <p class="text-muted mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($rental['location']); ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mb-3">
                            <span class="text-muted small">Monthly Rent</span>
                            <span class="fw-bold fs-5 text-primary"><?php echo $rental['currency'] . ' ' . number_format($rental['rent_amount']); ?></span>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Start Date</small>
                                <span class="fw-medium"><?php echo date('M d, Y', strtotime($rental['start_date'])); ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Landlord Info -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">Landlord / Dealer Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary-subtle text-primary rounded-circle p-3 me-3">
                                <i class="bi bi-person-badge-fill fs-3"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($rental['dealer_name']); ?></h6>
                                <span class="text-muted small">Property Manager</span>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 border-0 d-flex align-items-center">
                                <i class="bi bi-envelope me-3 text-muted"></i>
                                <a href="mailto:<?php echo htmlspecialchars($rental['dealer_email']); ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($rental['dealer_email']); ?></a>
                            </li>
                            <li class="list-group-item px-0 border-0 d-flex align-items-center">
                                <i class="bi bi-telephone me-3 text-muted"></i>
                                <a href="tel:<?php echo htmlspecialchars($rental['dealer_phone']); ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($rental['dealer_phone']); ?></a>
                            </li>
                            <li class="list-group-item px-0 border-0 d-flex align-items-center">
                                <i class="bi bi-whatsapp me-3 text-success"></i>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $rental['dealer_phone']); ?>" target="_blank" class="text-decoration-none text-dark">Chat on WhatsApp</a>
                            </li>
                        </ul>
                        
                        <hr class="my-4">
                        
                        <?php if(!empty($rental['bank_details'])): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Deposit Details</h6>
                            <div class="bg-light p-3 rounded-3 small">
                                <i class="bi bi-wallet2 me-2 text-primary"></i>
                                <?php echo nl2br(htmlspecialchars($rental['bank_details'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="alert alert-warning border-0 d-flex align-items-start" role="alert">
                            <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                            <div class="small">
                                Need to report an issue? Contact your landlord directly using the details above.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/6028/6028690.png" width="120" class="mb-3 opacity-50" alt="No House">
            <h4 class="fw-bold text-muted">No Active Rental</h4>
            <p class="text-muted">You are not currently assigned to any property.</p>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>