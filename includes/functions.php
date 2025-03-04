<?php
require_once 'config.php';
require_once 'db.php';

// Security Functions
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function verifyToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isEmployee() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'employee';
}

// Image Handling Functions
function uploadImage($file, $destination) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Type de fichier non autorisé');
    }

    if ($file['size'] > $maxSize) {
        throw new Exception('Fichier trop volumineux');
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = UPLOADS_PATH . '/' . $destination . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Erreur lors du téléchargement');
    }

    return $fileName;
}

// Date and Time Functions
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

function formatTime($time, $format = 'H:i') {
    return date($format, strtotime($time));
}

// Price Formatting
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' MAD';
}

// Translation Function
function translate($key) {
    global $translations;
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : DEFAULT_LANG;
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}

// Notification Functions
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Pagination Function
function paginate($total, $per_page, $current_page) {
    $total_pages = ceil($total / $per_page);
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);

    return [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'start' => $start,
        'end' => $end
    ];
}

// Email Function
function sendEmail($to, $subject, $message) {
    require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom(ADMIN_EMAIL, SITE_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Logging Function
function logAction($action, $details = null) {
    if (!isLoggedIn()) return false;

    $db = Database::getInstance();
    $sql = "INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
    $params = [
        $_SESSION['user_id'],
        $action,
        $details,
        $_SERVER['REMOTE_ADDR']
    ];

    try {
        $db->query($sql, $params);
        return true;
    } catch (Exception $e) {
        error_log("Logging Error: {$e->getMessage()}");
        return false;
    }
}
