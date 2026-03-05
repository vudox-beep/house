<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Try empty first
$dbname = 'rent';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Alter property_type
    $sql1 = "ALTER TABLE properties MODIFY COLUMN property_type ENUM('house','apartment','flat','boarding_house','land','commercial','wedding_venue','restaurant','lodge','studio','cottage','manor') NOT NULL DEFAULT 'house'";
    $conn->exec($sql1);
    echo "Modified property_type successfully.\n";

    // Alter listing_purpose
    $sql2 = "ALTER TABLE properties MODIFY COLUMN listing_purpose ENUM('rent','sale','booking','service') NOT NULL DEFAULT 'rent'";
    $conn->exec($sql2);
    echo "Modified listing_purpose successfully.\n";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    // Try with '.' password just in case, though empty worked for check_schema
    if ($pass === '') {
        try {
            $pass = '.';
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql1 = "ALTER TABLE properties MODIFY COLUMN property_type ENUM('house','apartment','flat','boarding_house','land','commercial','wedding_venue','restaurant','lodge','studio','cottage','manor') NOT NULL DEFAULT 'house'";
            $conn->exec($sql1);
            echo "Modified property_type successfully (with password).\n";

            $sql2 = "ALTER TABLE properties MODIFY COLUMN listing_purpose ENUM('rent','sale','booking','service') NOT NULL DEFAULT 'rent'";
            $conn->exec($sql2);
            echo "Modified listing_purpose successfully (with password).\n";
        } catch (PDOException $e2) {
             echo "Connection failed with password too: " . $e2->getMessage() . "\n";
        }
    }
}
?>