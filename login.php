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
$email = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($email) || empty($password)) {
        $error = translate('all_fields_required');
    } else {
        $db = Database::getInstance();
        
        // Get user by email
        $stmt = $db->query("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1", [$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_firstname'] = $user['firstname'];
            $_SESSION['user_lastname'] = $user['lastname'];
            $_SESSION['user_role'] = $user['role'];

            // Update last login timestamp
            $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

            // Set remember me cookie if requested
            if ($remember) {
                $token = generateToken();
                $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                setcookie('remember_token', $token, $expiry, '/');
                
                // Store token in database
                $db->query("UPDATE users SET remember_token = ? WHERE id = ?", [$token, $user['id']]);
            }

            // Log the login action
            logAction('user_login', "User logged in: {$user['email']}");

            // Redirect based on user role
            switch ($user['role']) {
                case 'admin':
                    header('Location: ' . SITE_URL . '/admin/');
                    break;
                case 'employee':
                    header('Location: ' . SITE_URL . '/employee/');
                    break;
                default:
                    header('Location: ' . SITE_URL);
            }
            exit;
        } else {
            $error = translate('invalid_credentials');
            // Log failed login attempt
            logAction('failed_login', "Failed login attempt for email: {$email}");
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4"><?php echo translate('login'); ?></h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="login-form">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i><?php echo translate('email'); ?>
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($email); ?>" 
                                   required 
                                   autofocus>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
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

                        <!-- Remember Me -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="remember" 
                                   name="remember">
                            <label class="form-check-label" for="remember">
                                <?php echo translate('remember_me'); ?>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i><?php echo translate('login'); ?>
                            </button>
                        </div>
                    </form>

                    <!-- Links -->
                    <div class="text-center mt-4">
                        <p class="mb-2">
                            <a href="forgot-password.php" class="text-decoration-none">
                                <i class="fas fa-key me-2"></i><?php echo translate('forgot_password'); ?>
                            </a>
                        </p>
                        <p>
                            <?php echo translate('dont_have_account'); ?> 
                            <a href="register.php" class="text-decoration-none">
                                <?php echo translate('register_now'); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Form validation
    const form = document.getElementById('login-form');
    form.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!email || !password) {
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
