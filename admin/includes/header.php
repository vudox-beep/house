<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div class="d-flex align-items-center">
            <button class="btn btn-link d-md-none me-3" id="adminSidebarToggle">
                <i class="bi bi-list fs-3"></i>
            </button>
            <h4 class="mb-0 fw-bold">Admin Panel</h4>
        </div>
        <div class="d-flex align-items-center">
            
            <!-- Send SMS Button -->
            <button class="btn btn-outline-primary btn-sm me-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#adminSMSModal">
                <i class="bi bi-chat-dots-fill me-1"></i> Send SMS
            </button>

            <!-- Notification Manager Link -->
            <a href="notifications.php" class="text-secondary text-decoration-none me-4 position-relative">
                <i class="bi bi-bell-fill fs-4"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-primary border border-light rounded-circle" style="font-size: 0.5rem;">
                    <span class="visually-hidden">Manage</span>
                </span>
            </a>

            <!-- Admin Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="me-2 fw-bold text-dark d-none d-md-block"><?php echo $_SESSION['user_name']; ?></span>
                    <img src="../assets/images/user-placeholder.png" class="rounded-circle border" width="40" height="40" alt="Admin">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li><a class="dropdown-item" href="notifications.php"><i class="bi bi-megaphone me-2"></i> Send Notification</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#adminSMSModal"><i class="bi bi-chat-dots me-2"></i> Quick SMS</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

<!-- SMS Modal -->
<div class="modal fade" id="adminSMSModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Send Quick SMS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4">
                <form id="adminSMSForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Phone Number</label>
                        <input type="text" class="form-control" name="phone" placeholder="e.g. +26097xxxxxxx" required>
                        <div class="form-text small">Include country code (e.g., +260 for Zambia).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Message</label>
                        <textarea class="form-control" name="message" rows="3" maxlength="160" placeholder="Type your message here..." required></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <div class="form-text small">Max 160 characters.</div>
                            <div id="smsCharCount" class="small text-muted">0 / 160</div>
                        </div>
                    </div>
                    <div id="smsAlert" class="alert d-none small"></div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="btnSendSMS">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                            Send SMS Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('adminSidebarToggle').addEventListener('click', function() {
        document.querySelector('.admin-sidebar').classList.toggle('active');
    });

    // SMS Character Counter
    const smsTextarea = document.querySelector('#adminSMSModal textarea[name="message"]');
    const smsCountDisplay = document.getElementById('smsCharCount');
    if (smsTextarea) {
        smsTextarea.addEventListener('input', function() {
            smsCountDisplay.textContent = `${this.value.length} / 160`;
        });
    }

    // SMS Form Handler
    const smsForm = document.getElementById('adminSMSForm');
    const smsAlert = document.getElementById('smsAlert');
    const btnSendSMS = document.getElementById('btnSendSMS');

    if (smsForm) {
        smsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            btnSendSMS.disabled = true;
            btnSendSMS.querySelector('.spinner-border').classList.remove('d-none');
            smsAlert.classList.add('d-none');

            fetch('send_sms_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnSendSMS.disabled = false;
                btnSendSMS.querySelector('.spinner-border').classList.add('d-none');
                
                smsAlert.classList.remove('d-none', 'alert-danger', 'alert-success');
                if (data.status === 'success') {
                    smsAlert.classList.add('alert-success');
                    smsAlert.textContent = data.message;
                    smsForm.reset();
                    smsCountDisplay.textContent = '0 / 160';
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('adminSMSModal'));
                        modal.hide();
                        smsAlert.classList.add('d-none');
                    }, 2000);
                } else {
                    smsAlert.classList.add('alert-danger');
                    smsAlert.textContent = data.message;
                }
            })
            .catch(error => {
                btnSendSMS.disabled = false;
                btnSendSMS.querySelector('.spinner-border').classList.add('d-none');
                smsAlert.classList.remove('d-none');
                smsAlert.classList.add('alert-danger');
                smsAlert.textContent = 'Network error. Please try again.';
            });
        });
    }
</script>