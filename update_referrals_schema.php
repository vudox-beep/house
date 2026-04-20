 <?php
if (PHP_SAPI === 'cli') {
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? 'off';
    $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $sessionPath = __DIR__ . '/tmp_sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0777, true);
    }
    ini_set('session.save_path', $sessionPath);
}

require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function columnExists(PDO $pdo, $table, $column) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND COLUMN_NAME = :column
        ");
        $stmt->execute([':table' => $table, ':column' => $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    function indexExists(PDO $pdo, $table, $index) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND INDEX_NAME = :index
        ");
        $stmt->execute([':table' => $table, ':index' => $index]);
        return (int)$stmt->fetchColumn() > 0;
    }

    function fkExists(PDO $pdo, $table, $constraint) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND CONSTRAINT_NAME = :constraint
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        $stmt->execute([':table' => $table, ':constraint' => $constraint]);
        return (int)$stmt->fetchColumn() > 0;
    }

    if (!columnExists($pdo, 'users', 'referral_code')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN referral_code VARCHAR(40) NULL UNIQUE AFTER google_id");
        echo "Added users.referral_code\n";
    }

    if (!columnExists($pdo, 'users', 'referred_by_user_id')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN referred_by_user_id INT NULL AFTER referral_code");
        echo "Added users.referred_by_user_id\n";
    }

    if (!columnExists($pdo, 'users', 'referral_registered_at')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN referral_registered_at DATETIME NULL AFTER referred_by_user_id");
        echo "Added users.referral_registered_at\n";
    }

    if (!indexExists($pdo, 'users', 'idx_users_referral_code')) {
        $pdo->exec("CREATE INDEX idx_users_referral_code ON users(referral_code)");
        echo "Added index idx_users_referral_code\n";
    }

    if (!indexExists($pdo, 'users', 'idx_users_referred_by')) {
        $pdo->exec("CREATE INDEX idx_users_referred_by ON users(referred_by_user_id)");
        echo "Added index idx_users_referred_by\n";
    }

    if (!fkExists($pdo, 'users', 'fk_users_referred_by')) {
        $pdo->exec("
            ALTER TABLE users
            ADD CONSTRAINT fk_users_referred_by
            FOREIGN KEY (referred_by_user_id) REFERENCES users(id)
            ON DELETE SET NULL
        ");
        echo "Added foreign key fk_users_referred_by\n";
    }

    if (!columnExists($pdo, 'dealers', 'referral_earnings')) {
        $pdo->exec("ALTER TABLE dealers ADD COLUMN referral_earnings DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER subscription_expiry");
        echo "Added dealers.referral_earnings\n";
    }

    if (!columnExists($pdo, 'dealers', 'referral_milestone_awarded')) {
        $pdo->exec("ALTER TABLE dealers ADD COLUMN referral_milestone_awarded TINYINT(1) NOT NULL DEFAULT 0 AFTER referral_earnings");
        echo "Added dealers.referral_milestone_awarded\n";
    }

    if (!columnExists($pdo, 'dealers', 'referral_discount_used')) {
        $pdo->exec("ALTER TABLE dealers ADD COLUMN referral_discount_used TINYINT(1) NOT NULL DEFAULT 0 AFTER referral_milestone_awarded");
        echo "Added dealers.referral_discount_used\n";
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS referral_rewards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            referrer_user_id INT NOT NULL,
            referred_user_id INT NULL,
            reward_type ENUM('signup_bonus', 'milestone_bonus', 'subscription_commission') NOT NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            notes VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_referrer (referrer_user_id),
            INDEX idx_referred (referred_user_id),
            CONSTRAINT fk_referral_rewards_referrer FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_referral_rewards_referred FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Also update existing table if it already exists but doesn't have the new enum value
    try {
        $pdo->exec("ALTER TABLE referral_rewards MODIFY COLUMN reward_type ENUM('signup_bonus', 'milestone_bonus', 'subscription_commission') NOT NULL");
        echo "Updated referral_rewards.reward_type ENUM\n";
    } catch (Exception $e) {
        // Ignore if already updated or other errors
    }

    if (!columnExists($pdo, 'dealers', 'referral_commission_paid')) {
        $pdo->exec("ALTER TABLE dealers ADD COLUMN referral_commission_paid TINYINT(1) NOT NULL DEFAULT 0 AFTER referral_discount_used");
        echo "Added dealers.referral_commission_paid\n";
    }

    // Backfill referral codes for existing dealers.
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'dealer' AND (referral_code IS NULL OR referral_code = '')");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', substr((string)$row['name'], 0, 4)));
        if ($base === '') {
            $base = 'DLR';
        }

        do {
            $code = $base . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $check = $pdo->prepare("SELECT id FROM users WHERE referral_code = :code LIMIT 1");
            $check->execute([':code' => $code]);
            $exists = $check->fetch(PDO::FETCH_ASSOC);
        } while ($exists);

        $upd = $pdo->prepare("UPDATE users SET referral_code = :code WHERE id = :id");
        $upd->execute([':code' => $code, ':id' => $row['id']]);
    }

    echo "Backfilled dealer referral codes: " . count($rows) . "\n";
    echo "Referral schema update complete.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
