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

$user_id = $_SESSION['user_id'];

// Fetch the list of users the current user is following
$sql_following = "SELECT followed_user_id FROM followers WHERE user_id = ?";
$stmt = $conn->prepare($sql_following);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$following_result = $stmt->get_result();
$following_users = [];

while ($row = $following_result->fetch_assoc()) {
    $following_users[] = $row['followed_user_id'];
}

// If the user is following others, fetch their posts
if (!empty($following_users)) {
    $placeholders = implode(',', array_fill(0, count($following_users), '?'));
    $sql = "SELECT p.*, l.username, l.profile_pic AS user_image
            FROM posts p 
            JOIN users l ON p.user_id = l.id 
            WHERE p.user_id IN ($placeholders) 
            ORDER BY p.created_at DESC";
    
    // Bind parameters dynamically
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($following_users)), ...$following_users);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // If the user isn't following anyone, return an empty result
    $posts = [];
}


// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage Following</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f8f9fa;
        }

        /* Top bar */
        .top-bar {
            display: flex;
            justify-content: center; 
            align-items: center;     
            padding: 10px 20px;
            background-color: white;
            border-bottom: 1px solid #ddd;
        }

        .top-bar img {
            width: 50px; 
        }

        /* Navigation links */
        .nav-links {
            display: flex;
            justify-content: center;
            background-color: white;
            border-bottom: 1px solid #ddd;
        }

        .nav-links a {
            text-decoration: none;
            color: black;
            padding: 10px 20px;
            flex: 1;
            text-align: center;
            font-weight: bold;
        }

        .nav-links a.active {
            border-bottom: 2px solid black;
        }

        /* Post styles */
        .post {
            margin: 10px 15px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .post-header {
            display: flex;
            flex-direction: column;
            padding: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .user-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .username {
            font-weight: bold;
        }

        .time {
            font-size: 12px;
            color: gray;
        }

        .post-content {
            padding: 15px;
        }

        .post-content img {
            width: 100%;
            border-radius: 10px;
            margin-top: 10px;
            max-width: 300px; 
            max-height: auto; 
            object-fit: cover; 
            border-radius: 10px; 
        }
        .post-footer {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-top: 1px solid #ddd;
        }

        .post-footer .action {
            display: flex;
            align-items: center;
            color: gray;
        }

        .post-footer .action img {
            margin-right: 5px;
        }

        /* Bottom navigation */
        .bottom-nav {
            display: flex;
            position: fixed;
            justify-content: space-around;
            bottom: 0;
            width: 100%;
            background-color: white;
            border-top: 1px solid #ddd;
            padding: 10px 0;
        }

        .bottom-nav a {
            color: black;
            text-decoration: none;
            text-align: center;
        }

        .bottom-nav a img {
            width: 30px;
            height: 30px;
        }
    </style>
</head>
<body>
    <!-- Top bar -->
    <div class="top-bar">
        <img src="twinkle.png" alt="Twinkle">
    </div>

    <!-- Navigation links -->
    <div class="nav-links">
        <a href="homepage.php">For you</a>
        <a href="following.php" class="active">Following</a>
    </div>

    <!-- Loop through posts -->
    <?php foreach ($posts as $post): ?>
<div class="post">
    <div class="post-header">
        <div class="user-info"> 
            <img src="<?= !empty($post['user_image']) && file_exists('uploads/profile_pics/' . $post['user_image']) ? 'uploads/profile_pics/' . htmlspecialchars($post['user_image']) : 'default-profile.jpg' ?>" 
                 alt="User Image" 
                 class="user-img">
            <span class="username"><?= htmlspecialchars($post['username']) ?></span>
            <span class="time"><?= date('H:i', strtotime($post['created_at'])) ?></span>
        </div>
        <div class="post-content">
            <p><?= htmlspecialchars($post['content']) ?></p>
            <?php if (!empty($post['post_image'])): ?>
                <img src="<?= htmlspecialchars($post['post_image']) ?>" alt="Post Image">
            <?php endif; ?>
        </div>
    </div>
    <div class="post-footer">
        <div class="action">
            <img src="react.jpg" width="20" height="20"> <?= htmlspecialchars($post['likes_count'] ?? 0) ?>
        </div>
        <div class="action">
            <img src="comment.png" width="20" height="20"> <?= htmlspecialchars($post['comments_count'] ?? 0) ?>
        </div>
        <div class="action">
            <img src="share.png" width="20" height="20"> <?= htmlspecialchars($post['shares_count'] ?? 0) ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
    <!-- Bottom navigation -->
    <div class="bottom-nav">
        <a href="#"><img src="home.png" width="50" height="50"></a>
        <a href="friend.php"><img src="light-search.jpg" width="50" height="50"></a>
        <a href="post.php"><img src="light-add.jpg" width="50" height="50"></a>
        <a href="#"><img src="light-heart.jpg" width="50" height="50"></a>
        <a href="profile.php"><img src="light-profile.jpg" width="50" height="50"></a>
    </div>
</body>
</html>
