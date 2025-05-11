<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$dbname = "twinkl_app";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//form inputs
    $username_email_mobile = $_POST['username_email_mobile'] ?? null;
    $password = $_POST['password'] ?? null;
// Check if username,email,mobile and password are provided
if ($username_email_mobile && $password) {
    // Hash password
    $password = md5($password);
    //query for checking user credentials
    $sql = "SELECT * FROM users WHERE (username = ? OR email = ? OR mobile_number = ?) AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username_email_mobile, $username_email_mobile, $username_email_mobile, $password);
    $stmt->execute();
    $result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Store user info in session and redirect to homepage
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['password'] = $user['password'];
    header("Location: homepage.php");
    exit();
    } else {
        $error_message = "Incorrect password!";
    }
        $stmt->close();
    } else {
        $error_message = "Please enter both username/email/mobile and password.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twinkl App Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }

        .container {
            width: 320px;
            padding: 20px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .container img {
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            margin: 10px 0;
            padding: 10px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            background-color: #007bff;
        }

        button:hover {
            background-color: #0056b3;
        }

        .signup {
            background-color: #007bff;
        }

        .signup:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="twinkle.png" alt="Twinkle App Logo" width="50">
    <h1>Login to Twinkl App</h1>
    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <input type="text" name="username_email_mobile" placeholder="Username, email or mobile number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Log in</button>
    </form>
    <p>Don't have an account? <a href="signup.php" class="signup-link">Sign up</a></p>
</div>
</body>
</html>
