<?php
require_once __DIR__ . '/../config/db.php';

class Referral {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getDealerByCode($code) {
        $query = "SELECT id, name, role FROM users WHERE referral_code = :code LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':code' => strtoupper(trim((string)$code))]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || strtolower((string)$user['role']) !== 'dealer') {
            return null;
        }

        return $user;
    }

    public function ensureDealerReferralCode($dealerId, $dealerName = '') {
        $stmt = $this->conn->prepare("SELECT referral_code FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $dealerId]);
        $current = $stmt->fetchColumn();
        if (!empty($current)) {
            return $current;
        }

        $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', substr((string)$dealerName, 0, 4)));
        if ($base === '') {
            $base = 'DLR';
        }

        do {
            $code = $base . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $check = $this->conn->prepare("SELECT id FROM users WHERE referral_code = :code LIMIT 1");
            $check->execute([':code' => $code]);
            $exists = $check->fetch(PDO::FETCH_ASSOC);
        } while ($exists);

        $upd = $this->conn->prepare("UPDATE users SET referral_code = :code WHERE id = :id");
        $upd->execute([
            ':code' => $code,
            ':id' => $dealerId
        ]);

        return $code;
    }

    public function attachReferrer($newUserId, $referrerId) {
        $query = "UPDATE users
                  SET referred_by_user_id = :referrer_id
                  WHERE id = :new_user_id
                  AND (referred_by_user_id IS NULL OR referred_by_user_id = 0)
                  AND id <> :referrer_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':referrer_id' => $referrerId,
            ':new_user_id' => $newUserId
        ]);
    }

    public function creditOnVerifiedDealerRegistration($newDealerId) {
        $this->conn->beginTransaction();
        try {
            $q = "SELECT id, role, referred_by_user_id, referral_registered_at
                  FROM users
                  WHERE id = :id
                  LIMIT 1
                  FOR UPDATE";
            $stmt = $this->conn->prepare($q);
            $stmt->execute([':id' => $newDealerId]);
            $dealer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dealer || strtolower((string)$dealer['role']) !== 'dealer') {
                $this->conn->rollBack();
                return false;
            }

            $referrerId = (int)($dealer['referred_by_user_id'] ?? 0);
            if ($referrerId <= 0 || !empty($dealer['referral_registered_at'])) {
                $this->conn->rollBack();
                return false;
            }

            $mark = $this->conn->prepare(
                "UPDATE users SET referral_registered_at = NOW() WHERE id = :id AND referral_registered_at IS NULL"
            );
            $mark->execute([':id' => $newDealerId]);
            if ($mark->rowCount() === 0) {
                $this->conn->rollBack();
                return false;
            }

            // Ensure the referrer exists in the dealers table (without reward)
            $this->conn->prepare(
                "INSERT INTO dealers (user_id, subscription_status, subscription_expiry, referral_earnings)
                 VALUES (:user_id, 'none', NULL, 0)
                 ON DUPLICATE KEY UPDATE user_id = user_id"
            )->execute([':user_id' => $referrerId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function getDealerReferralStats($dealerId) {
        $this->ensureDealerReferralCode($dealerId);

        $stats = [
            'referral_code' => '',
            'successful_referrals' => 0,
            'pending_referrals' => 0,
            'total_earnings' => 0,
            'milestone_awarded' => 0
        ];

        $stmt = $this->conn->prepare(
            "SELECT u.referral_code, d.referral_earnings, d.referral_milestone_awarded
             FROM users u
             LEFT JOIN dealers d ON d.user_id = u.id
             WHERE u.id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $dealerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $stats['referral_code'] = $row['referral_code'] ?? '';
            $stats['total_earnings'] = (float)($row['referral_earnings'] ?? 0);
            $stats['milestone_awarded'] = (int)($row['referral_milestone_awarded'] ?? 0);
        }

        $success = $this->conn->prepare(
            "SELECT COUNT(*) FROM users
             WHERE referred_by_user_id = :id
             AND role = 'dealer'
             AND referral_registered_at IS NOT NULL"
        );
        $success->execute([':id' => $dealerId]);
        $stats['successful_referrals'] = (int)$success->fetchColumn();

        $pending = $this->conn->prepare(
            "SELECT COUNT(*) FROM users
             WHERE referred_by_user_id = :id
             AND role = 'dealer'
             AND referral_registered_at IS NULL"
        );
        $pending->execute([':id' => $dealerId]);
        $stats['pending_referrals'] = (int)$pending->fetchColumn();

        return $stats;
    }

    public function getDealerReferrals($dealerId, $limit = 30) {
        $limit = max(1, (int)$limit);
        $query = "SELECT id, name, email, created_at, verification_token, referral_registered_at
                  FROM users
                  WHERE referred_by_user_id = :id
                  AND role = 'dealer'
                  ORDER BY created_at DESC
                  LIMIT {$limit}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $dealerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDealerRewardHistory($dealerId, $limit = 30) {
        $limit = max(1, (int)$limit);
        $query = "SELECT rr.*, u.name AS referred_name, u.email AS referred_email
                  FROM referral_rewards rr
                  LEFT JOIN users u ON u.id = rr.referred_user_id
                  WHERE rr.referrer_user_id = :id
                  ORDER BY rr.created_at DESC
                  LIMIT {$limit}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $dealerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDiscountForDealer($dealerId) {
        $stmt = $this->conn->prepare(
            "SELECT u.role, u.referred_by_user_id, d.referral_discount_used
             FROM users u
             LEFT JOIN dealers d ON d.user_id = u.id
             WHERE u.id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $dealerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $isDealer = strtolower((string)($row['role'] ?? '')) === 'dealer';
        $hasReferrer = (int)($row['referred_by_user_id'] ?? 0) > 0;
        $discountUsed = (int)($row['referral_discount_used'] ?? 0) === 1;

        $txCheck = $this->conn->prepare(
            "SELECT COUNT(*) FROM transactions WHERE user_id = :id AND status = 'successful'"
        );
        $txCheck->execute([':id' => $dealerId]);
        $hasSuccessfulPayment = (int)$txCheck->fetchColumn() > 0;

        $discountPercent = (float)REFERRAL_NEW_DEALER_DISCOUNT_PERCENT;
        $discountAmount = round((float)SUBSCRIPTION_FEE * ($discountPercent / 100), 2);
        $finalAmount = max(0, round((float)SUBSCRIPTION_FEE - $discountAmount, 2));

        $isEligible = $isDealer && $hasReferrer && !$discountUsed && !$hasSuccessfulPayment && $discountPercent > 0;

        return [
            'eligible' => $isEligible,
            'discount_percent' => $discountPercent,
            'original_amount' => (float)SUBSCRIPTION_FEE,
            'discount_amount' => $discountAmount,
            'final_amount' => $isEligible ? $finalAmount : (float)SUBSCRIPTION_FEE
        ];
    }

    public function markDealerDiscountUsed($dealerId) {
        $stmt = $this->conn->prepare(
            "UPDATE dealers d
             INNER JOIN users u ON u.id = d.user_id
             SET d.referral_discount_used = 1
             WHERE d.user_id = :id
             AND d.referral_discount_used = 0
             AND u.referred_by_user_id IS NOT NULL"
        );
        return $stmt->execute([':id' => $dealerId]);
    }

    public function processSubscriptionCommission($dealerId, $subscriptionAmount) {
        $this->conn->beginTransaction();
        try {
            // Check if dealer was referred and if commission is already paid
            $q = "SELECT u.referred_by_user_id, d.referral_commission_paid 
                  FROM users u 
                  LEFT JOIN dealers d ON d.user_id = u.id 
                  WHERE u.id = :id 
                  LIMIT 1 
                  FOR UPDATE";
            $stmt = $this->conn->prepare($q);
            $stmt->execute([':id' => $dealerId]);
            $dealer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dealer) {
                $this->conn->rollBack();
                return false;
            }

            $referrerId = (int)($dealer['referred_by_user_id'] ?? 0);
            $commissionPaid = (int)($dealer['referral_commission_paid'] ?? 0);

            // Only pay commission if there's a referrer and it hasn't been paid yet
            if ($referrerId <= 0 || $commissionPaid === 1) {
                $this->conn->rollBack();
                return false;
            }

            // Calculate 30% commission
            $commissionPercent = (float)REFERRAL_SUBSCRIPTION_COMMISSION_PERCENT;
            $commissionAmount = round((float)$subscriptionAmount * ($commissionPercent / 100), 2);

            if ($commissionAmount <= 0) {
                $this->conn->rollBack();
                return false;
            }

            // 1. Mark commission as paid for the dealer
            $mark = $this->conn->prepare(
                "UPDATE dealers SET referral_commission_paid = 1 WHERE user_id = :id"
            );
            $mark->execute([':id' => $dealerId]);

            // 2. Ensure referrer exists in dealers table
            $this->conn->prepare(
                "INSERT INTO dealers (user_id, subscription_status, subscription_expiry, referral_earnings)
                 VALUES (:user_id, 'none', NULL, 0)
                 ON DUPLICATE KEY UPDATE user_id = user_id"
            )->execute([':user_id' => $referrerId]);

            // 3. Record the reward
            $insReward = $this->conn->prepare(
                "INSERT INTO referral_rewards (referrer_user_id, referred_user_id, reward_type, amount, notes)
                 VALUES (:referrer, :referred, 'subscription_commission', :amount, :notes)"
            );
            $insReward->execute([
                ':referrer' => $referrerId,
                ':referred' => $dealerId,
                ':amount' => $commissionAmount,
                ':notes' => '30% commission from first subscription'
            ]);

            // 4. Update referrer's balance
            $updBalance = $this->conn->prepare(
                "UPDATE dealers SET referral_earnings = referral_earnings + :amount WHERE user_id = :user_id"
            );
            $updBalance->execute([
                ':amount' => $commissionAmount,
                ':user_id' => $referrerId
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }
}
?>
