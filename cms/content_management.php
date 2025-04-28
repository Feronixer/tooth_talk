<?php
require 'db_connect.php';

// Fetch all services from the database
$stmt = $conn->query("SELECT * FROM services");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Content Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="content_management.css" />
  <script>
    // JavaScript for inline editing
    function enableEditing(element) {
      element.contentEditable = true;
      element.focus();
      element.style.border = "1px solid #ccc";
    }

    function saveChanges(element, type, id) {
  element.contentEditable = false;
  element.style.border = "none";
  const updatedValue = element.innerText.trim(); // Trim to remove extra spaces

  // Send updated data to the server
  fetch('update_content.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ type, id, value: updatedValue })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Changes saved successfully!');
      } else {
        alert('Failed to save changes: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while saving changes.');
    });
}
  </script>
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

  <div class="content">
    <div class="section">
      <h2>Update Announcement</h2>
      <div class="announcement">
        <img src="images/announcement.jpg" alt="Announcement Photo">
        <div class="announcement-message">
          <!-- Editable Title -->
          <h3 contenteditable="false" 
              onfocusout="saveChanges(this, 'announcement_title', 1)" 
              onclick="enableEditing(this)">
            IT'S A CELEBRATION!
          </h3>
          <!-- Editable Message -->
          <p contenteditable="false" 
             onfocusout="saveChanges(this, 'announcement_message', 1)" 
             onclick="enableEditing(this)">
            We are closed on April 9, 2025. Clinical Operations Resume on April 10, 2025
          </p>
        </div>
      </div>
    </div>

   <!-- Editable Services Section -->
<div class="section">
  <h2>Service <a href="add_service.php" class="btn add">Add Service</a></h2>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>ID</th><th>Icon</th><th>Service</th><th>Price</th><th>Description</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($services as $service): ?>
          <tr id="service-<?php echo $service['id']; ?>">
            <td><?php echo $service['id']; ?></td>
            <td><img src="<?php echo $service['image_url']; ?>" alt="Service Icon" width="50"></td>
            <td contenteditable="false"><?php echo $service['name']; ?></td>
            <td contenteditable="false"><?php echo $service['price']; ?></td>
            <td contenteditable="false"><?php echo $service['description']; ?></td>
            <td>
              <button class="btn edit" onclick="editService(<?php echo $service['id']; ?>)">Edit</button>
              <button class="btn save" onclick="saveService(<?php echo $service['id']; ?>)" style="display: none;">Save</button>
              <button class="btn delete" onclick="deleteService(<?php echo $service['id']; ?>)">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  // Enable editing for a specific service row
  function editService(id) {
    const row = document.getElementById(`service-${id}`);
    const cells = row.querySelectorAll('td[contenteditable]');
    const editButton = row.querySelector('.btn.edit');
    const saveButton = row.querySelector('.btn.save');

    // Enable contenteditable for all editable cells
    cells.forEach(cell => {
      cell.contentEditable = true;
      cell.style.border = "1px solid #ccc";
    });

    // Show the Save button and hide the Edit button
    editButton.style.display = 'none';
    saveButton.style.display = 'inline-block';
  }

  // Save changes for a specific service row
  function saveService(id) {
  const row = document.getElementById(`service-${id}`);
  const cells = row.querySelectorAll('td[contenteditable]');
  const editButton = row.querySelector('.btn.edit');
  const saveButton = row.querySelector('.btn.save');

  // Collect updated values
  const updatedData = {
    id: id,
    name: cells[0].innerText.trim(),
    price: cells[1].innerText.trim(),
    description: cells[2].innerText.trim()
  };

  // Send updated data to the server
  fetch('update_content.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      type: 'update_service',
      id: updatedData.id,
      name: updatedData.name,
      price: updatedData.price,
      description: updatedData.description
    })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Service updated successfully!');
      } else {
        alert('Failed to update service: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while updating the service.');
    });

  // Disable contenteditable for all editable cells
  cells.forEach(cell => {
    cell.contentEditable = false;
    cell.style.border = "none";
  });

  // Show the Edit button and hide the Save button
  editButton.style.display = 'inline-block';
  saveButton.style.display = 'none';
}
function saveAnnouncement(id, field, value) {
  fetch('update_content.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      type: field === 'title' ? 'announcement_title' : 'announcement_message',
      id: id,
      value: value
    })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Announcement updated successfully!');
      } else {
        alert('Failed to save changes: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while saving the announcement.');
    });
}

  // Delete a service
  function deleteService(id) {
  if (confirm('Are you sure you want to delete this service?')) {
    fetch('update_content.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type: 'delete_service', id: id })
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Service deleted successfully!');
          location.reload(); // Reload the page to reflect changes
        } else {
          alert('Failed to delete service: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the service.');
      });
  }
}
</script>
</div>
  </div>
</body>
</html>