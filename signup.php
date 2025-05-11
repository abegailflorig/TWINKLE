<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "twinkl_app";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $mobile_number = $_POST['mobile_number'] ?? '';
    $password = isset($_POST['password']) ? md5($_POST['password']) : '';

    if ($full_name && $username && $email && $mobile_number && $password) {
        $sql = "INSERT INTO users (full_name, username, email, mobile_number, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sssss", $full_name, $username, $email, $mobile_number, $password);
            if ($stmt->execute()) {
                echo "Registration successful! You can now <a href='login.php'>log in</a>.";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing query: " . $conn->error;
        }
    } else {
        echo "Please fill in all fields.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Twinkl App</title>
    <style>
        /* General Reset */
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
            margin-top: 10px;
        }

        .signup a {
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
        }

        .signup a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="twinkle.png" alt="Twinkle App Logo" width="50">
        <h1>Sign Up for Twinkl App</h1>
        <form action="signup.php" method="POST">
            <input type="text" name="full_name" placeholder="Full name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="mobile_number" placeholder="Mobile Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign up</button>
        </form>
        <div class="signup">
            <a href="login.php">Already have an account? Log in</a>
        </div>
    </div>
</body>
</html>
