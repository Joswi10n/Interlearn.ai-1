
<?php
session_start();
include 'connect.php';


if (isset($_POST['Login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE username ='$username' AND password ='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();


        $_SESSION['username'] = $username;

        header("Location: courses.php");
        
        
    } else {
        echo "Invalid username or password!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <title>Interlearn - Login</title>
  <link rel="stylesheet" href="register.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <div class="container">
    <div class="title">Login</div>
    <div class="content">
      <form action="login.php" method="POST">
        <div class="user-details">
          <div class="input-box">
            <span class="details">Username</span>
            <input type="text" name="username" placeholder="Enter your username" required>
            <?php if (isset($usernameError)) { echo "<span style='color: red;'>$usernameError</span>"; } ?>
          </div>
          <div class="input-box">
            <span class="details">Password</span>
            <input type="password" name="password" placeholder="Enter your password" required>
            <?php if (isset($passwordError)) { echo "<span style='color: red;'>$passwordError</span>"; } ?>
          </div>
        </div>
        <div class="button">
          <input type="submit" name="Login">
        </div>
        <div class="login-link">
          Not a member? <a href="./register.php" target="_blank">Register now</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>