<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get featured services
$db = Database::getInstance();
$featuredServices = $db->query("
    SELECT * FROM services 
    WHERE status = 'active' 
    ORDER BY RAND() 
    LIMIT 6
")->fetchAll();

// Get latest products
$latestProducts = $db->query("
    SELECT * FROM products 
    WHERE status = 'active' 
    ORDER BY created_at DESC 
    LIMIT 4
")->fetchAll();

// Get latest blog posts
$latestPosts = $db->query("
    SELECT bp.*, u.firstname, u.lastname 
    FROM blog_posts bp 
    JOIN users u ON bp.author_id = u.id 
    WHERE bp.status = 'published' 
    ORDER BY bp.created_at DESC 
    LIMIT 3
")->fetchAll();

// Get gallery images
$galleryImages = $db->query("
    SELECT * FROM gallery 
    ORDER BY created_at DESC 
    LIMIT 6
")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 mb-4 fade-in"><?php echo translate('welcome_hero_title'); ?></h1>
        <p class="lead mb-5 fade-in"><?php echo translate('welcome_hero_subtitle'); ?></p>
        <a href="rdv.php" class="btn btn-primary btn-lg me-3 fade-in">
            <i class="fas fa-calendar-alt me-2"></i><?php echo translate('book_appointment'); ?>
        </a>
        <a href="services.php" class="btn btn-outline-light btn-lg fade-in">
            <i class="fas fa-info-circle me-2"></i><?php echo translate('our_services'); ?>
        </a>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo translate('our_services'); ?></h2>
        <div class="row">
            <?php foreach ($featuredServices as $service): ?>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <?php if ($service['image']): ?>
                            <img src="assets/images/services/<?php echo htmlspecialchars($service['image']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($service['name']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                            <p class="card-text">
                                <strong><?php echo formatPrice($service['price']); ?></strong>
                                <small class="text-muted"> - <?php echo $service['duration']; ?> min</small>
                            </p>
                            <a href="rdv.php?service=<?php echo $service['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-2"></i><?php echo translate('book_now'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="services.php" class="btn btn-outline-primary">
                <i class="fas fa-list me-2"></i><?php echo translate('view_all_services'); ?>
            </a>
        </div>
    </div>
</section>

<!-- Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo translate('featured_products'); ?></h2>
        <div class="row">
            <?php foreach ($latestProducts as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card h-100">
                        <?php if ($product['image']): ?>
                            <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                 class="card-img-top product-image" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <p class="card-text">
                                <strong><?php echo formatPrice($product['price']); ?></strong>
                            </p>
                            <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart me-2"></i><?php echo translate('add_to_cart'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="produits.php" class="btn btn-outline-primary">
                <i class="fas fa-store me-2"></i><?php echo translate('visit_shop'); ?>
            </a>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo translate('our_gallery'); ?></h2>
        <div class="row">
            <?php foreach ($galleryImages as $image): ?>
                <div class="col-md-4 mb-4">
                    <div class="gallery-item">
                        <img src="assets/images/gallery/<?php echo htmlspecialchars($image['image']); ?>" 
                             class="img-fluid w-100" 
                             alt="<?php echo htmlspecialchars($image['title']); ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="gallery.php" class="btn btn-outline-primary">
                <i class="fas fa-images me-2"></i><?php echo translate('view_gallery'); ?>
            </a>
        </div>
    </div>
</section>

<!-- Blog Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo translate('latest_blog_posts'); ?></h2>
        <div class="row">
            <?php foreach ($latestPosts as $post): ?>
                <div class="col-md-4 mb-4">
                    <div class="card blog-card h-100">
                        <?php if ($post['image']): ?>
                            <img src="assets/images/blog/<?php echo htmlspecialchars($post['image']); ?>" 
                                 class="card-img-top blog-image" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 150)) . '...'; ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($post['firstname'] . ' ' . $post['lastname']); ?>
                                    <i class="fas fa-calendar ms-3 me-2"></i><?php echo formatDate($post['created_at']); ?>
                                </small>
                            </p>
                            <a href="blog.php?post=<?php echo $post['id']; ?>" class="btn btn-outline-primary">
                                <?php echo translate('read_more'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="blog.php" class="btn btn-outline-primary">
                <i class="fas fa-blog me-2"></i><?php echo translate('view_all_posts'); ?>
            </a>
        </div>
    </div>
</section>

<!-- Appointment CTA Section -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h2 class="mb-4"><?php echo translate('book_appointment_cta'); ?></h2>
        <p class="lead mb-4"><?php echo translate('book_appointment_description'); ?></p>
        <a href="rdv.php" class="btn btn-lg btn-light">
            <i class="fas fa-calendar-alt me-2"></i><?php echo translate('book_now'); ?>
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
