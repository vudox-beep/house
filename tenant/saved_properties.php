<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/Favorite.php';

// Include Header (this starts session and checks auth)
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$favoriteModel = new Favorite();
$favorites = $favoriteModel->getUserFavorites($user_id);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Saved Properties</h4>
            <p class="text-muted small mb-0">Your favorite listings.</p>
        </div>
        <a href="../listings.php" class="btn btn-primary btn-sm">
            <i class="bi bi-search"></i> Browse More
        </a>
    </div>

    <?php if(count($favorites) > 0): ?>
        <div class="row g-4">
            <?php foreach($favorites as $property): ?>
                <?php 
                    // Fetch images for thumbnail
                    $propertyModel = new Property();
                    $images = $propertyModel->getImages($property['id']);
                    $main_image = 'https://placehold.co/600x400?text=No+Image';
                    if(count($images) > 0) {
                        $main_image = '../' . $images[0]['image_path'];
                    }
                ?>
                <div class="col-md-6 col-lg-4" id="fav-card-<?php echo $property['id']; ?>">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="position-relative" style="height: 200px; overflow: hidden;">
                            <a href="../property_details.php?id=<?php echo $property['id']; ?>">
                                <img src="<?php echo $main_image; ?>" class="card-img-top w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($property['title']); ?>">
                            </a>
                            <span class="position-absolute top-0 end-0 badge bg-white text-dark m-3 shadow-sm">
                                <?php echo ucfirst($property['property_type']); ?>
                            </span>
                            <span class="position-absolute bottom-0 start-0 badge bg-primary m-3 shadow-sm">
                                <?php echo $property['currency'] . ' ' . number_format($property['price']); ?>
                                <?php echo ($property['listing_purpose'] ?? 'rent') == 'rent' ? '/ mo' : ''; ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-1">
                                <a href="../property_details.php?id=<?php echo $property['id']; ?>" class="text-dark text-decoration-none">
                                    <?php echo htmlspecialchars($property['title']); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small mb-3">
                                <i class="bi bi-geo-alt-fill text-primary"></i> <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <small class="text-muted">Saved on <?php echo date('M d, Y', strtotime($property['saved_at'])); ?></small>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeFavorite(<?php echo $property['id']; ?>)">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded-3 shadow-sm">
            <div class="mb-3"><i class="bi bi-heart fs-1 text-muted opacity-50"></i></div>
            <h4 class="text-muted">No saved properties yet</h4>
            <p class="text-muted mb-4">Start browsing and save properties you like!</p>
            <a href="../listings.php" class="btn btn-primary">Browse Properties</a>
        </div>
    <?php endif; ?>

</div>

<script>
function removeFavorite(propertyId) {
    if(!confirm('Are you sure you want to remove this property from favorites?')) return;

    fetch('save_property.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'property_id=' + propertyId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove card from DOM
            const card = document.getElementById('fav-card-' + propertyId);
            if(card) {
                card.remove();
                // Reload if empty to show empty state (optional, or just reload page)
                if(document.querySelectorAll('.col-md-6').length === 0) {
                    location.reload();
                }
            }
        } else {
            alert('Failed to remove.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred.');
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>