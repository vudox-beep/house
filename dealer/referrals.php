<?php
require_once '../config/config.php';
require_once '../models/Referral.php';

include 'includes/header.php';

$referralModel = new Referral();
$dealerId = (int)$_SESSION['user_id'];

$stats = $referralModel->getDealerReferralStats($dealerId);
$referrals = $referralModel->getDealerReferrals($dealerId, 25);
$rewards = $referralModel->getDealerRewardHistory($dealerId, 25);

$referralLink = SITE_URL . '/register.php?ref=' . urlencode($stats['referral_code'] ?? '');
$shareMessage = "Grow your real estate business with HouseRent Africa! 🏠 Register as a dealer today to unlock unlimited listings, featured properties, and advanced analytics. Join the leading property platform in Zambia. Sign up here: " . $referralLink;
?>

<div class="card border-0 shadow-sm bg-primary text-white mb-4 overflow-hidden position-relative">
    <div class="card-body p-4 position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="fw-bold mb-2">Earn 30% Commission! 🚀</h3>
                <p class="mb-0 opacity-75">Our Referral Program is now bigger than ever. Invite fellow dealers to HouseRent Africa and get 30% of their first subscription payment instantly!</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="display-5 fw-bold"><?php echo (float)REFERRAL_SUBSCRIPTION_COMMISSION_PERCENT; ?>%</div>
                <div class="small opacity-75">Per Successful Referral</div>
            </div>
        </div>
    </div>
    <!-- Decorative Circle -->
    <div class="position-absolute" style="width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; top: -100px; right: -50px; z-index: 1;"></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Successful Referrals</div>
                <h3 class="fw-bold mb-0"><?php echo (int)$stats['successful_referrals']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Pending Referrals</div>
                <h3 class="fw-bold mb-0"><?php echo (int)$stats['pending_referrals']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total Referral Earnings</div>
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="fw-bold mb-0"><?php echo CURRENCY . ' ' . number_format((float)$stats['total_earnings'], 2); ?></h3>
                    <button class="btn btn-sm btn-outline-primary" onclick="showWithdrawInfo()">Contact Admin</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawal Info Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Contact Administrator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4 text-center">
                <div class="mb-4">
                    <i class="bi bi-info-circle text-primary display-4"></i>
                </div>
                <p class="mb-4">To withdraw your referral earnings, please contact the administrator directly for processing.</p>
                <div class="d-grid gap-2">
                    <a href="mailto:chisalaluckyky5@houseforrent.site" class="btn btn-primary">
                        <i class="bi bi-envelope me-2"></i>Email Admin
                    </a>
                    <a href="https://wa.me/260970000000" class="btn btn-success" target="_blank">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3"><i class="bi bi-share me-2"></i>Invite Other Dealers to HouseRent Africa</h5>
        <p class="text-muted mb-4">Help other dealers grow their business while earning a <strong><?php echo (float)REFERRAL_SUBSCRIPTION_COMMISSION_PERCENT; ?>% commission</strong> on their first subscription!</p>
        
        <div class="mb-4">
            <label class="form-label small fw-bold text-muted">Your Referral Link</label>
            <div class="input-group mb-2">
                <input type="text" class="form-control bg-light" id="referralLinkInput" value="<?php echo htmlspecialchars($referralLink); ?>" readonly>
                <button class="btn btn-primary" type="button" onclick="copyReferralLink()">Copy Link</button>
            </div>
        </div>

        <div class="mb-0">
            <label class="form-label small fw-bold text-muted">Share via WhatsApp</label>
            <div class="d-grid">
                <a href="https://wa.me/?text=<?php echo urlencode($shareMessage); ?>" target="_blank" class="btn btn-success">
                    <i class="bi bi-whatsapp me-2"></i>Share Invite Message
                </a>
            </div>
            <div class="mt-2 small text-muted text-center">Your referral code: <span class="fw-bold text-primary"><?php echo htmlspecialchars($stats['referral_code']); ?></span></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0 fw-bold">Referred Dealers</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Dealer</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($referrals)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No referrals yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($referrals as $ref): ?>
                                    <?php $ok = !empty($ref['referral_registered_at']); ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold"><?php echo htmlspecialchars($ref['name']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($ref['email']); ?></div>
                                        </td>
                                        <td>
                                            <?php if ($ok): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3 small text-muted"><?php echo date('M d, Y', strtotime($ref['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0 fw-bold">Reward History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Reward</th>
                                <th>Amount</th>
                                <th class="text-end pe-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rewards)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No rewards yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($rewards as $reward): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold"><?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($reward['reward_type']))); ?></div>
                                            <?php if (!empty($reward['referred_email'])): ?>
                                                <div class="small text-muted"><?php echo htmlspecialchars($reward['referred_email']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold"><?php echo CURRENCY . ' ' . number_format((float)$reward['amount'], 2); ?></td>
                                        <td class="text-end pe-3 small text-muted"><?php echo date('M d, Y', strtotime($reward['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<script>
function showWithdrawInfo() {
    var modal = new bootstrap.Modal(document.getElementById('withdrawModal'));
    modal.show();
}

function copyReferralLink() {
    const input = document.getElementById('referralLinkInput');
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
