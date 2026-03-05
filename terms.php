<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-heart-fill"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="privacy.php">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="mb-4 fw-bold">Terms of Service</h1>
                <p class="text-muted mb-5">Last Updated: <?php echo date('F d, Y'); ?></p>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">1. Acceptance of Terms</h3>
                    <p>By accessing or using <?php echo SITE_NAME; ?>, you agree to be bound by these Terms of Service. If you do not agree to all of these terms, you may not access or use our services.</p>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">2. User Accounts</h3>
                    <p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms.</p>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4 border-start border-4 border-danger bg-danger-subtle">
                    <h3 class="h5 fw-bold mb-3 text-danger"><i class="bi bi-shield-exclamation me-2"></i>CRITICAL WARNING: Tenant Safety & Payment Policy</h3>
                    <p class="mb-3 fw-bold">To protect yourself from fraud, you strictly agree to the following:</p>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex">
                            <i class="bi bi-x-circle-fill text-danger me-2 mt-1"></i>
                            <div><strong>DO NOT PAY IN ADVANCE:</strong> Never pay any money (rent, deposit, or holding fees) until you have physically visited the property and verified the identity of the landlord/agent.</div>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                            <div><strong>VERIFY FIRST:</strong> Inspect the property inside and out. Ensure the person showing the property has the keys and the authority to rent it.</div>
                        </li>
                        <li class="mb-0 d-flex">
                            <i class="bi bi-info-circle-fill text-primary me-2 mt-1"></i>
                            <div><strong>PLATFORM LIABILITY:</strong> <?php echo SITE_NAME; ?> is a listing platform. We do not own the properties. We are not liable for any losses incurred if you transfer money without proper due diligence.</div>
                        </li>
                    </ul>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">4. Anti-Fraud & Fake Listings Policy</h3>
                    <p>We are committed to maintaining a safe and trustworthy platform. Users agree to the following strictly enforced rules:</p>
                    <ul>
                        <li class="mb-2"><strong>Accurate Representation:</strong> All property listings must accurately represent the actual property. Photos must be current and truthful.</li>
                        <li class="mb-2"><strong>Verification:</strong> We reserve the right to verify property ownership or authorization to list.</li>
                        <li class="mb-2"><strong>Consequences of Fake Listings:</strong> Any user found posting fake listings, scamming potential tenants, or misrepresenting properties will face:
                            <ul>
                                <li>Immediate and permanent account ban.</li>
                                <li>Reporting to local law enforcement agencies.</li>
                                <li>Blacklisting from all our associated services.</li>
                                <li>Legal action for damages caused to the platform's reputation.</li>
                            </ul>
                        </li>
                    </ul>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">4. Fees and Payments</h3>
                    <p>Dealers are required to pay subscription fees to list properties. All fees are non-refundable unless otherwise stated in writing by <?php echo SITE_NAME; ?>.</p>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">5. Limitation of Liability</h3>
                    <p>In no event shall <?php echo SITE_NAME; ?>, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses.</p>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">7. Changes to Terms</h3>
                    <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms.</p>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Violation of these terms will result in immediate termination of your access to the Service.
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>