<?php
include("connect.php");

$showError = false; // Variable to track whether to show the error messages

if (isset($_POST['Register'])) {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $class = $_POST['class'];
  $syllabus = $_POST['syllabus'];

  // Validate email format using filter_var
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $showError = true;
    $emailError = "Invalid email format!";
  }

  // Validate password format
  if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{6,20}$/', $password)) {
    $showError = true;
    $passwordError = "Password must contain at least one lowercase letter, one uppercase letter, one number, one special character, and be 6-20 characters long!";
  }

  // Check if email already exists
  $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $showError = true;
    $emailError = "Email already exists!";
  }

  // Check if username already exists
  $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $showError = true;
    $usernameError = "Username already exists!";
  }

  // If there are no errors, proceed with registration
  if (!$showError) {
    $stmt = $conn->prepare("INSERT INTO user (username, email, password, class, syllabus) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $class, $syllabus);

    if ($stmt->execute()) {
      echo "Registration successful!";
      header("Location: login.php");
      exit();
    } else {
      echo "Error: " . $stmt->error;
    }

    $stmt->close();
  }
}

// Function to generate a unique ID
function generateUniqueID($conn) {
  $stmt = $conn->prepare("SELECT MAX(id) as max_id FROM user");
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $max_id = $row['max_id'];
  $new_id = $max_id + 1;
  return $new_id;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <title>Smart Judy - Register</title>
  <link rel="stylesheet" href="register.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <div class="container">
    <div class="title">Registration</div>
    <div class="content">
      <form action="register.php" method="POST">
        <div class="user-details">
          <div class="input-box">
            <span class="details">Username</span>
            <input type="text" name="username" placeholder="Enter your username" required>
            <?php if (isset($usernameError)) { echo "<span style='color: red;'>$usernameError</span>"; } ?>
          </div>
          <div class="input-box">
            <span class="details">Email</span>
            <input type="email" name="email" placeholder="Enter your email" required>
            <?php if (isset($emailError)) { echo "<span style='color: red;'>$emailError</span>"; } ?>
          </div>
          <div class="input-box">
            <span class="details">Password</span>
            <input type="password" name="password" placeholder="Enter your password" required>
            <?php if (isset($passwordError)) { echo "<span style='color: red;'>$passwordError</span>"; } ?>
          </div>
          <div class="input-box">
            <span class="details">Class</span>
            <select name="class" required>
              <option value="" disabled selected>Select your class</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
              <option value="6">6</option>
              <option value="7">7</option>
              <option value="8">8</option>
              <option value="9">9</option>
              <option value="10">10</option>
            </select>
          </div>
          <div class="input-box">
            <span class="details">Syllabus/Curriculum</span>
            <select name="syllabus" required>
              <option value="" disabled selected>Select your syllabus/curriculum</option>
              <option value="ICSE">ICSE</option>
              <option value="CBSE">CBSE</option>
            </select>
          </div>
        </div>
        <div class="button">
          <input type="submit" name="Register">
        </div>
        <div class="login-link">
          Already a member? <a href="./login.php" target="_blank">Login now</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
