<?php
session_start(); 

// Logged-in user ID from session
$logged_in_user_id = $_SESSION['user_id']; 

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'twinkl_app';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle follow/unfollow actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];

    if ($action === 'follow') {
        // Insert into followers table
        $stmt = $conn->prepare("INSERT INTO followers (user_id, followed_user_id, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $logged_in_user_id, $user_id);
        $stmt->execute();
        $stmt->close();

        // Increment followers count
        $stmt = $conn->prepare("UPDATE users SET followers_count = followers_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    } else if ($action === 'unfollow') {
        // Delete from followers table
        $stmt = $conn->prepare("DELETE FROM followers WHERE user_id = ? AND followed_user_id = ?");
        $stmt->bind_param("ii", $logged_in_user_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET followers_count = followers_count - 1 WHERE id = ?");
         $stmt->bind_param("i", $user_id);
         $stmt->execute();
        $stmt->close();
    }
    echo json_encode(['success' => true]);
    exit;
}


// Fetch users and their follower details //'users.id !='-not in //$logged_in_user_id(user's who logged-in)
$sql = " SELECT 
        users.id AS user_id, 
        users.username, 
        users.profile_pic, 
        users.full_name,
        users.followers_count,
        EXISTS (
            SELECT 1 
            FROM followers 
            WHERE followers.user_id = $logged_in_user_id 
            AND followers.followed_user_id = users.id
        ) AS is_following
    FROM users
    WHERE users.id != $logged_in_user_id
    ORDER BY followers_count DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Error executing query: " . $conn->error);
}
$users = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Following</title>
    <style>
        body { 
             font-family: Arial, sans-serif; 
            background-color: #f4f4f4; 
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            background: #fff; 
            padding: 20px; 
            border-radius: 10px; 
        }
        .user-list { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
        }
        .user-list li { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 10px 0; 
            border-bottom: 1px solid #ddd; 
        }
        .user-list li:last-child { 
            border-bottom: none; 
        }
        .profile-image { 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            margin-right: 10px; 
            object-fit: cover; 
        }
        .user-details { 
            flex: 1; 
        }
        .username { 
            font-weight: bold; 
        }
        .followers { 
            font-size: 0.9em; 
            color: #666; 
        }
        .follow-button { 
            padding: 5px 10px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            background: #007bff; 
            color: #fff; 
        }
        .follow-button.unfollow { 
            background: #dc3545; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Users</h1>
        <ul class="user-list">
            <?php foreach ($users as $user): ?>
                <li>
                    <img src="<?= !empty($user['profile_pic']) ? 'uploads/profile_pics/' . htmlspecialchars($user['profile_pic']) : 'default-profile.jpg' ?>" 
                         class="profile-image" 
                         alt="Profile">
                    <div class="user-details">
                        <div class="username"><?= htmlspecialchars($user['username']) ?></div>
                        <div class="followers"><?= htmlspecialchars($user['followers_count']) ?> Followers</div>
                    </div>
                    <button class="follow-button <?= $user['is_following'] ? 'unfollow' : '' ?>" 
                            data-user-id="<?= $user['user_id'] ?>" 
                            data-action="<?= $user['is_following'] ? 'unfollow' : 'follow' ?>">
                        <?= $user['is_following'] ? 'Unfollow' : 'Follow' ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.follow-button').forEach(button => {
                button.addEventListener('click', () => {
                    const userId = button.getAttribute('data-user-id');
                    const action = button.getAttribute('data-action');

                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `user_id=${userId}&action=${action}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (action === 'follow') {
                                button.textContent = 'Unfollow';
                                button.setAttribute('data-action', 'unfollow');
                                button.classList.add('unfollow');
                            } else {
                                button.textContent = 'Follow';
                                button.setAttribute('data-action', 'follow');
                                button.classList.remove('unfollow');
                            }
                            // Update followers count dynamically
                            const followersDiv = button.parentNode.querySelector('.followers');
                            let followersCount = parseInt(followersDiv.textContent.split(' ')[0]);
                            followersCount = action === 'follow' ? followersCount + 1 : followersCount - 1;
                            followersDiv.textContent = followersCount + ' Followers';
                        } else {
                            alert('Action failed. Please try again.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
</body>
</html>
