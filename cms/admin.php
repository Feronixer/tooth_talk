<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="admin_login.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php
  session_start();
  require 'db_connect.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $username = trim($_POST['username']);
      $password = trim($_POST['password']);

      $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
      $stmt->bindParam(':username', $username, PDO::PARAM_STR);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($password, $user['password'])) {
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['role'] = $user['role'];
          header('Location: admin_dashboard.php');
          exit;
      } else {
          $error = "Invalid username or password.";
      }
  }
  ?>
  <div class="login-wrapper">
    <div class="login-left">
      <img src="images/Logo.png" alt="Logo" class="logo">
      <h1>ToothTalk</h1>
      <p class="sub">JValera Dental Clinic</p>
      <h2>Admin Portal<br><strong>LOG IN</strong></h2>

      <?php if (isset($error)): ?>
          <p class="error"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>

      <form method="POST">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Login</button>
      </form>

      <div class="forgot">Forgot Password?</div>
    </div>
    <div class="login-right"></div>
  </div>
</body>
</html>
