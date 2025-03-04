<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$error = '';
$success = '';
$formData = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'city' => ''
];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and clean form data
    foreach ($formData as $key => $value) {
        $formData[$key] = cleanInput($_POST[$key] ?? '');
    }
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate form data
    if (empty($formData['firstname']) || empty($formData['lastname']) || 
        empty($formData['email']) || empty($password)) {
        $error = translate('required_fields_missing');
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = translate('invalid_email');
    } elseif (strlen($password) < 8) {
        $error = translate('password_too_short');
    } elseif ($password !== $confirmPassword) {
        $error = translate('passwords_dont_match');
    } else {
        $db = Database::getInstance();
        
        // Check if email already exists
        $stmt = $db->query("SELECT id FROM users WHERE email = ?", [$formData['email']]);
        if ($stmt->fetch()) {
            $error = translate('email_already_exists');
        } else {
            try {
                // Begin transaction
                $db->beginTransaction();

                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $sql = "INSERT INTO users (firstname, lastname, email, password, phone, address, city, role, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'client', 'active')";
                $params = [
                    $formData['firstname'],
                    $formData['lastname'],
                    $formData['email'],
                    $hashedPassword,
                    $formData['phone'],
                    $formData['address'],
                    $formData['city']
                ];
                
                $db->query($sql, $params);
                $userId = $db->lastInsertId();

                // Handle profile image upload if provided
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $imageName = uploadImage($_FILES['profile_image'], 'profiles');
                    $db->query("UPDATE users SET profile_image = ? WHERE id = ?", [$imageName, $userId]);
                }

                // Commit transaction
                $db->commit();

                // Log the registration
                logAction('user_registration', "New user registered: {$formData['email']}");

                // Set success message
                $success = translate('registration_successful');

                // Clear form data
                $formData = array_fill_keys(array_keys($formData), '');

                // Send welcome email
                $emailContent = sprintf(
                    translate('welcome_email_template'),
                    $formData['firstname'],
                    SITE_NAME
                );
                sendEmail($formData['email'], translate('welcome_email_subject'), $emailContent);

            } catch (Exception $e) {
                // Rollback transaction on error
                $db->rollback();
                $error = translate('registration_failed');
                error_log("Registration Error: " . $e->getMessage());
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4"><?php echo translate('register'); ?></h1>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i><?php echo translate('login_now'); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" enctype="multipart/form-data" id="register-form">
                            <div class="row">
                                <!-- First Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="firstname" class="form-label">
                                        <i class="fas fa-user me-2"></i><?php echo translate('firstname'); ?>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="firstname" 
                                           name="firstname" 
                                           value="<?php echo htmlspecialchars($formData['firstname']); ?>" 
                                           required>
                                </div>

                                <!-- Last Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="lastname" class="form-label">
                                        <i class="fas fa-user me-2"></i><?php echo translate('lastname'); ?>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="lastname" 
                                           name="lastname" 
                                           value="<?php echo htmlspecialchars($formData['lastname']); ?>" 
                                           required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i><?php echo translate('email'); ?>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($formData['email']); ?>" 
                                       required>
                            </div>

                            <!-- Password -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i><?php echo translate('password'); ?>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="password" 
                                               required>
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                id="toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i><?php echo translate('confirm_password'); ?>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               required>
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                id="toggle-confirm-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-2"></i><?php echo translate('phone'); ?>
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo htmlspecialchars($formData['phone']); ?>">
                            </div>

                            <!-- Address -->
                            <div class="mb-3">
                                <label for="address" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo translate('address'); ?>
                                </label>
                                <textarea class="form-control" 
                                          id="address" 
                                          name="address" 
                                          rows="2"><?php echo htmlspecialchars($formData['address']); ?></textarea>
                            </div>

                            <!-- City -->
                            <div class="mb-3">
                                <label for="city" class="form-label">
                                    <i class="fas fa-city me-2"></i><?php echo translate('city'); ?>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="city" 
                                       name="city" 
                                       value="<?php echo htmlspecialchars($formData['city']); ?>">
                            </div>

                            <!-- Profile Image -->
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">
                                    <i class="fas fa-image me-2"></i><?php echo translate('profile_image'); ?>
                                </label>
                                <input type="file" 
                                       class="form-control" 
                                       id="profile_image" 
                                       name="profile_image" 
                                       accept="image/*">
                                <div id="image-preview" class="mt-2 text-center"></div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="terms" 
                                       name="terms" 
                                       required>
                                <label class="form-check-label" for="terms">
                                    <?php echo translate('accept_terms'); ?>
                                    <a href="terms.php" target="_blank"><?php echo translate('terms_conditions'); ?></a>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i><?php echo translate('register'); ?>
                                </button>
                            </div>
                        </form>

                        <!-- Login Link -->
                        <div class="text-center mt-4">
                            <p>
                                <?php echo translate('already_have_account'); ?> 
                                <a href="login.php" class="text-decoration-none">
                                    <?php echo translate('login_here'); ?>
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    function setupPasswordToggle(inputId, toggleId) {
        const input = document.getElementById(inputId);
        const toggle = document.getElementById(toggleId);

        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    setupPasswordToggle('password', 'toggle-password');
    setupPasswordToggle('confirm_password', 'toggle-confirm-password');

    // Image preview
    const profileImage = document.getElementById('profile_image');
    const imagePreview = document.getElementById('image-preview');

    profileImage.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.innerHTML = `
                    <img src="${e.target.result}" 
                         alt="Profile Preview" 
                         class="img-thumbnail" 
                         style="max-width: 200px;">`;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Form validation
    const form = document.getElementById('register-form');
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: '<?php echo translate("error"); ?>',
                text: '<?php echo translate("passwords_dont_match"); ?>'
            });
        }

        if (password.length < 8) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: '<?php echo translate("error"); ?>',
                text: '<?php echo translate("password_too_short"); ?>'
            });
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
