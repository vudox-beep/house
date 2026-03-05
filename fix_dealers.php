<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Update ALL existing dealers to be verified
    // This ensures they don't see the "Account Verification Required" screen
    // Handles both 'dealer' (lowercase) and 'Dealer' (capitalized) just in case
    $sql = "UPDATE users SET is_verified = 1 WHERE role = 'dealer' OR role = 'Dealer'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $count = $stmt->rowCount();
    
    echo "<h1>Success!</h1>";
    echo "<p>Updated <strong>$count</strong> existing dealers to 'Verified' status.</p>";
    echo "<p>They can now access the Add Property page without uploading an ID.</p>";
    echo "<br><a href='index.php'>Go to Home</a>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>