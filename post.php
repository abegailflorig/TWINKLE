<?php
// Start session to verify if the user is logged in
session_start();

// Redirect to login.php if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "twinkl_app";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post'])) {
    $content = $_POST['content'] ?? '';
    $image = null;

    // Check if the uploads directory exists, if not, create it
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);  // Create the directory if it doesn't exist
    }

    // Handle file upload
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $image = $uploadDir . basename($_FILES['post_image']['name']);
        
        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($_FILES['post_image']['tmp_name'], $image)) {
            echo "File uploaded successfully.";
        } else {
            echo "Error moving uploaded file.";
        }
    }

    // Insert the new post into the database
$user_id = $_SESSION['user_id'];
$image = isset($image) ? $image : NULL; // Ensure image is NULL if not uploaded

$sql = "INSERT INTO posts (user_id, content, post_image, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);

// Bind parameters: 'i' for integer, 's' for string, and 's' for image (could be NULL)
$stmt->bind_param('iss', $user_id, $content, $image);

if ($stmt->execute()) {
    // Redirect to homepage after successful post
    header("Location: homepage.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();

}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Posting</title>
    <style>
        .new-thread-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .profile span {
            font-weight: bold;
        }

        /* New post form */
        .new-post-form {
            margin: 20px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .new-post-form textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .new-post-form input[type="file"] {
            margin-bottom: 10px;
        }

        .new-post-form button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            align-items: center;
            display: flex;
        }

        .new-post-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <a href="homepage.php"><img src="back.jpg" alt="Back to Homepage" width="25" height="25"></a>
    <!-- New Post Form -->
    <div class="new-post-form">
        <form action="post.php" method="POST" enctype="multipart/form-data">
            <textarea name="content" placeholder="What's on your mind?" required></textarea>
            <input type="file" name="post_image">
            <button type="submit" name="new_post">Post</button>
        </form>
    </div>
</body>
</html>
