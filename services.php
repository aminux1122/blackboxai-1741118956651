<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get all active services grouped by category
$db = Database::getInstance();
$services = $db->query("
    SELECT * FROM services 
    WHERE status = 'active' 
    ORDER BY category, name
")->fetchAll();

// Group services by category
$servicesByCategory = [];
foreach ($services as $service) {
    $servicesByCategory[$service['category']][] = $service;
}

require_once 'includes/header.php';
?>

<!-- Services Hero Section -->
<section class="hero-section text-center" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/services-bg.jpg');">
    <div class="container">
        <h1 class="display-4 mb-4 fade-in"><?php echo translate('our_services'); ?></h1>
        <p class="lead mb-4 fade-in"><?php echo translate('services_description'); ?></p>
        <a href="rdv.php" class="btn btn-primary btn-lg fade-in">
            <i class="fas fa-calendar-alt me-2"></i><?php echo translate('book_appointment'); ?>
        </a>
    </div>
</section>

<!-- Services Categories Section -->
<section class="py-5">
    <div class="container">
        <!-- Category Navigation -->
        <ul class="nav nav-pills mb-5 justify-content-center" id="services-tab" role="tablist">
            <?php 
            $firstCategory = true;
            foreach ($servicesByCategory as $category => $categoryServices): 
            ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $firstCategory ? 'active' : ''; ?>" 
                            id="<?php echo $category; ?>-tab" 
                            data-bs-toggle="pill" 
                            data-bs-target="#<?php echo $category; ?>" 
                            type="button" 
                            role="tab">
                        <?php 
                        $icon = $category === 'homme' ? 'fa-male' : ($category === 'femme' ? 'fa-female' : 'fa-users');
                        ?>
                        <i class="fas <?php echo $icon; ?> me-2"></i>
                        <?php echo translate('category_' . $category); ?>
                    </button>
                </li>
            <?php 
            $firstCategory = false;
            endforeach; 
            ?>
        </ul>

        <!-- Services Content -->
        <div class="tab-content" id="services-tabContent">
            <?php 
            $firstCategory = true;
            foreach ($servicesByCategory as $category => $categoryServices): 
            ?>
                <div class="tab-pane fade <?php echo $firstCategory ? 'show active' : ''; ?>" 
                     id="<?php echo $category; ?>" 
                     role="tabpanel">
                    
                    <div class="row">
                        <?php foreach ($categoryServices as $service): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card service-card h-100">
                                    <?php if ($service['image']): ?>
                                        <img src="assets/images/services/<?php echo htmlspecialchars($service['image']); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($service['name']); ?>">
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($service['name']); ?>
                                        </h5>
                                        
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($service['description']); ?>
                                        </p>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <p class="mb-0">
                                                    <strong><?php echo formatPrice($service['price']); ?></strong>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>
                                                    <?php echo $service['duration']; ?> <?php echo translate('minutes'); ?>
                                                </small>
                                            </div>
                                            
                                            <a href="rdv.php?service=<?php echo $service['id']; ?>" 
                                               class="btn btn-primary">
                                                <i class="fas fa-calendar-plus me-2"></i>
                                                <?php echo translate('book_now'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
            $firstCategory = false;
            endforeach; 
            ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo translate('why_choose_us'); ?></h2>
        
        <div class="row g-4">
            <!-- Professional Team -->
            <div class="col-md-4">
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-user-tie fa-3x text-primary"></i>
                    </div>
                    <h4><?php echo translate('professional_team'); ?></h4>
                    <p class="text-muted">
                        <?php echo translate('professional_team_description'); ?>
                    </p>
                </div>
            </div>

            <!-- Quality Products -->
            <div class="col-md-4">
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-star fa-3x text-primary"></i>
                    </div>
                    <h4><?php echo translate('quality_products'); ?></h4>
                    <p class="text-muted">
                        <?php echo translate('quality_products_description'); ?>
                    </p>
                </div>
            </div>

            <!-- Modern Equipment -->
            <div class="col-md-4">
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-cut fa-3x text-primary"></i>
                    </div>
                    <h4><?php echo translate('modern_equipment'); ?></h4>
                    <p class="text-muted">
                        <?php echo translate('modern_equipment_description'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo translate('client_testimonials'); ?></h2>
        
        <?php
        // Get recent reviews with user information
        $reviews = $db->query("
            SELECT r.*, u.firstname, u.lastname, u.profile_image, s.name as service_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN services s ON r.service_id = s.id
            WHERE r.rating >= 4
            ORDER BY r.created_at DESC
            LIMIT 3
        ")->fetchAll();
        ?>

        <div class="row">
            <?php foreach ($reviews as $review): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="assets/images/profiles/<?php echo $review['profile_image'] ?: 'default.jpg'; ?>" 
                                     class="rounded-circle me-3" 
                                     alt="<?php echo htmlspecialchars($review['firstname']); ?>"
                                     width="60">
                                <div>
                                    <h5 class="mb-0">
                                        <?php echo htmlspecialchars($review['firstname'] . ' ' . substr($review['lastname'], 0, 1) . '.'); ?>
                                    </h5>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($review['service_name']); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <p class="card-text">
                                <?php echo htmlspecialchars($review['comment']); ?>
                            </p>
                            
                            <small class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i>
                                <?php echo formatDate($review['created_at']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h2 class="mb-4"><?php echo translate('ready_to_book'); ?></h2>
        <p class="lead mb-4"><?php echo translate('book_now_description'); ?></p>
        <a href="rdv.php" class="btn btn-lg btn-light">
            <i class="fas fa-calendar-alt me-2"></i><?php echo translate('book_now'); ?>
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
