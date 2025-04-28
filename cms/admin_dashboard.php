<?php
session_start();
require 'db_connect.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin.php');
    exit;
}

// Fetch all services
$stmt = $conn->query("SELECT * FROM services");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch general feedback data
$feedbackStmt = $conn->query("SELECT rating FROM feedback");
$feedbackData = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC);

$sexStmt = $conn->query("SELECT sex, COUNT(*) as count FROM patients GROUP BY sex");
$sexData = $sexStmt->fetchAll(PDO::FETCH_ASSOC);

$appointmentStmt = $conn->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
$appointmentData = $appointmentStmt->fetchAll(PDO::FETCH_ASSOC);// Fetch demographics data for Age
$ageStmt = $conn->query("
    SELECT 
        CASE 
            WHEN age BETWEEN 0 AND 17 THEN 'Pediatric'
            WHEN age BETWEEN 18 AND 59 THEN 'Adult'
            WHEN age BETWEEN 60 AND 100 THEN 'Geriatric'
        END as age_group, 
        COUNT(*) as count 
    FROM patients 
    GROUP BY age_group
");
$ageData = $ageStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch appointment counts
try {
    // Today's appointments
    $todayStmt = $conn->prepare("SELECT COUNT(*) AS count FROM follow_ups WHERE DATE(follow_up_date) = CURDATE()");
    $todayStmt->execute();
    $todayCount = $todayStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Tomorrow's appointments
    $tomorrowStmt = $conn->prepare("SELECT COUNT(*) AS count FROM follow_ups WHERE DATE(follow_up_date) = CURDATE() + INTERVAL 1 DAY");
    $tomorrowStmt->execute();
    $tomorrowCount = $tomorrowStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // This week's appointments
    $weekStmt = $conn->prepare("SELECT COUNT(*) AS count FROM follow_ups WHERE YEARWEEK(follow_up_date, 1) = YEARWEEK(CURDATE(), 1)");
    $weekStmt->execute();
    $weekCount = $weekStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // This month's appointments
    $monthStmt = $conn->prepare("SELECT COUNT(*) AS count FROM follow_ups WHERE MONTH(follow_up_date) = MONTH(CURDATE()) AND YEAR(follow_up_date) = YEAR(CURDATE())");
    $monthStmt->execute();
    $monthCount = $monthStmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $todayCount = $tomorrowCount = $weekCount = $monthCount = 0; // Default to 0 if query fails
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-top">
      <img src="images/Logo.png" alt="Logo" class="logo">
      <a href="admin_dashboard.php" class="icon-wrapper" title="Dashboard"><i class="fas fa-chart-line icon"></i></a>
      <a href="schedule_follow_up.php" class="icon-wrapper" title="Appointments"><i class="fas fa-calendar-alt icon"></i></a>
      <a href="content_management.php" class="icon-wrapper" title="Content Management"><i class="fas fa-file-alt icon"></i></a>
      <a href="users.php" class="icon-wrapper" title="Users"><i class="fas fa-user icon"></i></a>
    </div> 
    <div class="sidebar-bottom">
      <a href="notifications.php" class="icon-wrapper" title="Notifications"><i class="fas fa-bell icon"></i></a>
      <a href="profile.php" class="icon-wrapper" title="Profile"><img src="images/user profile.png" alt="User" class="avatar"></a>
      <a href="logout.php" class="icon-wrapper" title="Logout"><i class="fas fa-sign-out-alt icon"></i></a>
    </div>
</div>

<div class="main-content">
    <h3 class="title">Dashboard</h3>
    <div class= "cards">
    <div class="card">
        <h3>Today</h3>
        <p><?php echo $todayCount; ?> Patient Appointments</p>
    </div>
    <div class="card">
        <h3>Tomorrow</h3>
        <p><?php echo $tomorrowCount; ?> Patient Appointments</p>
    </div>
    <div class="card">
        <h3>This Week</h3>
        <p><?php echo $weekCount; ?> Patient Appointments</p>
    </div>
    <div class="card">
        <h3>This Month</h3>
        <p><?php echo $monthCount; ?> Patient Appointments</p>
    </div>
</div>

  <div class="appointment-section">
    <h2>Recent Appointments</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Patient Number</th><th>Date</th><th>Time</th><th>Treatment</th><th>Type</th><th>Status</th><th>Patient</th><th>Reason</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>1</td><td>25-0101</td><td>Sat, Apr 12</td><td>10:30 AM</td><td>Cleaning</td><td>Follow-Up</td><td><span class="status pending">Pending</span></td><td>Angel Cudalenci</td><td>-</td></tr>
        <tr><td>2</td><td>25-0102</td><td>Sat, Apr 12</td><td>12:30 PM</td><td>Flexible Denture</td><td>Re-schedule</td><td><span class="status pending">Pending</span></td><td>Alexi Joy Carpio</td><td>Out of the Country</td></tr>
        <tr><td>3</td><td>25-0103</td><td>Sat, Apr 12</td><td>2:30 PM</td><td>Cleaning</td><td>Follow-Up</td><td><span class="status confirmed">Confirmed</span></td><td>Jace Cuaderno</td><td>-</td></tr>
        <tr><td>4</td><td>25-0104</td><td>Sat, Apr 12</td><td>12:00 PM</td><td>LS Plastic Denture</td><td>Follow-Up</td><td><span class="status completed">Completed</span></td><td>John Rey Laticanero</td><td>-</td></tr>
      </tbody>
    </table>
  </div>

  <div class="chart">
    
    <div class="chart-box" style="margin-top: 20px;">
    <h3>User Demographics: Sex</h3>
    <canvas id="sexChart"></canvas>
    </div>
    <div class="chart-box1" style="margin-top: 20px;">
    <h3>User Demographics: Age</h3>
    <canvas id="ageChart"></canvas>
    </div>
  </div>

  <div class="chart-box2" style="margin-top: 20px;">
    <h3>Service Feedback</h3>
    <canvas id="feedbackChart"></canvas>
  </div>
</div>

<script>
 // Render the Sex pie chart
const sexCtx = document.getElementById('sexChart').getContext('2d');
new Chart(sexCtx, {
  type: 'pie',
  data: {
    labels: ['Male', 'Female'],
    datasets: [{
      data: [28.6, 71.4], // Example data
      backgroundColor: ['#41b8d5', '#6ce5e8'],
      borderColor: ['#2a8ca1', '#4db8c1'],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          font: {
            size: 14
          }
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `${context.label}: ${context.raw}%`;
          }
        }
      }
    }
  }
});

