<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

if (!isset($_GET['id'])) {
    echo "<script>window.location.href = 'properties.php';</script>";
    exit;
}

$property_id = $_GET['id'];
$propertyModel = new Property();
$property = $propertyModel->getById($property_id);

if (!$property || $property['dealer_id'] != $_SESSION['user_id']) {
    echo "<script>window.location.href = 'properties.php';</script>";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'property_id' => $property_id,
        'tenant_name' => htmlspecialchars($_POST['tenant_name']),
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'condition_start' => htmlspecialchars($_POST['condition_start']),
        'condition_end' => htmlspecialchars($_POST['condition_end'])
    ];

    if ($propertyModel->addHistory($data)) {
        $success = "History record added successfully.";
    } else {
        $error = "Failed to add record.";
    }
}

$history = $propertyModel->getHistory($property_id);
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Tenancy History</h4>
            <p class="text-muted mb-0">For: <strong><?php echo htmlspecialchars($property['title']); ?></strong></p>
        </div>
        <a href="properties.php" class="btn btn-light border"><i class="bi bi-arrow-left"></i> Back to Properties</a>
    </div>

    <div class="row">
        <!-- Add New Record -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Add Record</h5>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tenant Name</label>
                            <input type="text" class="form-control" name="tenant_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Condition (Start)</label>
                            <textarea class="form-control" name="condition_start" rows="2" placeholder="Notes on condition when moved in..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Condition (End)</label>
                            <textarea class="form-control" name="condition_end" rows="2" placeholder="Notes on condition when moved out..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Record</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- History List -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Tenant</th>
                                    <th>Period</th>
                                    <th>Condition Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($history) > 0): ?>
                                    <?php foreach ($history as $record): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?php echo htmlspecialchars($record['tenant_name']); ?></td>
                                            <td>
                                                <div class="small text-muted">In: <?php echo date('M d, Y', strtotime($record['start_date'])); ?></div>
                                                <div class="small text-muted">Out: <?php echo date('M d, Y', strtotime($record['end_date'])); ?></div>
                                            </td>
                                            <td>
                                                <div class="small mb-1"><strong>Start:</strong> <?php echo htmlspecialchars($record['condition_start']); ?></div>
                                                <div class="small"><strong>End:</strong> <?php echo htmlspecialchars($record['condition_end']); ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">No history records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>