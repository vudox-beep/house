<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../models/Referral.php';

// Include Header
include 'includes/header.php';

// Get current user details
$userModel = new User();
$user = $userModel->getUserById($_SESSION['user_id']);

if (!$user) {
    echo "<script>window.location.href = '../logout.php';</script>";
    exit;
}

$email = $user['email'];
$referralModel = new Referral();
$discount = $referralModel->getDiscountForDealer((int)$_SESSION['user_id']);
$amount = $discount['final_amount'];
$currency = CURRENCY;
?>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Choose Your Plan</h2>
                <p class="text-muted">Unlock unlimited listings and premium features.</p>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Free Plan -->
                <div class="col-md-5">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-body p-4 text-center d-flex flex-column">
                            <h5 class="fw-bold text-muted mb-3">Basic Access</h5>
                            <h1 class="display-4 fw-bold mb-0">Free</h1>
                            <p class="text-muted mb-4">Forever</p>
                            <ul class="list-unstyled text-start mb-auto mx-auto" style="max-width: 250px;">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Browse Properties</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Contact Dealers</li>
                                <li class="mb-2"><i class="bi bi-x-circle-fill text-muted me-2"></i> List Properties</li>
                                <li class="mb-2"><i class="bi bi-x-circle-fill text-muted me-2"></i> Analytics Dashboard</li>
                            </ul>
                            <button class="btn btn-outline-secondary mt-4" disabled>Current Plan</button>
                        </div>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="col-md-5">
                    <div class="card h-100 border-0 shadow rounded-3 position-relative overflow-hidden">
                        <div class="position-absolute top-0 end-0 bg-warning text-dark px-3 py-1 fw-bold small rounded-bottom-start">RECOMMENDED</div>
                        <div class="card-body p-4 text-center d-flex flex-column">
                            <h5 class="fw-bold text-primary mb-3">Dealer Pro</h5>
                            <h1 class="display-4 fw-bold mb-0"><?php echo $currency . ' ' . number_format($amount, 2); ?></h1>
                            <p class="text-muted mb-4">Per Month</p>
                            <ul class="list-unstyled text-start mb-auto mx-auto" style="max-width: 250px;">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Unlimited Listings</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Featured Properties</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Analytics & Leads</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Verified Badge</li>
                            </ul>
                            <button class="btn btn-primary mt-4" onclick="openPaymentModal()">Upgrade Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pre-Payment Selection Modal -->
    <div class="modal fade" id="prePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Select Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-4">
                    <form id="paymentForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Choose Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" onchange="togglePaymentFields()">
                                <option value="card">Credit / Debit Card (Visa/Mastercard)</option>
                                <option value="mobile-money">Mobile Money (Airtel / MTN / Zamtel)</option>
                            </select>
                        </div>

                        <div id="mobile_money_fields" style="display: none;" class="bg-light p-3 rounded mb-3">
                            <div class="mb-3">
                                <label for="country" class="form-label small text-uppercase fw-bold">Country</label>
                                <select class="form-select" id="country" name="country" onchange="updatePhonePlaceholder()">
                                    <option value="zm" data-code="260" selected>Zambia (+260)</option>
                                    <option value="mw" data-code="265">Malawi (+265)</option>
                                    <option value="ke" data-code="254">Kenya (+254)</option>
                                    <option value="ug" data-code="256">Uganda (+256)</option>
                                    <option value="tz" data-code="255">Tanzania (+255)</option>
                                    <option value="rw" data-code="250">Rwanda (+250)</option>
                                    <option value="gh" data-code="233">Ghana (+233)</option>
                                    <option value="ng" data-code="234">Nigeria (+234)</option>
                                    <option value="za" data-code="27">South Africa (+27)</option>
                                    <option value="zw" data-code="263">Zimbabwe (+263)</option>
                                    <option value="bw" data-code="267">Botswana (+267)</option>
                                    <option value="na" data-code="264">Namibia (+264)</option>
                                    <option value="mz" data-code="258">Mozambique (+258)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label small text-uppercase fw-bold">Mobile Number</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="e.g. 097xxxxxxx">
                                    <button class="btn btn-outline-secondary" type="button" onclick="verifyPhoneFormat()">Verify</button>
                                </div>
                                <div id="phone-feedback" class="form-text"></div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="button" class="btn btn-primary btn-lg" onclick="initiateLencoPayment()">Proceed to Pay</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Lenco Inline JS -->
<script src="https://pay.lenco.co/js/v1/inline.js"></script>

