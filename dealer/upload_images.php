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

// Handle Uploads
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadDir = '../assets/images/properties/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    // Check current counts
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM property_images WHERE property_id = ? AND type = 'image'");
    $stmt->execute([$property_id]);
    $imageCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM property_images WHERE property_id = ? AND type = 'video'");
    $stmt->execute([$property_id]);
    $videoCount = $stmt->fetchColumn();

    // 1. Handle Images
    if (!empty($_FILES['images']['name'][0])) {
        $totalFiles = count($_FILES['images']['name']);
        
        if (($imageCount + $totalFiles) > 10) {
            $error .= "You can only upload a maximum of 10 images. You already have $imageCount.<br>";
        } else {
            for ($i = 0; $i < $totalFiles; $i++) {
                $fileName = $_FILES['images']['name'][$i];
                $fileTmp = $_FILES['images']['tmp_name'][$i];
                $fileSize = $_FILES['images']['size'][$i];
                $fileError = $_FILES['images']['error'][$i];

                if ($fileError === 0) {
                    if ($fileSize <= 6 * 1024 * 1024) { // 6MB
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                        if (in_array($fileExt, $allowed)) {
                            $newFileName = 'prop_' . $property_id . '_' . uniqid() . '.' . $fileExt;
                            $destination = $uploadDir . $newFileName;

                            if (move_uploaded_file($fileTmp, $destination)) {
                                // Save to DB
                                $stmt = $pdo->prepare("INSERT INTO property_images (property_id, image_path, type) VALUES (?, ?, 'image')");
                                $stmt->execute([$property_id, 'assets/images/properties/' . $newFileName]);
                            }
                        } else {
                            $error .= "File '$fileName' is not a valid image type.<br>";
                        }
                    } else {
                        $error .= "File '$fileName' exceeds 6MB limit.<br>";
                    }
                }
            }
            if (empty($error)) $message .= "Images uploaded successfully.<br>";
        }
    }

    // 2. Handle Video
    if (!empty($_FILES['video']['name'])) {
        if ($videoCount >= 1) {
            $error .= "You can only upload 1 video per property.<br>";
        } else {
            $fileName = $_FILES['video']['name'];
            $fileTmp = $_FILES['video']['tmp_name'];
            $fileSize = $_FILES['video']['size'];
            $fileError = $_FILES['video']['error'];

            if ($fileError === 0) {
                if ($fileSize <= 6 * 1024 * 1024) { // 6MB
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowed = ['mp4', 'webm', 'ogg'];

                    if (in_array($fileExt, $allowed)) {
                        $newFileName = 'vid_' . $property_id . '_' . uniqid() . '.' . $fileExt;
                        $destination = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmp, $destination)) {
                            // Save to DB
                            $stmt = $pdo->prepare("INSERT INTO property_images (property_id, image_path, type) VALUES (?, ?, 'video')");
                            $stmt->execute([$property_id, 'assets/images/properties/' . $newFileName]);
                            $message .= "Video uploaded successfully.<br>";
                        }
                    } else {
                        $error .= "Invalid video format. Allowed: mp4, webm, ogg.<br>";
                    }
                } else {
                    $error .= "Video exceeds 6MB limit.<br>";
                }
            }
        }
    }
}

// Fetch existing media
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$stmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY type, id DESC");
$stmt->execute([$property_id]);
$media = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Manage Media</h4>
                    <a href="properties.php" class="btn btn-sm btn-light border">Back to List</a>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-3">Upload Requirements</h6>
                        <ul class="small text-muted mb-0">
                            <li>Images: Max 10 files (JPG, PNG, WEBP). Max 6MB each.</li>
                            <li>Video: Max 1 file (MP4, WEBM). Max 6MB.</li>
                        </ul>
                    </div>

                    <form method="POST" action="" enctype="multipart/form-data" class="mb-5">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Add Images</label>
                                <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Add Video</label>
                                <input type="file" class="form-control" name="video" accept="video/*">
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-primary px-4">Upload Media</button>
                        </div>
                    </form>

                    <h5 class="fw-bold mb-3">Existing Media</h5>
                    <div class="row g-3">
                        <?php if (count($media) > 0): ?>
                            <?php foreach ($media as $item): ?>
                                <div class="col-md-3 col-6">
                                    <div class="position-relative border rounded overflow-hidden">
                                        <?php if ($item['type'] === 'video'): ?>
                                            <video src="../<?php echo $item['image_path']; ?>" class="w-100" style="height: 150px; object-fit: cover;" controls></video>
                                            <span class="position-absolute top-0 start-0 badge bg-danger m-2">Video</span>
                                        <?php else: ?>
                                            <img src="../<?php echo $item['image_path']; ?>" class="w-100" style="height: 150px; object-fit: cover;">
                                        <?php endif; ?>
                                        
                                        <a href="delete_image.php?id=<?php echo $item['id']; ?>&prop_id=<?php echo $property_id; ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="return confirm('Delete this media?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-4">
                                <p class="text-muted">No media uploaded yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>