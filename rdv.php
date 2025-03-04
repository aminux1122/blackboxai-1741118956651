<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', translate('login_required_appointment'));
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Get selected service if provided
$selectedServiceId = isset($_GET['service']) ? (int)$_GET['service'] : null;

// Get all active services
$services = $db->query("
    SELECT * FROM services 
    WHERE status = 'active' 
    ORDER BY category, name
")->fetchAll();

// Get all active employees
$employees = $db->query("
    SELECT u.*, e.speciality, e.working_hours 
    FROM users u 
    JOIN employees e ON u.id = e.user_id 
    WHERE u.status = 'active' AND u.role = 'employee'
    ORDER BY u.firstname
")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = (int)$_POST['service_id'];
    $employeeId = (int)$_POST['employee_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $notes = cleanInput($_POST['notes'] ?? '');

    try {
        // Validate appointment time
        $appointmentDateTime = new DateTime("$date $time");
        $now = new DateTime();

        if ($appointmentDateTime <= $now) {
            throw new Exception(translate('invalid_appointment_time'));
        }

        // Check if slot is available
        $stmt = $db->query("
            SELECT COUNT(*) as count 
            FROM appointments 
            WHERE employee_id = ? 
            AND appointment_date = ? 
            AND appointment_time = ? 
            AND status != 'cancelled'
        ", [$employeeId, $date, $time]);
        
        if ($stmt->fetch()['count'] > 0) {
            throw new Exception(translate('slot_not_available'));
        }

        // Create appointment
        $db->query("
            INSERT INTO appointments (client_id, employee_id, service_id, appointment_date, appointment_time, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ", [$_SESSION['user_id'], $employeeId, $serviceId, $date, $time, $notes]);

        // Send confirmation email
        $service = $db->query("SELECT name FROM services WHERE id = ?", [$serviceId])->fetch();
        $employee = $db->query("SELECT firstname, lastname FROM users WHERE id = ?", [$employeeId])->fetch();

        $emailContent = sprintf(
            translate('appointment_confirmation_email'),
            $_SESSION['user_firstname'],
            $service['name'],
            $employee['firstname'] . ' ' . $employee['lastname'],
            formatDate($date),
            $time
        );

        sendEmail($_SESSION['user_email'], translate('appointment_confirmation_subject'), $emailContent);

        // Set success message
        setFlashMessage('success', translate('appointment_booked_successfully'));
        header('Location: profile.php');
        exit;

    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}

// Additional CSS for calendar
$additional_css = ['fullcalendar.min.css'];

require_once 'includes/header.php';
?>

<!-- Appointment Booking Section -->
<div class="container py-5">
    <div class="row">
        <!-- Booking Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title mb-4">
                        <i class="fas fa-calendar-alt me-2"></i><?php echo translate('book_appointment'); ?>
                    </h2>

                    <form method="POST" action="" id="appointment-form">
                        <!-- Service Selection -->
                        <div class="mb-4">
                            <label for="service_id" class="form-label">
                                <i class="fas fa-cut me-2"></i><?php echo translate('select_service'); ?>
                            </label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value=""><?php echo translate('choose_service'); ?></option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>"
                                            data-duration="<?php echo $service['duration']; ?>"
                                            data-price="<?php echo $service['price']; ?>"
                                            <?php echo $selectedServiceId == $service['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($service['name']); ?> 
                                        (<?php echo formatPrice($service['price']); ?> - 
                                        <?php echo $service['duration']; ?> <?php echo translate('minutes'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Employee Selection -->
                        <div class="mb-4">
                            <label for="employee_id" class="form-label">
                                <i class="fas fa-user-tie me-2"></i><?php echo translate('select_employee'); ?>
                            </label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value=""><?php echo translate('choose_employee'); ?></option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee['id']; ?>"
                                            data-working-hours='<?php echo htmlspecialchars($employee['working_hours']); ?>'>
                                        <?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?> 
                                        (<?php echo htmlspecialchars($employee['speciality']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Date Selection -->
                        <div class="mb-4">
                            <label for="appointment_date" class="form-label">
                                <i class="fas fa-calendar me-2"></i><?php echo translate('select_date'); ?>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="appointment_date" 
                                   name="date" 
                                   required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <!-- Time Slots -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-clock me-2"></i><?php echo translate('select_time'); ?>
                            </label>
                            <div id="time-slots" class="d-flex flex-wrap gap-2">
                                <!-- Time slots will be loaded dynamically -->
                            </div>
                            <input type="hidden" id="appointment_time" name="time" required>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-comment me-2"></i><?php echo translate('notes'); ?>
                            </label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="<?php echo translate('notes_placeholder'); ?>"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-calendar-check me-2"></i><?php echo translate('confirm_appointment'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Calendar Preview -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">
                        <i class="fas fa-calendar me-2"></i><?php echo translate('availability'); ?>
                    </h3>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service_id');
    const employeeSelect = document.getElementById('employee_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSlotsDiv = document.getElementById('time-slots');
    const timeInput = document.getElementById('appointment_time');
    const form = document.getElementById('appointment-form');

    // Initialize calendar
    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        selectable: true,
        select: function(info) {
            dateInput.value = info.startStr;
            loadTimeSlots();
        }
    });
    calendar.render();

    // Load available time slots
    function loadTimeSlots() {
        const serviceId = serviceSelect.value;
        const employeeId = employeeSelect.value;
        const date = dateInput.value;

        if (!serviceId || !employeeId || !date) return;

        // Clear previous slots
        timeSlotsDiv.innerHTML = '';
        timeInput.value = '';

        // Get employee working hours
        const workingHours = JSON.parse(employeeSelect.options[employeeSelect.selectedIndex].dataset.workingHours || '{}');
        const serviceDuration = parseInt(serviceSelect.options[serviceSelect.selectedIndex].dataset.duration);

        // Get booked slots
        fetch(`ajax/appointments.php?action=get_booked_slots&employee_id=${employeeId}&date=${date}`)
            .then(response => response.json())
            .then(bookedSlots => {
                const dayOfWeek = new Date(date).getDay();
                const dayHours = workingHours[dayOfWeek] || { start: '09:00', end: '18:00' };

                // Generate time slots
                let currentTime = new Date(`${date} ${dayHours.start}`);
                const endTime = new Date(`${date} ${dayHours.end}`);

                while (currentTime < endTime) {
                    const timeSlot = currentTime.toTimeString().substring(0, 5);
                    
                    if (!bookedSlots.includes(timeSlot)) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'btn btn-outline-primary';
                        button.textContent = timeSlot;
                        button.onclick = () => selectTimeSlot(timeSlot);
                        timeSlotsDiv.appendChild(button);
                    }

                    // Add service duration minutes
                    currentTime.setMinutes(currentTime.getMinutes() + serviceDuration);
                }
            });
    }

    function selectTimeSlot(time) {
        // Remove active class from all buttons
        timeSlotsDiv.querySelectorAll('.btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Add active class to selected button
        const selectedButton = Array.from(timeSlotsDiv.querySelectorAll('.btn'))
            .find(btn => btn.textContent === time);
        if (selectedButton) {
            selectedButton.classList.add('active');
            timeInput.value = time;
        }
    }

    // Event listeners
    serviceSelect.addEventListener('change', loadTimeSlots);
    employeeSelect.addEventListener('change', loadTimeSlots);
    dateInput.addEventListener('change', loadTimeSlots);

    // Form validation
    form.addEventListener('submit', function(e) {
        if (!timeInput.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: '<?php echo translate("error"); ?>',
                text: '<?php echo translate("select_time_slot"); ?>'
            });
        }
    });

    // Load initial time slots if service is pre-selected
    if (serviceSelect.value) {
        loadTimeSlots();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
