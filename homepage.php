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
//Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id']; //logged-in user_id
//reactions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['react'])) {
    $post_id = $_POST['post_id'];
    //check if the user already reacted //'*'-all columns, i-integer s-string
    $stmt = $conn->prepare('SELECT * FROM post_reactions WHERE post_id = ? AND user_id = ?');
    $stmt->bind_param('ii', $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        //Add reaction
        $stmt = $conn->prepare('INSERT INTO post_reactions (post_id, user_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $post_id, $user_id);
        $stmt->execute();

        //Increment likes_count
        $stmt = $conn->prepare('UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?');
        $stmt->bind_param('i', $post_id);
        $stmt->execute();
    } else {
        //Remove reaction
        $stmt = $conn->prepare('DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?');
        $stmt->bind_param('ii', $post_id, $user_id);
        $stmt->execute();

        //Decrement likes_count
        $stmt = $conn->prepare('UPDATE posts SET likes_count = likes_count - 1 WHERE id = ?');
        $stmt->bind_param('i', $post_id);
        $stmt->execute();
    }
    $stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
//comments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $post_id = $_POST['post_id'];
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $stmt = $conn->prepare('INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iis', $post_id, $user_id, $comment);
        $stmt->execute();
        $stmt->close();
        //Increment comments_count
        $stmt = $conn->prepare('UPDATE posts SET comments_count = comments_count + 1 WHERE id = ?');
        $stmt->bind_param('i', $post_id);
        $stmt->execute();
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

//post deletion
if (isset($_GET['delete'])) {
    $post_id = $_GET['delete'];
    //Delete the post, reactions and comments
    $stmt = $conn->prepare('DELETE FROM post_reactions WHERE post_id = ?');
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    $stmt = $conn->prepare('DELETE FROM comments WHERE post_id = ?');
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    $stmt = $conn->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

//Fetch posts and reactions //* means all columns //p. This is an alias assigned to the posts table.
$sql = "SELECT p.*, u.username, u.profile_pic AS user_image, 
               (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
               (SELECT COUNT(*) FROM post_reactions r WHERE r.post_id = p.id) AS reaction_count
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
$posts = $result->fetch_all(MYSQLI_ASSOC);

//Fetch comments for a specific post
function fetch_comments($post_id, $conn) {
    $stmt = $conn->prepare('SELECT c.*, u.username, u.profile_pic AS user_image 
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
    <title>Homepage</title>
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

        /*Top bar*/
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


        /*Navigation links*/
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

        /*Post container*/
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
            gap: 5px;
        }

        .post-footer .action img {
            display: inline-block;
            width: 25px;
            height: 25px;
        }   

        .post-footer .action span {
            font-size: 14px; 
            color: gray;
        }
        
        /*Bottom navigation*/
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
        .comment-section {
            padding: 15px;
            border-top: 1px solid #ddd; 
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
        .dropdown {
            position: relative;
            margin-left: 10px; 
        }

        .dropdown-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: gray;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border: 1px solid #ddd;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>
    <!--Top Bar-->
    <div class="top-bar">
        <img src="twinkle.png" alt="Twinkle">
    </div>

    <!--Navigation Links-->
    <div class="nav-links">
        <a href="homepage.php" class="active">For you</a>
        <a href="following.php">Following</a>
    </div>

    <!--Display Posts-->
    <?php foreach ($posts as $post): ?>
        <div class="post-container">
            <div class="post-header">
                <img src="<?= !empty($post['user_image']) && file_exists('uploads/profile_pics/' . $post['user_image']) ? 'uploads/profile_pics/' . htmlspecialchars($post['user_image']) : 'default-profile.jpg' ?>" alt="User Image">
                <div class="user-info">
                    <span><strong><?= htmlspecialchars($post['username']) ?></strong></span>
                    <span><?= date('H:i', strtotime($post['created_at'])) ?></span>
                </div>
            <!--Dropdown Button for delete-->
                <?php if ($post['user_id'] == $user_id): ?>
                    <div class="dropdown">
                        <button class="dropdown-btn">•••</button>
                        <div class="dropdown-content">  
                            <a href="?delete=<?= $post['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="post-content">
                <p><?= htmlspecialchars($post['content']) ?></p>
                <?php if (!empty($post['post_image'])): ?>
                    <img src="<?= htmlspecialchars($post['post_image']) ?>" alt="Post Image">
                <?php endif; ?>
            </div>
            <div class="post-footer">
                <div class="action">
                    <form action="" method="POST" style="display: inline;">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" name="react" style="background: none; border: none; cursor: pointer;">
                            <img src="light-heart.jpg" alt="Heart Icon">
                        </button>
                    </form>
                    <span><?= $post['reaction_count'] ?></span>
                </div>
                <div class="action">
                    <img src="comment.png" alt="Comment Icon">
                    <span><?= $post['comment_count'] ?></span>
                </div>
                <div class="action">
                    <img src="share.png" alt="Share Icon">
                    <span>0</span> 
                </div>
            </div>          
            <!--Comment Section-->
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
                            <!--profile picture of commenter -->
                            <img src="<?= !empty($comment['user_image']) && file_exists('uploads/profile_pics/' . $comment['user_image']) ? 'uploads/profile_pics/' . htmlspecialchars($comment['user_image']) : 'default-profile.jpg' ?>" alt="User Image">
                            <span><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['comment']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="#"><img src="home.png" width="50" height="50"></a>
        <a href="friend.php"><img src="light-search.jpg" width="50" height="50"></a>
        <a href="post.php"><img src="light-add.jpg" width="50" height="50"></a>
        <a href="hearts.php"><img src="light-heart.jpg" width="50" height="50"></a>
        <a href="profile.php"><img src="light-profile.jpg" width="50" height="50"></a>
    </div>
</body>
</html>
<?php $conn->close(); ?>
