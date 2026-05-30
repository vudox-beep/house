<?php
$host = 'localhost';
$user = 'atphieleqa_house';
$pass = 'Octomass12.';
$dbname = 'atphieleqa_house';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Alter property_type
    $sql1 = "ALTER TABLE properties MODIFY COLUMN property_type ENUM('house','apartment','flat','boarding_house','land','commercial','wedding_venue','restaurant','lodge','studio','cottage','manor','salon','gadget','mechanic','other_service') NOT NULL DEFAULT 'house'";
    $conn->exec($sql1);
    echo "Modified property_type successfully.\n";

    // Alter listing_purpose
    $sql2 = "ALTER TABLE properties MODIFY COLUMN listing_purpose ENUM('rent','sale','booking','service','auction','lease') NOT NULL DEFAULT 'rent'";
    $conn->exec($sql2);
    echo "Modified listing_purpose successfully.\n";

    // Add emails_sent column if not exists
    $stmt = $conn->query("SHOW COLUMNS FROM tenant_requests LIKE 'emails_sent'");
    if($stmt->rowCount() == 0) {
        $sql3 = "ALTER TABLE tenant_requests ADD COLUMN emails_sent TINYINT(1) NOT NULL DEFAULT 0";
        $conn->exec($sql3);
        echo "Added emails_sent column successfully.\n";
    } else {
        echo "Column emails_sent already exists.\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>