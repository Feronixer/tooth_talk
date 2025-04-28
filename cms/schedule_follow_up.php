<?php
require 'db_connect.php';

// Fetch patients for the dropdown
try {
    $stmt = $conn->query("SELECT id, full_name FROM patients ORDER BY full_name ASC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $patients = []; // Initialize as an empty array if the query fails
}

// Handle form submission for scheduling a new appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $service_name = $_POST['service_name'];
    $follow_up_date = $_POST['follow_up_date'];

    // Validate 30-minute interval
    $timestamp = strtotime($follow_up_date);
    if (date('i', $timestamp) % 30 !== 0) {
        echo "<script>alert('Error: Follow-up time must be in 30-minute intervals.'); window.history.back();</script>";
        exit;
    }

    try {
        // Fetch patient details
        $stmt = $conn->prepare("SELECT full_name, email, phone FROM patients WHERE id = ?");
        $stmt->execute([$patient_id]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            echo "<p>Patient not found. Please try again.</p>";
            exit;
        }

        $patient_name = $patient['full_name'];
        $patient_email = $patient['email'];
        $patient_phone = $patient['phone'];

        // Insert the follow-up appointment
        $stmt = $conn->prepare("INSERT INTO follow_ups (patient_id, patient_name, patient_email, patient_phone, service_name, follow_up_date, status) 
                                VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$patient_id, $patient_name, $patient_email, $patient_phone, $service_name, $follow_up_date]);
        header('Location: schedule_follow_up.php'); // Refresh the page to show the new appointment
        exit;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo "<p>An error occurred while scheduling the appointment. Please try again later.</p>";
    }
}

// Fetch follow-up appointments for the calendar
try {
    $stmt = $conn->query("SELECT * FROM follow_ups ORDER BY follow_up_date ASC");
    $follow_ups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $follow_ups = []; // Initialize as an empty array if the query fails
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Follow-Up</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="follow-up.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-top">
        <img src="images/Logo.png" alt="Logo" class="logo">
        <a href="admin_dashboard.php" class="icon-wrapper" title="Dashboard"><i class="fas fa-chart-line icon"></i></a>
        <a href=" schedule_follow_up.php" class="icon-wrapper" title="Appointments"><i class="fas fa-calendar-alt icon"></i></a>
        <a href="content_management.php" class="icon-wrapper" title="Content Management"><i class="fas fa-file-alt icon"></i></a>
        <a href="users.php" class="icon-wrapper" title="Users"><i class="fas fa-user icon"></i></a>
    </div>
    <div class="sidebar-bottom">
        <a href="notifications.php" class="icon-wrapper" title="Notifications"><i class="fas fa-bell icon"></i></a>
        <a href="profile.php" class="icon-wrapper" title="Profile"><img src="images/user profile.png" alt="User" class="avatar"></a>
        <a href="logout.php" class="icon-wrapper" title="Logout"><i class="fas fa-sign-out-alt icon"></i></a>
    </div>
</div>
<div class="content">
    <div class="schedule-header">
        <h2>Schedule Follow-Up</h2>
        <p>Manage all follow-up appointments</p>
    </div>

    <div class="schedule-container">
    <!-- Calendar -->
    <div id="calendar"></div>

    <!-- Appointment List -->
    <div class="appointment-listing">
        <h3>Scheduled Appointments</h3>
        <?php foreach ($follow_ups as $follow_up): ?> <!-- Corrected syntax -->
            <div class="appointment-card">
                <strong><?php echo date('h:i A', strtotime($follow_up['follow_up_date'])); ?> - <?php echo htmlspecialchars($follow_up['patient_name']); ?></strong><br>
                <?php echo htmlspecialchars($follow_up['service_name']); ?><br>
                <?php echo htmlspecialchars($follow_up['patient_email']); ?> - <?php echo htmlspecialchars($follow_up['patient_phone']); ?>
            </div>
        <?php endforeach; ?> <!-- Corrected syntax -->
    </div>
</div>

    <!-- Appointment Form Trigger Button -->
    <div class="appointment-form-trigger">
        <button id="openFormButton">Schedule a New Appointment</button>
    </div>

    <!-- Appointment Form (Initially Hidden) -->
    <div class="appointment-form" id="appointmentForm" style="display: none;">
        <h3>Schedule a New Appointment</h3>
        <form method="POST">
            <label for="patient_id">Select Patient:</label>
            <select id="patient_id" name="patient_id" required>
                <option value="" disabled selected>Select a patient</option>
                <?php foreach ($patients as $patient): ?>
                    <option value="<?php echo $patient['id']; ?>"><?php echo htmlspecialchars($patient['full_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="service_name">Service Name:</label>
            <input type="text" id="service_name" name="service_name" required>

            <label for="follow_up_date">Follow-Up Date:</label>
            <input type="datetime-local" id="follow_up_date" name="follow_up_date" required>

            <button type="submit">Schedule Appointment</button>
            <button type="button" id="closeFormButton">Cancel</button>
        </form>
    </div>
</div>

<script>
    // JavaScript to toggle the visibility of the appointment form
    document.getElementById('openFormButton').addEventListener('click', function () {
        document.getElementById('appointmentForm').style.display = 'block';
    });

    document.getElementById('closeFormButton').addEventListener('click', function () {
        document.getElementById('appointmentForm').style.display = 'none';
    });
</script>
<script>
    document.getElementById('follow_up_date').addEventListener('input', function (e) {
        const date = new Date(this.value);
        const minutes = date.getMinutes();

        // Round to the nearest 30-minute interval
        if (minutes % 30 !== 0) {
            alert('Follow-up time must be in 30-minute intervals.');
            this.value = ''; // Clear the invalid input
        }
    });
</script>
<script>
    document.getElementById('follow_up_date').addEventListener('input', function (e) {
        const date = new Date(this.value);
        const minutes = date.getMinutes();

        // Round to the nearest 30-minute interval
        if (minutes % 30 !== 0) {
            alert('Follow-up time must be in 30-minute intervals.');
            this.value = ''; // Clear the invalid input
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: [
            <?php if (!empty($follow_ups)): ?>
                <?php foreach ($follow_ups as $index => $follow_up): ?>
                {
                    title: '<?php echo addslashes($follow_up['service_name']); ?> - <?php echo addslashes($follow_up['patient_name']); ?>',
                    start: '<?php echo date('Y-m-d\TH:i:s', strtotime($follow_up['follow_up_date'])); ?>',
                    backgroundColor: '<?php echo ($follow_up['status'] === 'pending') ? '#f39c12' : ($follow_up['status'] === 'confirmed' ? '#00a65a' : '#007bff'); ?>',
                    borderColor: '<?php echo ($follow_up['status'] === 'pending') ? '#f39c12' : ($follow_up['status'] === 'confirmed' ? '#00a65a' : '#007bff'); ?>'
                }<?php echo $index < count($follow_ups) - 1 ? ',' : ''; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        ]
    });
    calendar.render();
});
</script>
</body>
</html>