<?php
session_start();

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'twinkl_app';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Insert comment if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $user_id = $_SESSION['user_id']; 
    $post_id = $_POST['post_id'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare('INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $post_id, $user_id, $comment);
    $stmt->execute();
    $stmt->close();

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch posts from the database
$sql = "SELECT p.*, u.username, u.profile_image AS user_image, 
               (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
$posts = $result->fetch_all(MYSQLI_ASSOC);

// Fetch comments for a specific post
function fetch_comments($post_id, $conn) {
    $stmt = $conn->prepare('SELECT c.*, u.username, u.profile_image 
                            FROM comments c 
                            JOIN users u ON c.user_id = u.id 
                            WHERE c.post_id = ? 
                            ORDER BY c.created_at ASC');
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $comments;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage with Comments</title>
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

        /* Post container */
        .post-container {
            margin: 10px 15px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .post-header {
            display: flex;
            align-items: center;
            padding: 15px;
        }

        .post-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .post-header .user-info {
            display: flex;
            flex-direction: column;
        }

        .post-header .user-info .username {
            font-weight: bold;
        }

        .post-header .user-info .time {
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
            gap: 5px; /* Add some space between the image and the text */
        }

        .post-footer .action img {
            display: inline-block;
            width: 25px;
            height: 25px;
        }   

        .post-footer .action span {
            font-size: 14px; /* Adjust the font size for better alignment */
            color: gray;
        }
        
        /* Bottom navigation */
        .bottom-nav {
            display: flex;
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
        .comment-section {
            padding: 15px;
            border-top: 1px solid #ddd; /*line*/
        }

        .comment-section textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }

        .comments {
            padding: 15px;
            background-color: #f8f9f9;
        }

        .comment {
            padding: 5px;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            border-top: 1px solid #ddd;
        }

        .comment img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <img src="twinkle.png" alt="Twinkle">
    </div>
     <!-- Navigation links -->
     <div class="nav-links">
        <a href="homepage.php" class="active">For you</a>
        <a href="following.php">Following</a>
    </div>
    <!-- Display Posts -->
    <?php foreach ($posts as $post): ?>
        <div class="post-container">
            <div class="post-header">
                <img src="<?= !empty($post['user_image']) && file_exists('uploads/' . $post['user_image']) ? 'uploads/' . htmlspecialchars($post['user_image']) : 'default-profile.jpg' ?>" alt="User Image">
                <div class="user-info">
                    <span><strong><?= htmlspecialchars($post['username']) ?></strong></span>
                    <span><?= date('H:i', strtotime($post['created_at'])) ?></span>
                </div>
            </div>
            <div class="post-content">
                <p><?= htmlspecialchars($post['content']) ?></p>
                <?php if (!empty($post['post_image'])): ?>
                    <img src="<?= htmlspecialchars($post['post_image']) ?>" alt="Post Image">
                <?php endif; ?>
            </div>
            <div class="post-footer">
                <div class="action">
                    <img src="react.jpg" alt="React Icon">
                    <span><?= $post['likes'] ?></span>
                </div>
                <div class="action">
                    <img src="comment.png" alt="Comment Icon">
                    <span><?= $post['comment_count'] ?></span>
                </div>
                <div class="action">
                    <img src="share.png" alt="Share Icon">
                    <span><?= $post['shares'] ?></span>
                </div>
            </div>

            <!-- Comment Section -->
            <div class="comment-section">
                <form action="" method="POST">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <textarea name="comment" placeholder="Add a comment..." required></textarea>
                    <button type="submit">Comment</button>
                </form>
                <div class="comments">
                    <?php 
                    $comments = fetch_comments($post['id'], $conn);
                    foreach ($comments as $comment): ?>
                        <div class="comment">
                            <img src="<?= htmlspecialchars($comment['profile_image']) ?>" alt="User Image">
                            <span><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['comment']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <!-- Bottom navigation -->
    <div class="bottom-nav">
        <a href="#"><img src="home.png" width="50" height="50"></a>
        <a href="#"><img src="light-search.jpg" width="50" height="50"></a>
        <a href="post.php"><img src="light-add.jpg" width="50" height="50"></a>
        <a href="#"><img src="light-heart.jpg" width="50" height="50"></a>
        <a href="profile.php"><img src="light-profile.jpg" width="50" height="50"></a>
    </div>

</body>
</html>

<?php
$conn->close();
?>
