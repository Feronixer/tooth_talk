<?php
session_start();
require 'db_connect.php';

// Redirect to login if not authenticated as a patient
if (!isset($_SESSION['patient_id'])) {
    echo "<p>Your session has expired. Please <a href='patient_login.php'>log in again</a>.</p>";
    exit;
}

try {
    // Fetch patient details
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['patient_id']);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        // Patient not found
        echo "<p>Patient not found. Please <a href='patient_login.php'>log in again</a> or contact support.</p>";
        exit;
    }

    // Fetch follow-up data
    $stmt = $conn->prepare("SELECT * FROM follow_ups WHERE patient_id = :patient_id ORDER BY follow_up_date ASC");
    $stmt->bindParam(':patient_id', $_SESSION['patient_id']);
    $stmt->execute();
    $follow_ups = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "<p>An error occurred while fetching your data. Please try again later.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToothTalk Appointment Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
   <link rel="stylesheet" href="patient.css">
</head>
</head>
<body>
  <header class="header">
    <div class="logo">
      <img src="images/logo Nav Bar.png" alt="Logo">
      <div>
        <strong>ToothTalk</strong><br />
        <small>JValera Dental Clinic</small>
      </div>
    </div>
    <nav>
      <a href="#">Clinic</a>
      <a href="#">Announcement</a>
      <a href="#">About Us</a>
      <a href="#" class="active">Calendar</a>
      <a href="#">Record</a>
    </nav>
    <div class="icons">
      <img src="https://cdn-icons-png.flaticon.com/512/3388/3388823.png" alt="Bot"/>
      <img src="https://cdn-icons-png.flaticon.com/512/1827/1827392.png" alt="Bell"/>
      <img src="https://cdn-icons-png.flaticon.com/512/4140/4140048.png" alt="User"/>
    </div>
  </header>

  <div class="main">
    <aside class="left-panel">
      <h3>ToothTalk: Appointment Tracker</h3>
      <div class="mini-calendar">
        <div class="calendar-header">
          <span><strong>April 2025</strong></span>
        </div>
        <div class="calendar-grid" id="mini-calendar">
          <div class="day">S</div><div class="day">M</div><div class="day">T</div><div class="day">W</div><div class="day">T</div><div class="day">F</div><div class="day">S</div>
        </div>
      </div>
      <div class="description-box">
        <strong>Description:</strong>
        <p>You have a follow-up appointment for this day.</p>
        <ul>
          <li><strong>Nature of appointment:</strong><br />- Flexible dentures</li>
        </ul>
      </div>
    </aside>

    <section class="calendar-panel">
      <div id="calendar"></div>

      <div class="reschedule-form">
        <label>
          <input type="checkbox" id="reschedule-toggle" /> Request for Reschedule?
        </label>
        <label for="reason">State the Reason:</label>
        <input type="text" id="reason" placeholder="Enter your reason..." />

        <div class="row">
          <div>
            <label for="new-date">Select Date:</label>
            <input type="date" id="new-date" />
          </div>
          <div>
            <label for="new-time">Select Time:</label>
            <input type="time" id="new-time" />
          </div>
        </div>

        <button type="submit">Submit</button>
      </div>
    </section>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: '2025-04-01',
        height: 500,
        events: [
          {
            title: 'Flexible Dentures Appointment',
            start: '2025-04-09',
            display: 'block',
            backgroundColor: '#007b8f'
          }
        ]
      });
      calendar.render();

      // Populate mini calendar days for April 2025
      const miniCalendar = document.getElementById('mini-calendar');
      const daysInApril = 30;
      const startDay = 2; // April 1, 2025 is Tuesday
      for (let i = 0; i < startDay; i++) {
        miniCalendar.innerHTML += `<div></div>`;
      }
      for (let i = 1; i <= daysInApril; i++) {
        const day = document.createElement('div');
        day.className = 'day';
        day.textContent = i;
        if (i === 9) day.classList.add('selected');
        day.addEventListener('click', () => {
          document.querySelectorAll('.mini-calendar .day').forEach(d => d.classList.remove('selected'));
          day.classList.add('selected');
        });
        miniCalendar.appendChild(day);
      }
    });
  </script>
</body>
</html>