<script>
    function openPaymentModal() {
        var modal = new bootstrap.Modal(document.getElementById('prePaymentModal'));
        modal.show();
    }

    function togglePaymentFields() {
        var method = document.getElementById('payment_method').value;
        var mmFields = document.getElementById('mobile_money_fields');
        if (method === 'mobile-money') {
            mmFields.style.display = 'block';
            document.getElementById('phone').focus();
        } else {
            mmFields.style.display = 'none';
        }
    }

    function verifyPhoneFormat() {
        var inputPhone = document.getElementById('phone').value;
        var country = document.getElementById('country').value;
        var feedback = document.getElementById('phone-feedback');
        
        // Remove non-digits
        var cleanPhone = inputPhone.replace(/\D/g, '');
        var isValid = false;
        var message = "";

        // Simple length check based on country (approximate)
        if (country === 'zm') {
            // Zambia: 10 digits (09x...) or 9 digits (9x...)
            if (cleanPhone.length === 10 && (cleanPhone.startsWith('09') || cleanPhone.startsWith('07'))) {
                isValid = true;
                message = "Valid Zambia Format";
            } else if (cleanPhone.length === 9 && (cleanPhone.startsWith('9') || cleanPhone.startsWith('7'))) {
                isValid = true;
                message = "Valid Zambia Format (will be normalized)";
            } else {
                message = "Invalid format. Expected 10 digits starting with 09/07.";
            }
        } else if (country === 'mw') {
            // Malawi: 9 or 10 digits
            if (cleanPhone.length >= 9 && cleanPhone.length <= 10) {
                isValid = true;
                message = "Valid Malawi Format";
            } else {
                message = "Invalid length for Malawi.";
            }
        } else {
            // Generic check for other countries
            if (cleanPhone.length >= 8 && cleanPhone.length <= 15) {
                isValid = true;
                message = "Format looks okay";
            } else {
                message = "Invalid length";
            }
        }

        if (isValid) {
            feedback.className = "form-text text-success fw-bold";
            feedback.innerHTML = '<i class="bi bi-check-circle"></i> ' + message;
        } else {
            feedback.className = "form-text text-danger fw-bold";
            feedback.innerHTML = '<i class="bi bi-x-circle"></i> ' + message;
        }
    }

    function updatePhonePlaceholder() {
        var countrySelect = document.getElementById('country');
        var selectedOption = countrySelect.options[countrySelect.selectedIndex];
        var code = selectedOption.getAttribute('data-code');
        document.getElementById('phone').placeholder = "e.g. " + code + "xxxxxxx";
        document.getElementById('phone-feedback').innerHTML = ""; // Clear feedback on country change
    }

    function initiateLencoPayment() {
        // Ensure LencoPay is available
        if (typeof LencoPay === 'undefined') {
            alert('Payment system is loading. Please check your internet connection or try again.');
            console.error('LencoPay SDK not loaded');
            return;
        }

        var method = document.getElementById('payment_method').value;
        // API Key Check - Use Public Key for Frontend
        var apiKey = '<?php echo LENCO_SECRET; ?>'; // Using LENCO_SECRET as it starts with 'pub-'
        if (!apiKey) {
            console.error('Lenco Public Key is missing');
            alert('Configuration Error: Payment key missing.');
            return;
        }

        var customerPhone = "<?php echo $user['phone'] ?? '0970000000'; ?>";
        var channels = [method]; // ['card'] or ['mobile-money']
        
        // Handle Mobile Money specific logic
        if (method === 'mobile-money') {
            var inputPhone = document.getElementById('phone').value;
            var country = document.getElementById('country').value;
            
            if (!inputPhone) {
                alert("Please enter a valid mobile money number.");
                return;
            }
            
            // Normalize phone: Remove spaces, dashes, plus signs
            customerPhone = inputPhone.replace(/\D/g, '');
            console.log('Mobile Money Payment: ' + country + ' - ' + customerPhone);
        } else {
            console.log('Card Payment Initiated');
        }

        // Close our modal
        var modalEl = document.getElementById('prePaymentModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        try {
            // Launch Lenco
            LencoPay.getPaid({
                key: apiKey,
                reference: 'SUB-' + Date.now(),
                email: '<?php echo $email; ?>',
                amount: <?php echo $amount; ?>,
                currency: "<?php echo $currency; ?>",
                channels: channels,
                customer: {
                    firstName: "<?php echo explode(' ', $user['name'])[0]; ?>",
                    lastName: "<?php echo explode(' ', $user['name'])[1] ?? ''; ?>",
                    phone: customerPhone,
                },
                onSuccess: function (response) {
                    console.log('Payment Success:', response);
                    window.location = "verify_payment.php?reference=" + response.reference;
                },
                onClose: function () {
                    console.log('Payment window closed');
                    // Do nothing, user cancelled. Maybe re-enable buttons if disabled.
                },
                onConfirmationPending: function () {
                    alert('Your purchase will be completed when the payment is confirmed');
                },
            });
        } catch (err) {
            console.error('Lenco Initiation Error:', err);
            alert('Failed to start payment. See console for details.');
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
