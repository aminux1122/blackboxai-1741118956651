<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $subject = cleanInput($_POST['subject']);
    $message = cleanInput($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = translate('all_fields_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = translate('invalid_email');
    } else {
        try {
            // Save message to database
            $db = Database::getInstance();
            $db->query("
                INSERT INTO contact_messages (name, email, subject, message) 
                VALUES (?, ?, ?, ?)
            ", [$name, $email, $subject, $message]);

            // Send notification email to admin
            $adminEmailContent = sprintf(
                translate('new_contact_message_email'),
                $name,
                $email,
                $subject,
                $message
            );
            sendEmail(ADMIN_EMAIL, translate('new_contact_message_subject'), $adminEmailContent);

            // Send confirmation email to user
            $userEmailContent = sprintf(
                translate('contact_confirmation_email'),
                $name
            );
            sendEmail($email, translate('contact_confirmation_subject'), $userEmailContent);

            $success = translate('message_sent_successfully');

            // Log the contact message
            logAction('contact_message', "New contact message from: {$email}");

        } catch (Exception $e) {
            $error = translate('message_send_failed');
            error_log("Contact Form Error: " . $e->getMessage());
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Contact Hero Section -->
<section class="hero-section text-center" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/contact-bg.jpg');">
    <div class="container">
        <h1 class="display-4 mb-4 fade-in"><?php echo translate('contact_us'); ?></h1>
        <p class="lead mb-4 fade-in"><?php echo translate('contact_description'); ?></p>
    </div>
</section>

<!-- Contact Information Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Information -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-info-circle me-2 text-primary"></i><?php echo translate('contact_info'); ?>
                        </h3>
                        
                        <div class="mb-4">
                            <h5><i class="fas fa-map-marker-alt me-2 text-primary"></i><?php echo translate('address'); ?></h5>
                            <p class="text-muted">123 Rue Mohammed V, Casablanca, Maroc</p>
                        </div>
                        
                        <div class="mb-4">
                            <h5><i class="fas fa-phone me-2 text-primary"></i><?php echo translate('phone'); ?></h5>
                            <p class="text-muted">
                                <a href="tel:+212522123456" class="text-decoration-none">+212 522 123 456</a>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h5><i class="fas fa-envelope me-2 text-primary"></i><?php echo translate('email'); ?></h5>
                            <p class="text-muted">
                                <a href="mailto:contact@salon-beaute.ma" class="text-decoration-none">contact@salon-beaute.ma</a>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h5><i class="fas fa-clock me-2 text-primary"></i><?php echo translate('opening_hours'); ?></h5>
                            <ul class="list-unstyled text-muted">
                                <li>
                                    <i class="fas fa-chevron-right me-2"></i>
                                    <?php echo translate('monday_friday'); ?>: 9:00 - 20:00
                                </li>
                                <li>
                                    <i class="fas fa-chevron-right me-2"></i>
                                    <?php echo translate('saturday'); ?>: 9:00 - 18:00
                                </li>
                                <li>
                                    <i class="fas fa-chevron-right me-2"></i>
                                    <?php echo translate('sunday'); ?>: <?php echo translate('closed'); ?>
                                </li>
                            </ul>
                        </div>
                        
                        <div>
                            <h5><i class="fas fa-share-alt me-2 text-primary"></i><?php echo translate('follow_us'); ?></h5>
                            <div class="social-links">
                                <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-envelope me-2 text-primary"></i><?php echo translate('send_message'); ?>
                        </h3>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="contact-form">
                            <div class="row">
                                <!-- Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-2"></i><?php echo translate('name'); ?>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           required>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i><?php echo translate('email'); ?>
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           required>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="mb-3">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-heading me-2"></i><?php echo translate('subject'); ?>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="subject" 
                                       name="subject" 
                                       required>
                            </div>

                            <!-- Message -->
                            <div class="mb-4">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment me-2"></i><?php echo translate('message'); ?>
                                </label>
                                <textarea class="form-control" 
                                          id="message" 
                                          name="message" 
                                          rows="5" 
                                          required></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i><?php echo translate('send_message'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-4">
            <i class="fas fa-map-marked-alt me-2"></i><?php echo translate('find_us'); ?>
        </h3>
        <div class="ratio ratio-21x9">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.846447471348!2d-7.632492684770799!3d33.592882880730445!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda7d282e9e3b8cf%3A0x9d5b4ecf4b5c2199!2sMohammed%20V%20Boulevard%2C%20Casablanca%2C%20Morocco!5e0!3m2!1sen!2s!4v1647887642324!5m2!1sen!2s" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');

    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const subject = document.getElementById('subject').value.trim();
        const message = document.getElementById('message').value.trim();

        if (!name || !email || !subject || !message) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: '<?php echo translate("error"); ?>',
                text: '<?php echo translate("all_fields_required"); ?>'
            });
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