// Render the Age pie chart
const ageCtx = document.getElementById('ageChart').getContext('2d');
new Chart(ageCtx, {
  type: 'pie',
  data: {
    labels: ['Pediatric', 'Adult', 'Geriatric'],
    datasets: [{
      data: [8.3, 75, 16.7], // Example data
      backgroundColor: ['#6ce5e8', '#41b8d5', '#2d8bba'],
      borderColor: ['#4db8c1', '#2a8ca1', '#1f6e8a'],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          font: {
            size: 14
          }
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `${context.label}: ${context.raw}%`;
          }
        }
      }
    }
  }
});

// Render the Service Feedback bar chart
const feedbackCtx = document.getElementById('feedbackChart').getContext('2d');
new Chart(feedbackCtx, {
  type: 'bar',
  data: {
    labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
    datasets: [{
      label: 'Number of Ratings',
      data: [10, 8, 2, 1, 1], // Example data
      backgroundColor: ['#2d8bba', '#41b8d5', '#6ce5e8', '#a3e4f1', '#d9f7fc'],
      borderColor: ['#1f6e8a', '#2a8ca1', '#4db8c1', '#7fd0e0', '#bcecf5'],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: false
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `${context.label}: ${context.raw}`;
          }
        }
      }
    },
    scales: {
      x: {
        title: {
          display: true,
          text: 'Rating Levels',
          font: {
            size: 14
          }
        }
      },
      y: {
        beginAtZero: true,
        title: {
          display: true,
          text: 'Number of Ratings',
          font: {
            size: 14
          }
        }
      }
    }
  }
});
</script>
</body>
</html>