<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Fetch all follow-ups including reschedule requests
$stmt = $conn->query("
    SELECT * FROM follow_ups 
    ORDER BY 
        CASE 
            WHEN status = 'reschedule_requested' THEN 1 
            ELSE 2 
        END, follow_up_date DESC
");

$follow_ups = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link rel="stylesheet" href="scheduling.css">
</head>
<body>
    <header>
        <h1>Manage Appointments</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="schedule_follow_up.php">Schedule Follow-Up</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- List of Follow-Ups -->
        <section>
            <h2>Scheduled Follow-Ups</h2>
            <?php if (empty($follow_ups)): ?>
                <p>No follow-ups found.</p>
            <?php else: ?>
                <table>
                <thead>
    <tr>
        <th>Patient Name</th>
        <th>Current Appointment Date</th>
        <th>Requested Reschedule Date</th>
        <th>Procedure</th>
        <th>Status</th>
    
    </tr>
            </thead>
            <tbody>
                <?php foreach ($follow_ups as $follow_up): ?>
                <tr>
                    <td><?php echo htmlspecialchars($follow_up['patient_name']); ?></td>
                    <td><?php echo date('M j, Y h:i A', strtotime($follow_up['follow_up_date'])); ?></td>
                    <td>
    <?php if (!empty($follow_up['approved_reschedule_date'])): ?>
        <!-- Show Approved Rescheduled Date -->
        <span><?php echo date('M j, Y h:i A', strtotime($follow_up['approved_reschedule_date'])); ?></span>
    
    <?php elseif ($follow_up['status'] == 'reschedule_requested'): ?>
        <!-- If reschedule is requested but not yet approved -->
        <a href="process_reschedule.php?id=<?php echo $follow_up['id']; ?>&action=approve" class="approve-btn">Approve</a>
        <a href="process_reschedule.php?id=<?php echo $follow_up['id']; ?>&action=deny" class="deny-btn">Deny</a>
    
    <?php else: ?>
        <span>-</span>
    <?php endif; ?>
</td>
                    <td><?php echo htmlspecialchars($follow_up['service_name']); ?></td>
                    <td>
                                <form method="POST" action="update_status.php">
                                    <input type="hidden" name="follow_up_id" value="<?php echo $follow_up['id']; ?>">
                                    <select name="status" class="status-dropdown" onchange="changeColor(this); this.form.submit();">
                                        <option value="pending" <?php echo $follow_up['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $follow_up['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo $follow_up['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $follow_up['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
            

                </tr>
                <?php endforeach; ?>
            </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 JValera Dental Clinic. All rights reserved.</p>
    </footer>
    <script>
        function changeColor(select) {
            select.classList.remove("status-pending", "status-confirmed", "status-completed", "status-cancelled");

            let selectedStatus = select.value;
            if (selectedStatus === "pending") {
                select.classList.add("status-pending");
            } else if (selectedStatus === "confirmed") {
                select.classList.add("status-confirmed");
            } else if (selectedStatus === "completed") {
                select.classList.add("status-completed");
            } else if (selectedStatus === "cancelled") {
                select.classList.add("status-cancelled");
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            let selects = document.querySelectorAll(".status-dropdown");
            selects.forEach(select => changeColor(select));
        });
    </script>
</body>
</html>
